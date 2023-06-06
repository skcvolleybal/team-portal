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
        // WP ready 

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
        // WP Ready
        $user = get_user_by('ID', $userId);
        $userMeta = get_user_meta($userId);

        if (! $user instanceof \WP_User) {
            throw new UnexpectedValueException("Gebruiker met id '$userId' bestaat niet");
        }


        $persoon = new Persoon($user->data->ID, $userMeta['first_name'][0] . ' ' . $userMeta['last_name'][0], $user->data->user_email);
        // Tot hier werkend

        $persoon->rugnummer = isset($userMeta['rugnummer']) ? Utilities::StringToInt($userMeta['rugnummer']) : null;

        $persoon->positie = isset($userMeta['positie']) ? $userMeta['positie'][0] : "";

        // $persoon->positie = $userMeta['positie'][0];
        return $persoon;
    }

    public function GetLoggedInUser(): ?Persoon
    {
    // WP ready

        $wploggedin = is_user_logged_in();
        $wordPressUser = wp_get_current_user();
  
        if (!$wploggedin) {
            return null;
        }
        
        // Construct a TeamPortal Persoon
        $user = $this->GetUserById($wordPressUser->id);
        
        // Tot hier werkend
        if ($this->IsWebcie($user) && isset($_GET['impersonationId'])) {
            // To-do: ImpersonationID checken
            $impersonationId = $_GET['impersonationId'];
            return $this->GetUserById($impersonationId);
        }
        // Happy flow WP ready
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


        $args = array(
                'search'         => '*' . $name . '*',
                'search_columns' => array(
                    'display_name',
                ),
                'orderby'        => 'display_name',
                'order'          => 'ASC',
                'number'         => 5,
            );
            
        $rows = get_users($args);
            

        return $this->MapToPersonen($rows);
    }

    private function IsUserInUsergroup(?Persoon $user, string $usergroup): bool
    {
        // WP ready
        if ($user === null) {
            return false;
        }

        $user = get_user_by('ID', $user->id);

        // Set all to lowercase, to make sure "Webcie" = "webcie"
        if (in_array(strtolower($usergroup), array_map('strtolower', (array) $user->roles))) {
            return true;
        }
        
        return false;

    }

    public function IsScheidsrechter(?Persoon $user): bool
    {
        return $this->IsUserInUsergroup($user, 'Scheidsrechters');
    }

    public function IsWebcie(?Persoon $user): bool
    {
        // In WordPress, it's not Super User but administrator
        return $this->IsUserInUsergroup($user, 'administrator');
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
        // WP Ready

        $userMeta = get_user_meta($user->id);
        $teamId = $userMeta['team'][0];

        $params = array(
            'where'=> "id='" . $teamId .  "'"
        );

        $team = pods('team')->find( $params );

        if ($team->total() != 1) {
            return null;
        }

        while ( $team->fetch() ) {
            return new Team($team->display('name'), $team->display('id'));
        }


    }

    public function GetTeamgenoten(?Team $team): array
    {
        // WP ready
        if ($team === null) {
            return [];
        }

        $team = pods( 'team', $team->id );
        $teamGenoten =  $team->field( 'leden' );
        if ( ! empty( $teamGenoten ) ) {
            foreach ( $teamGenoten as $teamGenoot ) {
                // Cast array of arrays to array of objects
                $object = (object)$teamGenoot;
                $teamObjects[] = $object;
            }
            return $this->MapToPersonen($teamObjects);
        }
        return array();          
    }

    public function GetCoachteams(Persoon $user): array
    {
        // WP Working
        $userMeta = get_user_meta($user->id);

        $result = [];

        if (!isset($userMeta['coach_van'])) {
            return $result;
        }

        foreach ($userMeta['coach_van'] as $teamId) {
            $params = array('where'=> "id='" . $teamId .  "'");
            $team = pods('team')->find( $params );
            while ( $team->fetch() ) {
                $newTeam = new Team($team->display('name'), $team->display('id'));
                $newTeam->teamgenoten = $this->GetTeamgenoten($newTeam);
                $result[] = $newTeam;
                // Tot hier werkend
            }
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
        // WP ready
        $credentials = [
            'user_login' => $username,
            'user_password' => $password,
            'rememberme' => true
         ];
   
        $result = wp_signon($credentials, true); // true - use HTTP only cookie

        if ($result instanceof \WP_Error) {
            // Could not login
            if (function_exists("SimpleLogger")) {
                SimpleLogger()->warning("User $username could not login into Team-portal");
            }
            return false;
         } 
         else {
            $user_nicename = $result->data->user_nicename;
            // SimpleLogger: https://wordpress.org/plugins/simple-history/
            if (function_exists("SimpleLogger")) {
                SimpleLogger()->info("User $user_nicename logged into Team-portal");
            }
            return true;   
         }
         return false;

    }

    private function MapToPersonen(array $rows): array
    {
        // WP Ready
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->MapToPersoon($row);
        }
        return $result;
    }

    private function MapToPersoon(object $row): Persoon
    {
        // WP Ready

        $persoon = new Persoon($row->id, $row->display_name, $row->user_email);
        $userMeta = get_user_meta($row->id);


        $persoon->rugnummer = isset($userMeta['rugnummer']) ? Utilities::StringToInt($userMeta['rugnummer']) : null;
        $persoon->positie = isset($userMeta['positie']) ? $userMeta['positie'][0] : "";

        // Relatiecode seems not to be in use 
        // $persoon->relatiecode = $metaObjects->relatiecode;
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
