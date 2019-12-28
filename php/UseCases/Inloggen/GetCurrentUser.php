<?php
include 'IInteractor.php';
include_once 'JoomlaGateway.php';

class GetCurrentUser implements IInteractor
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        $user = $this->joomlaGateway->GetUser($userId);
        $result = (object) [
            'naam' => $user->naam,
            'id' => $user->id
        ];

        exit(json_encode($result));
    }
}
