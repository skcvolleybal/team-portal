<?php
include 'IInteractor.php';
include_once 'JoomlaGateway.php';

class GetGroups implements IInteractor
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
        $response = [];
        if ($this->joomlaGateway->IsScheidsrechter($userId)) {
            $response[] = "scheidsrechter";
        }
        if ($this->joomlaGateway->IsBarcie($userId)) {
            $response[] = "barcie";
        }
        if ($this->joomlaGateway->IsTeamcoordinator($userId)) {
            $response[] = "scheidsco";
        }
        if ($this->joomlaGateway->IsWebcie($userId)) {
            $response[] = "webcie";
        }
        exit(json_encode($response));
    }
}
