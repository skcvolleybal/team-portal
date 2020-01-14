<?php

class GetCurrentUserInteractor implements IInteractor
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            throw new UnauthorizedException();
        }

        $user = $this->joomlaGateway->GetUser($userId);
        return (object) [
            'naam' => $user->naam,
            'id' => $user->id
        ];
    }
}
