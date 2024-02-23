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

    public function __construct()
    {
        if (!isset($_ENV['WORDPRESS_PATH']) || strlen($_ENV['WORDPRESS_PATH']) == 0) {
            throw new UnexpectedValueException('WORDPRESS_PATH environment variable is not set or is empty');
        } else
            $wordpressPath = $_ENV['WORDPRESS_PATH'];
        try {
            require_once $wordpressPath . '/wp-load.php';
        } catch (\Throwable $e) {
            // Handle the error
            error_log($e->getMessage());
        }

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
        $user = get_user_by('ID', $userId);
        $userMeta = get_user_meta($userId);

        if (!$user instanceof \WP_User) {
            throw new UnexpectedValueException("Gebruiker met id '$userId' bestaat niet");
        }

        $persoon = new Persoon($user->data->ID, $userMeta['first_name'][0] . ' ' . $userMeta['last_name'][0], $user->data->user_email);

        $persoon->rugnummer = isset($userMeta['rugnummer']) ? Utilities::StringToInt($userMeta['rugnummer']) : null;
        $persoon->positie = isset($userMeta['positie']) ? $userMeta['positie'][0] : "";

        return $persoon;
    }

    public function GetLoggedInUser(): ?Persoon
    {
        $wploggedin = is_user_logged_in();
        $wordPressUser = wp_get_current_user();

        if (!$wploggedin) {
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

        $query = "SELECT 
        u.ID as id, 
        u.display_name as name, 
        u.user_email as email
        FROM 
        " . $_ENV['WPDBNAME'] . ".wp_users u      
        INNER JOIN
        " . $_ENV['WPDBNAME'] . ".wp_usermeta niveau_meta ON u.ID = niveau_meta.user_id AND niveau_meta.meta_key = 'scheidsrechter' AND niveau_meta.meta_value <> '' AND niveau_meta.meta_value IS NOT NULL
        WHERE id = ?";

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
        $query = "SELECT ID as id, post_title as title FROM " . $_ENV['WPDBNAME'] . ".wp_posts where post_title=? and post_type='team'";

        $params = [$team->GetSkcNaam()];
        $result = $this->database->Execute($query, $params);

        if (count($result) != 1) {
            return null;
        }
        return new Team($result[0]->title, $result[0]->id);
    }

    public function GetUserByEmail(string $email): Persoon
    {

        $user = get_user_by('email', $email);
        $user = $this->GetUserById($user->data->ID);
        return $user;
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

        $userMeta = get_user_meta($user->id);

        $teamId = $userMeta['team'][0];

        $params = array(
            'where' => "id='" . $teamId .  "'"
        );

        $team = pods('team')->find($params);

        if ($team->total() != 1) {
            return null;
        }

        while ($team->fetch()) {
            return new Team($team->display('name'), $team->display('id'));
        }
    }

    public function GetTeamgenoten(?Team $team): array
    {
        if ($team === null) {
            return [];
        }

        $team = pods('team', $team->id);
        $teamGenoten =  $team->field('leden');
        if (!empty($teamGenoten)) {
            foreach ($teamGenoten as $teamGenoot) {
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
        $userMeta = get_user_meta($user->id);

        $result = [];

        if (!isset($userMeta['coach_van'])) {
            return $result;
        }

        foreach ($userMeta['coach_van'] as $teamId) {
            $params = array('where' => "id='" . $teamId .  "'");
            $team = pods('team')->find($params);
            while ($team->fetch()) {
                $newTeam = new Team($team->display('name'), $team->display('id'));
                $newTeam->teamgenoten = $this->GetTeamgenoten($newTeam);
                $result[] = $newTeam;
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

    public function Login(string $username, string $password): bool
    {
        $credentials = [
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true
        ];

        $result = wp_signon($credentials, true); // true - use HTTP only cookie

        if ($result instanceof \WP_Error) {
            // Could not login
            if (function_exists("SimpleLogger")) {
                SimpleLogger()->warning("User $username could not login into Team-portal");
            }
            return false;
        } else {
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
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->MapToPersoon($row);
        }
        return $result;
    }

    private function MapToPersoon(object $row): Persoon
    {
        // WP Pods returns WP Users with ID's, not id's, so we add the id.
        if (!isset($row->id)) {
            if (isset($row->ID)) {
                $row->id = $row->ID;
            }
        }

        $persoon = new Persoon($row->id, $row->display_name, $row->user_email);
        $userMeta = get_user_meta($row->id);


        $persoon->rugnummer = isset($userMeta['rugnummer']) ? Utilities::StringToInt($userMeta['rugnummer']) : null;
        $persoon->positie = isset($userMeta['positie']) ? $userMeta['positie'][0] : "";

        $result[] = $persoon;

        return $persoon;
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
