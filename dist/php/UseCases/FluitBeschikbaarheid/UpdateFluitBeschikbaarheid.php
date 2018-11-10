<?php
include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';
include_once 'FluitBeschikbaarheidGateway.php';

class UpdateFluitBeschikbaarheid implements IInteractorWithData
{
    private $fluitBeschikbaarheidGateway;

    public function __construct($database)
    {
        $this->fluitBeschikbaarheidGateway = new FluitBeschikbaarheidGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsScheidsrechter($userId)) {
            InternalServerError("Je bent (helaas) geen scheidsrechter");
        }

        $datum = $data->datum;
        $tijd = $data->tijd;
        $beschikbaarheid = $data->beschikbaarheid;

        $this->fluitBeschikbaarheidGateway->UpdateBeschikbaarheid($userId, $datum, $tijd, $beschikbaarheid);
        exit();
    }
}
