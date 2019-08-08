<?php
include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';
include_once 'FluitBeschikbaarheidGateway.php';

class UpdateFluitBeschikbaarheid implements IInteractorWithData
{
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
            throw new UnexpectedValueException("Je bent (helaas) geen scheidsrechter");
        }

        $datum = $data->datum;
        $tijd = $data->tijd;
        $beschikbaarheid = $data->beschikbaarheid;

        if (!DateTime::createFromFormat('Y-m-d', $datum)) {
            throw new InvalidArgumentException("Unknown date: $datum");
        }
        if (!DateTime::createFromFormat('H:i:s', $tijd)) {
            throw new InvalidArgumentException("Unknown time: $tijd");
        }
        if (!in_array($beschikbaarheid, ["Ja", "Nee", "Onbekend"])) {
            throw new InvalidArgumentException("Unknown beschikbaarheid: $beschikbaarheid");
        }

        $dbBeschikbaarheid = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheid($userId, $datum, $tijd);
        if ($dbBeschikbaarheid == null) {
            $this->fluitBeschikbaarheidGateway->Insert($userId, $datum, $tijd, $beschikbaarheid);
        } else {
            if ($beschikbaarheid == "Onbekend") {
                $this->fluitBeschikbaarheidGateway->Delete($dbBeschikbaarheid->id);
            } else {
                $this->fluitBeschikbaarheidGateway->Update($dbBeschikbaarheid->id, $beschikbaarheid);
            }
        }

        exit();
    }
}
