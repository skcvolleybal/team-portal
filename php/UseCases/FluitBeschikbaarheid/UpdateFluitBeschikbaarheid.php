<?php
include 'IInteractorWithData.php';
include 'UserGateway.php';
include 'FluitBeschikbaarheidGateway.php';

class UpdateFluitBeschikbaarheid implements IInteractorWithData
{
    private $fluitBeschikbaarheidGateway;

    public function __construct($database)
    {
        $this->fluitBeschikbaarheidGateway = new FluitBeschikbaarheid($database);
        $this->userGateway = new UserGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->userGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->userGateway->IsScheidsrechter($userId)) {
            InternalServerError("Je bent (helaas) geen scheidsrechter");
        }

        $datum = $data->datum;
        $tijd = $data->tijd;
        $beschikbaarheid = $data->beschikbaarheid;

        $this->fluitBeschikbaarheidGateway->UpdateBeschikbaarheid($userId, $datum, $tijd, $beschikbaarheid);
        exit();
    }
}
