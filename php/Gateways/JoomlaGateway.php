<?php

include_once 'Param.php';
include_once 'Utilities.php';

class JoomlaGateway
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetUserId($forceImpersonation = true)
    {
        $this->InitJoomla();

        $session = JFactory::getSession();
        $user = JFactory::getUser();
        if ($user->guest) {
            return null;
        }

        if ($forceImpersonation && $this->IsWebcie($user->id) && isset($_GET['impersonationId'])) {
            $impersonationId = $_GET['impersonationId'];
            if (isset($impersonationId) && $this->DoesUserIdExist($impersonationId)) {
                return $impersonationId;
            }
        }

        return $user->id;
    }

    public function GetScheidsrechterByName($scheidsrechter)
    {
        if (empty($scheidsrechter)) {
            return null;
        }

        $query = "SELECT U.id, name
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE U.name = :scheidsrechter and
                        G.id in (SELECT id FROM J3_usergroups WHERE title = 'Scheidsrechters')";
        $params = [
            new Param(":scheidsrechter", $scheidsrechter, PDO::PARAM_STR),
        ];
        $scheidsrechters = $this->database->Execute($query, $params);
        if (count($scheidsrechters) == 0) {
            InternalServerError("Unknown scheidsrechter: $scheidsrechter");
        };
        return $scheidsrechters[0];
    }

    public function GetTeamByNaam($naam)
    {
        $query = "SELECT * FROM J3_usergroups
                  WHERE title = :naam";
        $params = [new Param(":naam", $naam, PDO::PARAM_STR)];
        $teams = $this->database->Execute($query, $params);
        if (count($teams) == 0) {
            return null;
        }
        return $teams[0];
    }

    private function DoesUserIdExist($userId)
    {
        $query = "SELECT id FROM J3_users WHERE id = :userId";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        return count($result) > 0;
    }

    public function GetUsersWithName($name)
    {
        $name = "%$name%";
        $query = "SELECT * FROM J3_users where name like :name LIMIT 0, 5";
        $params = [new Param(":name", $name, PDO::PARAM_STR)];
        return $this->database->Execute($query, $params);
    }

    private function IsUserInUsergroup($userId, $usergroup)
    {
        $query = "SELECT *
                  FROM J3_user_usergroup_map M
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE M.user_id = :userId and G.title = :usergroup";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":usergroup", $usergroup, PDO::PARAM_STR),
        ];
        $result = $this->database->Execute($query, $params);
        return count($result) > 0;
    }

    public function IsScheidsrechter($userId)
    {
        return $this->IsUserInUsergroup($userId, 'Scheidsrechters');
    }

    public function IsWebcie($userId)
    {
        return $this->IsUserInUsergroup($userId, 'Super Users');
    }

    public function IsScheidsco($userId)
    {
        return $this->IsUserInUsergroup($userId, 'Scheidsco');
    }

    public function GetTeam($userId)
    {
        $query = "SELECT title as naam
                  FROM J3_users U
                  LEFT JOIN J3_user_usergroup_map M on U.id = M.user_id
                  LEFT JOIN J3_usergroups G on G.id = M.group_id
                  WHERE M.user_id = :userId and G.parent_id in (select id from J3_usergroups where title = 'Teams')";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];

        $team = $this->database->Execute($query, $params);
        if (count($team) == 0) {
            return null;
        }

        return ToNevoboName($team[0]['naam']);
    }

    public function GetSpelers($team)
    {
        $team = ToSkcName($team);
        $query = "SELECT U.id, name as naam
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE G.title = :team";
        $params = [new Param(":team", $team, PDO::PARAM_STR)];
        return $this->database->Execute($query, $params);
    }

    public function GetCoachTeam($userId)
    {
        $query = "SELECT G.title as naam
                  FROM J3_usergroups G
                  INNER JOIN J3_user_usergroup_map M on G.id = M.group_id
                  WHERE M.user_id = :userId and (G.title like 'Coach Dames %' or G.title like 'Coach Herem %')";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];

        $team = $this->database->Execute($query, $params);
        if (count($team) == 0) {
            return null;
        }

        $coachTeam = substr($team[0]['naam'], 6);
        return ToNevoboName($coachTeam);
    }

    private function InitJoomla()
    {
        if (!defined('_JEXEC')) {
            define('_JEXEC', 1);

            if (DIRECTORY_SEPARATOR == '/') {
                define('JPATH_BASE', '/home/deb105013n2/domains/skcvolleybal.nl/public_html/');
            } else {
                define('JPATH_BASE', "C:\skc-website\\");
            }

            require_once JPATH_BASE . '/includes/defines.php';
            require_once JPATH_BASE . '/includes/framework.php';
            $mainframe = JFactory::getApplication('site');
            $mainframe->initialise();
        }
    }

    public function Login($username, $password)
    {
        $this->InitJoomla();

        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        $joomlaApp = JFactory::getApplication('site');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, password')
            ->from('#__users')
            ->where('username=' . $db->quote($credentials['username']));

        $db->setQuery($query);
        $result = $db->loadObject();
        if ($result) {
            $match = JUserHelper::verifyPassword($credentials['password'], $result->password, $result->id);
            if ($match === true) {
                $joomlaApp->login($credentials);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
