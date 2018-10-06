<?php
include 'IInteractorWithData.php';
include 'UserGateway.php';
include 'FluitBeschikbaarheid.php';

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

        $datum = $data->datum;
        $tijd = $data->tijd;
        $beschikbaarheid = $data->beschikbaarheid;

        $this->fluitBeschikbaarheidGateway->UpdateBeschikbaarheid($userId, $datum, $tijd, $beschikbaarheid);
        exit();
    }
}
