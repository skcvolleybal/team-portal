<?php


class JoomlaGateway
{
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

    public function GetUser($userId)
    {
        $query = 'SELECT * 
                  FROM J3_users
                  WHERE id = :id';
        $params = [new Param(Column::Id, $userId, PDO::PARAM_INT)];
        $users = $this->database->Execute($query, $params);
        if (count($users) == 1) {
            return new Persoon($users[0]->id, $users[0]->name, $users[0]->email);
        }
        return null;
    }

    public function GetScheidsrechterByName($scheidsrechter)
    {
        if (empty($scheidsrechter)) {
            return null;
        }

        $query = 'SELECT U.id, name
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE U.name = :scheidsrechter and
                        G.id in (SELECT id FROM J3_usergroups WHERE title = "Scheidsrechters")';
        $params = [
            new Param(':scheidsrechter', $scheidsrechter, PDO::PARAM_STR),
        ];
        $scheidsrechters = $this->database->Execute($query, $params);
        if (count($scheidsrechters) == 0) {
            throw new UnexpectedValueException('Unknown scheidsrechter: $scheidsrechter');
        };
        return $scheidsrechters[0];
    }

    public function GetTeamByNaam($naam)
    {
        $query = 'SELECT * FROM J3_usergroups
                  WHERE title = :naam';
        $params = [new Param(':naam', $naam, PDO::PARAM_STR)];
        $teams = $this->database->Execute($query, $params);
        if (count($teams) == 0) {
            return null;
        }
        return $teams[0];
    }

    public function DoesUserIdExist($userId)
    {
        $query = 'SELECT id FROM J3_users WHERE id = :userId';
        $params = [new Param(Column::UserId, $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        return count($result) > 0;
    }

    public function GetUsersWithName($name)
    {
        $query = "SELECT * 
                  FROM J3_users 
                  WHERE name like '%$name%'
                  ORDER BY 
                  CASE 
                    WHEN name LIKE '$name%' THEN 0 ELSE 1 end,
                  name  
                  LIMIT 0, 5";
        return $this->database->Execute2($query);
    }

    private function IsUserInUsergroup($userId, $usergroup)
    {
        $query = 'SELECT *
                  FROM J3_user_usergroup_map M
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE M.user_id = :userId and G.title = :usergroup';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(':usergroup', $usergroup, PDO::PARAM_STR),
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

    public function IsTeamcoordinator($userId)
    {
        return $this->IsUserInUsergroup($userId, 'Teamcoordinator');
    }

    public function IsBarcie($userId)
    {
        return $this->IsUserInUsergroup($userId, 'Barcie');
    }

    public function IsCoach($userId)
    {
        $query = 'SELECT *
                  FROM J3_user_usergroup_map M
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE M.user_id = :userId and G.title LIKE :usergroup';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(':usergroup', 'Coach %', PDO::PARAM_STR),
        ];
        $result = $this->database->Execute($query, $params);
        return count($result) > 0;
    }

    public function GetTeam($userId)
    {
        $query = 'SELECT title as naam
                  FROM J3_users U
                  LEFT JOIN J3_user_usergroup_map M on U.id = M.user_id
                  LEFT JOIN J3_usergroups G on G.id = M.group_id
                  WHERE M.user_id = :userId and G.parent_id in (select id from J3_usergroups where title = \'Teams\')';
        $params = [new Param(Column::UserId, $userId, PDO::PARAM_INT)];

        $team = $this->database->Execute($query, $params);
        if (count($team) == 0) {
            return null;
        }

        return ToNevoboName($team[0]->naam);
    }

    public function GetTeamgenoten($team)
    {
        $team = ToSkcName($team);
        $query = 'SELECT 
                    U.id, 
                    name as naam,
                    email
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE G.title = :team
                  ORDER BY name';
        $params = [new Param(':team', $team, PDO::PARAM_STR)];
        return $this->database->Execute($query, $params);
    }

    public function GetCoachTeam($userId)
    {
        $query = 'SELECT G.title as naam
                  FROM J3_usergroups G
                  INNER JOIN J3_user_usergroup_map M on G.id = M.group_id
                  WHERE M.user_id = :userId and G.title like \'Coach %\'';
        $params = [new Param(Column::UserId, $userId, PDO::PARAM_INT)];

        $team = $this->database->Execute($query, $params);
        if (count($team) == 0) {
            return null;
        }

        $coachTeam = substr($team[0]->naam, 6);
        return ToNevoboName($coachTeam);
    }

    public function GetCoaches($teamnaam)
    {
        return $this->GetUsersInGroup('Coach ' . ToSkcName($teamnaam));
    }

    public function GetTrainers($teamnaam)
    {
        return $this->GetUsersInGroup('Trainer ' . ToSkcName($teamnaam));
    }

    public function GetUsersInGroup($groupname)
    {
        $query = 'SELECT
                    U.id,
                    U.name as naam
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE G.title = :groupname';
        $params = [new Param(':groupname', $groupname, PDO::PARAM_STR)];
        return $this->database->Execute($query, $params);
    }

    public function InitJoomla()
    {
        $mainframe = JFactory::getApplication('site');
        $mainframe->initialise();
    }

    public function Login($username, $password)
    {
        $this->InitJoomla();

        $credentials = (object) [
            'username' => $username,
            'password' => $password,
        ];

        $joomlaApp = JFactory::getApplication('site');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, password')
            ->from('#__users')
            ->where('username=' . $db->quote($credentials->username));

        $db->setQuery($query);
        $result = $db->loadObject();
        if ($result) {
            $match = JUserHelper::verifyPassword($credentials->password, $result->password, $result->id);
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
}
