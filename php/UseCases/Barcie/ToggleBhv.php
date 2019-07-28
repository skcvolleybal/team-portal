<?php
include 'IInteractorWithData.php';
include_once 'BarcieGateway.php';
include_once 'JoomlaGateway.php';

class ToggleBhv implements IInteractorWithData
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

        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent geen scheidsco");
        }

        $barcielidId = $data->barcielidId ?? null;
        $date = $data->date ?? null;
        $shift = $data->shift ?? null;

        if ($barcielidId === null) {
            InternalServerError("barcielidId is leeg");
        }
        if ($date === null) {
            InternalServerError("Date is leeg");
        }
        if ($shift === null) {
            InternalServerError("Shift is leeg");
        }

        $dayId = $this->barcieGateway->GetDateId($date);
        if ($dayId === null) {
            InternalServerError("Er bestaat geen barciedag $date");
        }

        $aanwezigheid = $this->barcieGateway->GetAanwezigheid($dayId, $barcielidId, $shift);
        if ($aanwezigheid) {
            $this->barcieGateway->ToggleBhv($aanwezigheid['id']);
        } else {
            InternalServerError("Aanwezigheid bestaat niet");
        }
    }
}
