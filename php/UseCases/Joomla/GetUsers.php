<?php

class GetUsers implements IInteractorWithData
{

    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId(false);
        if (!$this->joomlaGateway->IsWebcie($userId)) {
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

        return $result;
    }
}
