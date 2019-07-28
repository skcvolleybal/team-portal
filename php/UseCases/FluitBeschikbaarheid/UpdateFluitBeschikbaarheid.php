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

        if (!DateTime::createFromFormat('Y-m-d', $datum)) {
            InternalServerError("Unknown date: $datum");
        }
        if (!DateTime::createFromFormat('H:i:s', $tijd)) {
            InternalServerError("Unknown time: $tijd");
        }
        if (!in_array($beschikbaarheid, ["Ja", "Nee", "Onbekend"])) {
            InternalServerError("Unknown beschikbaarheid: $beschikbaarheid");
        }

        $dbBeschikbaarheid = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheid($userId, $datum, $tijd);
        if ($dbBeschikbaarheid == null) {
            $this->fluitBeschikbaarheidGateway->Insert($userId, $datum, $tijd, $beschikbaarheid);
        } else {
            if ($beschikbaarheid == "Onbekend") {
                $this->fluitBeschikbaarheidGateway->Delete($dbBeschikbaarheid['id']);
            } else {
                $this->fluitBeschikbaarheidGateway->Update($dbBeschikbaarheid['id'], $beschikbaarheid);
            }
        }

        exit();
    }
}
