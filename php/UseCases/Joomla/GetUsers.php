<?php

class GetUsers implements Interactor
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data)
    {
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
