<?php
include 'IInteractorWithData.php';
include 'UserGateway.php';

class GetUsers implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->userGateway->GetUserId(false);
        $isWebcie = $this->userGateway->IsWebcie($userId);
        if ($isWebcie === false) {
            InternalServerError("Je bent geen webcie");
        }

        $name = $data->name ?? null;
        $result = [];

        if ($name != null && 3 <= strlen($name)) {
            $users = $this->userGateway->GetUsersWithName($name);
            foreach ($users as $user) {
                $result[] = [
                    "naam" => $user['name'],
                    "id" => $user['id'],
                ];
            }
        }

        exit(json_encode($result));
    }
}
