<?php

namespace TeamPortal\Gateways;

use SimplePie\Parse\Date;
use TeamPortal\Common\Database;
use TeamPortal\Common\Utilities;
use TeamPortal\Entities\Credentials;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Scheidsrechter;
use TeamPortal\Entities\Team;
use TeamPortal\UseCases\IWordPressGateway;
use UnexpectedValueException;

class WordPressGateway implements IWordPressGateway
{
    public $database;

    public function __construct() {
        $this->database = new Database();
    }

    private static $allSkcSpelers = null;

    public function GetUser(?int $userId = null): ?Persoon
    {
        $user = empty($userId) ? $this->GetLoggedInUser() : $this->GetUserById($userId);
        if (!$user) {
            return null;
        }

        $user->team = $this->GetTeam($user);
        $user->coachteams = $this->GetCoachteams($user);

        return $user;
    }

    private function GetUserById(int $userId): Persoon
    {
        $query = 'SELECT 
                    U.id, 
                    U.name AS naam, 
                    U.email,
                    C.cb_rugnummer as rugnummer,
                    C.cb_positie as positie,
                    C.cb_nevobocode as relatiecode
                  FROM J3_users U
                  LEFT JOIN J3_comprofiler C ON U.id = C.user_id
                  WHERE U.id = ?';
        $params = [$userId];
        $users = $this->database->Execute($query, $params);
        if (count($users) != 1) {
            throw new UnexpectedValueException("Gebruiker met id '$userId' bestaat niet");
        }

        $persoon = new Persoon($users[0]->id, $users[0]->naam, $users[0]->email);
        $persoon->rugnummer = Utilities::StringToInt($users[0]->rugnummer);
        $persoon->positie = $users[0]->positie;
        return $persoon;
    }

    public function GetLoggedInUser(): ?Persoon
    {
        $this->InitWordPress();

        $wordPressUser = \JFactory::getUser();
        if ($wordPressUser->guest) {
            return null;
        }

        $user = $this->GetUserById($wordPressUser->id);

        if ($this->IsWebcie($user) && isset($_GET['impersonationId'])) {
            $impersonationId = $_GET['impersonationId'];
            return $this->GetUserById($impersonationId);
        }

        return $user;
    }

    public function GetScheidsrechter(?int $userId): ?Scheidsrechter
    {
        $query = 'SELECT U.id, name, email
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE U.id = ? and
                        G.id in (
                            SELECT id FROM J3_usergroups WHERE title = "Scheidsrechters"
                        )';
        $params = [$userId];
        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return null;
        }

        return new Scheidsrechter(
            new Persoon($rows[0]->id, $rows[0]->name, $rows[0]->email)
        );
    }

    public function GetTeamByNaam(?string $naam): ?Team
    {
        if (empty($naam)) {
            return null;
        }
        $team = new Team($naam);
        $query = 'SELECT * FROM J3_usergroups
                  WHERE title = ?';
        $params = [$team->GetSkcNaam()];
        $result = $this->database->Execute($query, $params);
        if (count($result) != 1) {
            return null;
        }
        return new Team($result[0]->title, $result[0]->id);
    }

    public function GetUsersWithName(string $name): array
    {
        $query = "SELECT 
                    U.id,
                    U.name as naam,
                    U.email,
                    C.cb_rugnummer as rugnummer,
                    C.cb_positie as positie,
                    C.cb_nevobocode as relatiecode
                  FROM J3_users U
                  LEFT JOIN J3_comprofiler C ON U.id = C.user_id
                  WHERE name like '%$name%'
                  ORDER BY 
                  CASE 
                    WHEN name LIKE '$name%' THEN 0 ELSE 1 end,
                  name  
                  LIMIT 0, 5";
        $rows = $this->database->Execute($query);
        return $this->MapToPersonen($rows);
    }

    private function IsUserInUsergroup(?Persoon $user, string $usergroup): bool
    {
        if ($user === null) {
            return false;
        }
        $query = 'SELECT *
                  FROM J3_user_usergroup_map M
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE M.user_id = ? and G.title = ?';
        $params = [$user->id, $usergroup];
        $result = $this->database->Execute($query, $params);
        return count($result) > 0;
    }

    public function IsScheidsrechter(?Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Scheidsrechters');
    }

    public function IsWebcie(?Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Super Users');
    }

    public function IsTeamcoordinator(?Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Teamcoordinator');
    }

    public function IsBarcie(?Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Barcie');
    }

    public function GetTeam(Persoon $user): ?Team
    {
        $query = 'SELECT 
                    G.id,
                    title AS naam
                  FROM J3_users U
                  LEFT JOIN J3_user_usergroup_map M on U.id = M.user_id
                  LEFT JOIN J3_usergroups G on G.id = M.group_id
                  WHERE M.user_id = ? and G.parent_id in (SELECT id from J3_usergroups where title = \'Teams\')';
        $params = [$user->id];

        $team = $this->database->Execute($query, $params);
        if (count($team) != 1) {
            return null;
        }

        return new Team($team[0]->naam, $team[0]->id);
    }

    public function GetTeamgenoten(?Team $team): array
    {
        if ($team === null) {
            return [];
        }
        $query = 'SELECT 
                    U.id, 
                    name AS naam,
                    email,
                    cb_positie as positie,
                    cb_rugnummer as rugnummer,
                    C.cb_nevobocode as relatiecode
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  LEFT JOIN J3_comprofiler C ON U.id = C.id
                  WHERE G.title = ?
                  ORDER BY name';
        $params = [$team->GetSkcNaam($team)];
        $rows =  $this->database->Execute($query, $params);
        return $this->MapToPersonen($rows);
    }

    public function GetCoachteams(Persoon $user): array
    {
        $query = 'SELECT 
                    G2.id,
                    G2.title AS naam
                  FROM J3_usergroups G
                  INNER JOIN J3_user_usergroup_map M on G.id = M.group_id
                  INNER JOIN J3_usergroups G2 on G2.title = SUBSTRING(G.title, 7)
                  WHERE M.user_id = ? and G.title like \'Coach %\'';
        $params = [$user->id];

        $teams = $this->database->Execute($query, $params);

        $result = [];
        foreach ($teams as $team) {
            $newTeam = new Team($team->naam, $team->id);
            $newTeam->teamgenoten = $this->GetTeamgenoten($newTeam);
            $result[] = $newTeam;
        }

        return $result;
    }

    public function GetCoaches(Team $team): array
    {
        return $this->GetUsersInGroup('Coach ' . $team->GetSkcNaam());
    }

    public function GetTrainers(Team $team): array
    {
        return $this->GetUsersInGroup('Trainer ' . $team->GetSkcNaam());
    }

    public function GetUsersInGroup(string $groupname): array
    {
        $query = 'SELECT
                    U.id,
                    U.name AS naam,
                    U.email
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE G.title = ?';
        $params = [$groupname];
        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[]  = new Persoon($row->id, $row->naam, $row->email);
        }
        return $result;
    }

    public function InitWordPress(): void
    {
        if (defined('_JEXEC')) {
            return;
        }

        define('JPATH_BASE', $_ENV['JPATHBASE']);
        define('_JEXEC', 1);

        require_once JPATH_BASE . '/includes/defines.php';
        require_once JPATH_BASE . '/includes/framework.php';

        $mainframe = \JFactory::getApplication('site');
        $mainframe->initialise();
    }

    public function Login(string $username, string $password): bool
    {
        $this->InitWordPress();

        $credentials = new Credentials($username, $password);

        $joomlaApp = \JFactory::getApplication('site');

        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, password')
            ->from('#__users')
            ->where('username=' . $db->quote($credentials->username));

        $db->setQuery($query);
        $result = $db->loadObject();
        if ($result) {
            $match = \JUserHelper::verifyPassword($credentials->password, $result->password, $result->id);
            if ($match === true) {
                $joomlaApp->login((array) $credentials);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function MapToPersonen(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->MapToPersoon($row);
        }
        return $result;
    }

    private function MapToPersoon(object $row): Persoon
    {
        $persoon = new Persoon($row->id, $row->naam, $row->email);
        $persoon->relatiecode = $row->relatiecode;
        $persoon->positie = $row->positie;
        $persoon->rugnummer = Utilities::StringToInt($row->rugnummer);
        $result[] = $persoon;

        return $persoon;
    }

    public function GetSpelerByRugnummer(int $rugnummer, Team $team): ?Persoon
    {
        $query = "SELECT 
                    U.id, 
                    name AS naam,
                    email,
                    cb_positie as positie,
                    cb_rugnummer as rugnummer,
                    cb_nevobocode as relatiecode
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  LEFT JOIN J3_comprofiler C ON U.id = C.id
                  WHERE cb_rugnummer = ? AND G.title LIKE ?
                  ORDER BY ABS(CAST(SUBSTR(G.title, 6) AS INT) - ?)"; // Order by om het dichtst bijzijnde team te verkrijgen
        $params = [
            $rugnummer,
            substr($team->GetSkcNaam(), 0, 5) . " %",
            Utilities::StringToInt(substr($team->GetSkcNaam(), 6))
        ];
        $rows = $this->database->Execute($query, $params);
        return $rows != null ? $this->MapToPersoon($rows[0]) : null;
    }

    public function GetRugnummerOfPersoon(Persoon $user)
    {
        $query = "SELECT 
                    cb_rugnummer as rugnummer
                  FROM J3_comprofiler C
                  WHERE user_id = ?";
        $params = [$user->id];
        $rows = $this->database->Execute($query, $params);
        return empty($rows) ? null : Utilities::StringToInt($rows[0]->rugnummer);
    }

    public function GetAllSpelers()
    {
        if (self::$allSkcSpelers == null) {
            $query = "SELECT 
                    U.id, 
                    name AS naam,
                    email,
                    cb_positie as positie,
                    cb_rugnummer as rugnummer,
                    cb_nevobocode as relatiecode
                  FROM J3_users U
                  INNER JOIN J3_comprofiler C on U.id = C.user_id
                  WHERE C.cb_nevobocode is not null";
            $rows = $this->database->Execute($query);
            self::$allSkcSpelers = $this->MapToPersonen($rows);
        }

        return self::$allSkcSpelers;
    }
}
