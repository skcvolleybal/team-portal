<?php

include_once 'Param.php';
include_once 'Utilities.php';

class UserGateway
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetUserId()
    {
        $this->InitJoomla();

        $session = JFactory::getSession();
        $user = JFactory::getUser();
        if ($user->guest) {
            return null;
        }
        return $user->id;
    }

    public function GetZaalwachten($userId)
    {
        $query = "SELECT Z.*
                  FROM ScheidsApp_zaalwacht Z
                  INNER JOIN J3_user_usergroup_map M on Z.team_id = M.group_id
                  WHERE M.user_id = :userId and Z.date >= CURRENT_DATE()";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        return $this->database->Execute($query, $params);
    }

    public function GetTelbeurten($userId)
    {
        $query = "SELECT Matches.*, G.title as tellers, U.name as scheidsrechter
                  FROM ScheidsApp_matches Matches
                  LEFT JOIN J3_usergroups G on Matches.telteam_id = G.id
                  INNER JOIN J3_user_usergroup_map M on Matches.telteam_id = M.group_id
                  LEFT JOIN J3_users U on U.id = Matches.user_id
                  WHERE M.user_id = :userId and Matches.date >= CURRENT_DATE()";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        foreach ($result as &$row) {
            $row['tellers'] = ConvertToNevoboName($row['tellers']);
        }
        return $result;
    }

    public function GetFluitbeurten($userId)
    {
        $query = "SELECT Matches.*, G.title as tellers, U.name as scheidsrechter
                  FROM ScheidsApp_matches Matches
                  LEFT JOIN J3_usergroups G on Matches.telteam_id = G.id
                  LEFT JOIN J3_users U on U.id = Matches.user_id
                  WHERE Matches.user_id = :userId and Matches.date >= CURRENT_DATE()";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        foreach ($result as &$row) {
            $row['tellers'] = ConvertToNevoboName($row['tellers']);
        }
        return $result;
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

        return ConvertToNevoboName($team[0]['naam']);
    }

    public function GetSpelers($team)
    {
        $team = GetSkcTeam($team);
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
        return ConvertToNevoboName($coachTeam);
    }

    private function InitJoomla()
    {
        if (!defined('_JEXEC')) {
            define('_JEXEC', 1);

            if (DIRECTORY_SEPARATOR == '/') {
                define('JPATH_BASE', '/home/deb105013n2/domains/skcvolleybal.nl/public_html/');
            } else {
                define('JPATH_BASE', realpath(dirname(__DIR__) . '/../joomla/'));
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
