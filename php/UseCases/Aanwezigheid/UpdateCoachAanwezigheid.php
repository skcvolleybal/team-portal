<?php
include 'IInteractor.php';
include 'JoomlaGateway.php';

class UpdateCoachAanwezigheid implements IInteractor
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
    }
}
