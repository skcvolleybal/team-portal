<?php
include 'IInteractor.php';
include 'UserGateway.php';

class IsWebcie implements IInteractor
{

    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
    }

    public function Execute()
    {
        $isWebcie = false;
        $userId = $this->userGateway->GetUserId();
        if ($userId != null) {
            $isWebcie = $this->userGateway->IsWebcie($userId);
        }
        exit(json_encode($isWebcie));
    }
}
