<?php
include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';

class GetUsers implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId(false);
        $isWebcie = $this->joomlaGateway->IsWebcie($userId);
        if ($isWebcie === false) {
            InternalServerError("Je bent geen webcie");
        }

        $name = $data->name ?? null;
        $result = [];

        if ($name && 3 <= strlen($name)) {
            $users = $this->joomlaGateway->GetUsersWithName($name);
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