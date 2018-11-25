<?php
include_once 'IInteractorWithData.php';
include_once 'BarcieGateway.php';
include_once 'JoomlaGateway.php';

class AddBarcieDag implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->barcieGateway = new BarcieGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent geen scheidsco");
        }

        $date = $data->date ?? null;
        if ($date === null) {
            InternalServerError("Date is leeg");
        }

        if (new DateTime() > new DateTime($date)) {
            InternalServerError("Dag ligt in het verleden");
        }

        $dayId = $this->barcieGateway->GetDateId($date);
        if ($dayId !== null) {
            InternalServerError("Dag bestaat al");
        } else {
            $this->barcieGateway->AddBarcieDag($date);
        }
    }
}
