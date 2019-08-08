<?php
include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';
include_once 'BarcieGateway.php';

class UpdateBarcieBeschikbaarheid implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->barcieGateway = new BarcieGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsBarcie($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen Barcie lid");
        }

        $date = $data->date;
        $beschikbaarheid = $data->beschikbaarheid;

        $dayId = $this->barcieGateway->GetDateId($date);

        $dbBeschikbaarheid = $this->barcieGateway->GetBeschikbaarheid($userId, $dayId);
        if ($dbBeschikbaarheid) {
            $this->barcieGateway->UpdateBeschikbaarheid($dbBeschikbaarheid->id, $beschikbaarheid);
        } else {
            $this->barcieGateway->InsertBeschikbaarheid($userId, $dayId, $beschikbaarheid);
        }
    }
}
