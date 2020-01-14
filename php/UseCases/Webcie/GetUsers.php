<?php

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
            throw new UnexpectedValueException("Je bent geen webcie");
        }

        $name = $data->naam ?? null;
        $result = [];

        if ($name && 3 <= strlen($name)) {
            $users = $this->joomlaGateway->GetUsersWithName($name);
            foreach ($users as $user) {
                $result[] = (object) [
                    "naam" => $user->name,
                    "id" => $user->id,
                ];
            }
        }

        return $result)
    }
}
