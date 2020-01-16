<?php

class GetCurrentUser
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute()
    {
        $user = $this->joomlaGateway->GetUser();
        return (object) [
            'naam' => $user->naam,
            'id' => $user->id
        ];
    }
}
