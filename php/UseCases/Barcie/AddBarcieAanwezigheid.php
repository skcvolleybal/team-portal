<?php

class AddBarcieAanwezigheid implements IInteractorWithData
{
    public function __construct($configuration, $database)
    {
        $this->barcieGateway = new BarcieGateway($database);
        $this->joomlaGateway = new JoomlaGateway($configuration, $database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent geen teamcoordinator");
        }

        $barcielidId = $data->barcielidId ?? null;
        $date = $data->date ?? null;
        $shift = $data->shift ?? null;

        if ($barcielidId === null) {
            throw new InvalidArgumentException("barcielidId is leeg");
        }
        if ($date === null) {
            throw new InvalidArgumentException("Date is leeg");
        }
        if ($shift === null) {
            throw new InvalidArgumentException("Shift is leeg");
        }

        $dayId = $this->barcieGateway->GetDateId($date);
        if ($dayId === null) {
            throw new UnexpectedValueException("Er bestaat geen barciedag $date");
        }

        $aanwezigheid = $this->barcieGateway->GetAanwezigheid($dayId, $barcielidId, $shift);
        if ($aanwezigheid === null) {
            $this->barcieGateway->InsertAanwezigheid($dayId, $barcielidId, $shift);
        } else {
            throw new UnexpectedValueException("Aanwezigheid bestaat al");
        }
    }
}
