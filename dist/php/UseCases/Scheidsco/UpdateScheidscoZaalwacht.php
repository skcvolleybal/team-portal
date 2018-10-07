<?php
include 'IInteractorWithData.php';
include 'IndelingGateway.php';
include 'UserGateway.php';

class UpdateScheidscoZaalwacht implements IInteractorWithData
{
    private $indelingGateway;
    private $userGateway;

    public function __construct($database)
    {
        $this->indelingGateway = new IndelingGateway($database);
        $this->userGateway = new UserGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->userGateway->GetUserId();
        if ($userId == null) {
            UnauthorizedResult();
        }

        if (!$this->userGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }

        $datum = $data->date;
        $team = $data->team;

        $this->indelingGateway->UpdateScheidscoZaalwacht($datum, $team);
        exit();
    }
}
