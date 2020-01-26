<?php

namespace TeamPortal\Gateways;

use TeamPortal\Configuration;
use TeamPortal\Common\Database;
use TeamPortal\Entities;

class JoomlaGateway
{
    public function __construct(
        Configuration $configuration,
        Database $database
    ) {
        $this->configuration = $configuration;
        $this->database = $database;
    }

    public function GetUser(?int $userId = null): ?Entities\Persoon
    {
        $user = empty($userId) ? $this->GetLoggedInUser() : $this->GetUserById($userId);
        if (!$user) {
            return null;
        }

        $user->team = $this->GetTeam($user);
        $user->coachteam = $this->GetCoachTeam($user);

        return $user;
    }

    private function GetUserById(int $userId): Entities\Persoon
    {
        $query = 'SELECT id, name AS naam, email
                  FROM J3_users
                  WHERE id = ?';
        $params = [$userId];
        $users = $this->database->Execute($query, $params);
        if (count($users) != 1) {
            throw new \UnexpectedValueException("Gebruiker met id '$userId' bestaat niet");
        }

        return new Entities\Persoon($users[0]->id, $users[0]->naam, $users[0]->email);
    }

    public function GetLoggedInUser(): ?Entities\Persoon
    {
        $this->InitJoomla();

        $joomlaUser = \JFactory::getUser();
        if ($joomlaUser->guest) {
            return null;
        }

        $user = new Entities\Persoon(
            $joomlaUser->id,
            $joomlaUser->name,
            $joomlaUser->email
        );

        if ($this->IsWebcie($user) && isset($_GET['impersonationId'])) {
            $impersonationId = $_GET['impersonationId'];
            return $this->GetUserById($impersonationId);
        }

        return $user;
    }

    public function GetScheidsrechter(?int $userId): ?Entities\Scheidsrechter
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
        };
        return new Entities\Scheidsrechter(
            new Entities\Persoon($rows[0]->id, $rows[0]->name, $rows[0]->email)
        );
    }

    public function GetTeamByNaam(?string $naam): ?Entities\Team
    {
        if (empty($naam)) {
            return null;
        }
        $team = new Entities\Team($naam);
        $query = 'SELECT * FROM J3_usergroups
                  WHERE title = ?';
        $params = [$team->GetSkcNaam()];
        $result = $this->database->Execute($query, $params);
        if (count($result) != 1) {
            return null;
        }
        return new Entities\Team($result[0]->title, $result[0]->id);
    }

    public function GetUsersWithName(string $name): array
    {
        $query = "SELECT 
                    id,
                    name as naam,
                    email
                  FROM J3_users 
                  WHERE name like '%$name%'
                  ORDER BY 
                  CASE 
                    WHEN name LIKE '$name%' THEN 0 ELSE 1 end,
                  name  
                  LIMIT 0, 5";
        $rows = $this->database->Execute($query);
        return $this->MapToPersonen($rows);
    }

    private function IsUserInUsergroup(?Entities\Persoon $user, string $usergroup): bool
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

    public function IsScheidsrechter(?Entities\Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Scheidsrechters');
    }

    public function IsWebcie(?Entities\Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Super Users');
    }

    public function IsTeamcoordinator(?Entities\Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Teamcoordinator');
    }

    public function IsBarcie(?Entities\Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Barcie');
    }

    public function GetTeam(Entities\Persoon $user): ?Entities\Team
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

        return new Entities\Team($team[0]->naam, $team[0]->id);
    }

    public function GetTeamgenoten(?Entities\Team $team): array
    {
        if ($team === null) {
            return [];
        }
        $query = 'SELECT 
                    U.id, 
                    name AS naam,
                    email
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE G.title = ?
                  ORDER BY name';
        $params = [$team->GetSkcNaam($team)];
        $rows =  $this->database->Execute($query, $params);
        return $this->MapToPersonen($rows);
    }

    public function GetCoachTeam(Entities\Persoon $user): ?Entities\Team
    {
        $query = 'SELECT 
                    G2.id,
                    G2.title AS naam
                  FROM J3_usergroups G
                  INNER JOIN J3_user_usergroup_map M on G.id = M.group_id
                  INNER JOIN J3_usergroups G2 on G2.title = SUBSTRING(G.title, 7)
                  WHERE M.user_id = ? and G.title like \'Coach %\'';
        $params = [$user->id];

        $team = $this->database->Execute($query, $params);
        if (count($team) != 1) {
            return null;
        }

        return new Entities\Team($team[0]->naam, $team[0]->id);
    }

    public function GetCoaches(Entities\Team $team): array
    {
        return $this->GetUsersInGroup('Coach ' . $team->GetSkcNaam());
    }

    public function GetTrainers(Entities\Team $team): array
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
            $result[]  = new Entities\Persoon($row->id, $row->naam, $row->email);
        }
        return $result;
    }

    public function InitJoomla(): void
    {
        if (defined('_JEXEC')) {
            return;
        }

        define('JPATH_BASE', $this->configuration->JpathBase);
        define('_JEXEC', 1);

        require_once JPATH_BASE . '/includes/defines.php';
        require_once JPATH_BASE . '/includes/framework.php';

        $mainframe = \JFactory::getApplication('site');
        $mainframe->initialise();
    }

    public function Login(string $username, string $password): bool
    {
        $this->InitJoomla();

        $credentials = new Entities\Credentials($username, $password);

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
            $result[] = new Entities\Persoon($row->id, $row->naam, $row->email);
        }
        return $result;
    }
}
