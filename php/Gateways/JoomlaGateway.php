<?php

class JoomlaGateway
{
    public function __construct(Configuration $configuration, Database $database)
    {
        $this->configuration = $configuration;
        $this->database = $database;
    }

    public function GetUserId(bool $forceImpersonation = true): ?int
    {
        $this->InitJoomla();

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

    public function GetUser(int $userId): Persoon
    {
        $query = 'SELECT * 
                  FROM J3_users
                  WHERE id = ?';
        $params = [$userId];
        $users = $this->database->Execute($query, $params);
        if (count($users) == 1) {
            return new Persoon($users[0]->id, $users[0]->name, $users[0]->email);
        }
        return null;
    }

    public function GetScheidsrechter(int $id): Scheidsrechter
    {
        $query = 'SELECT U.id, name, email
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE U.id = ? and
                        G.id in (
                            SELECT id FROM J3_usergroups WHERE title = "Scheidsrechters"
                        )';
        $params = [$id];
        $result = $this->database->Execute($query, $params);
        if (count($result) != 1) {
            throw new UnexpectedValueException('Unknown scheidsrechter: $scheidsrechter');
        };
        return new Scheidsrechter($result[0]->id, $result[0]->name, $result[0]->email);
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

    public function DoesUserIdExist(int $userId): bool
    {
        $query = 'SELECT id FROM J3_users WHERE id = ?';
        $params = [$userId];
        $result = $this->database->Execute($query, $params);
        return count($result) > 0;
    }

    public function GetUsersWithName(string $name): array
    {
        $query = "SELECT * 
                  FROM J3_users 
                  WHERE name like '%$name%'
                  ORDER BY 
                  CASE 
                    WHEN name LIKE '$name%' THEN 0 ELSE 1 end,
                  name  
                  LIMIT 0, 5";
        return $this->database->Execute($query);
    }

    private function IsUserInUsergroup(int $userId, string $usergroup): bool
    {
        $query = 'SELECT *
                  FROM J3_user_usergroup_map M
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE M.user_id = ? and G.title = ?';
        $params = [$userId, $usergroup];
        $result = $this->database->Execute($query, $params);
        return count($result) > 0;
    }

    public function IsScheidsrechter(int $userId): bool
    {
        return $this->IsUserInUsergroup($userId, 'Scheidsrechters');
    }

    public function IsWebcie(int $userId): bool
    {
        return $this->IsUserInUsergroup($userId, 'Super Users');
    }

    public function IsTeamcoordinator(int $userId): bool
    {
        return $this->IsUserInUsergroup($userId, 'Teamcoordinator');
    }

    public function IsBarcie(int $userId): bool
    {
        return $this->IsUserInUsergroup($userId, 'Barcie');
    }

    public function IsCoach(int $userId): bool
    {
        $query = 'SELECT *
                  FROM J3_user_usergroup_map M
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE M.user_id = ? and G.title LIKE "Coach %"';
        $params = [$userId];
        $result = $this->database->Execute($query, $params);
        return count($result) > 0;
    }

    public function GetTeam(int $userId): Team
    {
        $query = 'SELECT 
                    G.id,
                    title as naam
                  FROM J3_users U
                  LEFT JOIN J3_user_usergroup_map M on U.id = M.user_id
                  LEFT JOIN J3_usergroups G on G.id = M.group_id
                  WHERE M.user_id = ? and G.parent_id in (select id from J3_usergroups where title = \'Teams\')';
        $params = [$userId];

        $team = $this->database->Execute($query, $params);
        if (count($team) != 1) {
            return null;
        }

        return new Team($team[0]->naam, $team[0]->id);
    }

    public function GetTeamgenoten(Team $team): array
    {
        $query = 'SELECT 
                    U.id, 
                    name as naam,
                    email
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE G.title = ?
                  ORDER BY name';
        $params = [$team->GetSkcNaam($team)];
        $rows =  $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Persoon($row->id, $row->naam, $row->email);
        }
        return $result;
    }

    public function GetCoachTeam(int $userId): ?Team
    {
        $query = 'SELECT 
                    G2.id,
                    G2.title as naam
                  FROM J3_usergroups G
                  INNER JOIN J3_user_usergroup_map M on G.id = M.group_id
                  INNER JOIN J3_usergroups G2 on G2.title = SUBSTRING(G.title, 7)
                  WHERE M.user_id = ? and G.title like \'Coach %\'';
        $params = [$userId];

        $team = $this->database->Execute($query, $params);
        if (count($team) != 1) {
            return null;
        }

        return new Team($team[0]->naam, $team[0]->id);
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
                    U.name as naam
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE G.title = ?';
        $params = [$groupname];
        return $this->database->Execute($query, $params);
    }

    public function InitJoomla()
    {
        define('JPATH_BASE', $this->configuration->JpathBase);
        define('_JEXEC', 1);

        require_once JPATH_BASE . '/includes/defines.php';
        require_once JPATH_BASE . '/includes/framework.php';

        $mainframe = JFactory::getApplication('site');
        $mainframe->initialise();
    }

    public function Login(string $username, string $password)
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
