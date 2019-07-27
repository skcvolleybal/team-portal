<?php
include_once 'IInteractorWithData.php';
include_once 'BarcieGateway.php';
include_once 'JoomlaGateway.php';

class DeleteBarcieAanwezigheid implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->barcieGateway = new BarcieGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent geen scheidsco");
        }

        $naam = $data->naam ?? null;
        $date = $data->date ?? null;
        $shift = $data->shift ?? null;

        if ($naam === null) {
            InternalServerError("Naam is leeg");
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
        $barcielid = $this->barcieGateway->GetBarcielidByName($date);
        if ($barcielid === null) {
            InternalServerError("Barcielid met naam $naam bestaat niet");
        }

        $aanwezigheid = $this->barcieGateway->GetAanwezigheid($dayId, $barcielid["id"], $shift);
        if ($aanwezigheid) {
            $this->barcieGateway->DeleteAanwezigheid($aanwezigheid['id']);
        } else {
            InternalServerError("Aanwezigheid bestaat niet");
        }
    }
}
