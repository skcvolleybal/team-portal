<?php

class DeleteBarcieDag implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->barcieGateway = new BarcieGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent geen teamcoordinator");
        }

        $date = $data->date ?? null;
        if ($date === null) {
            throw new InvalidArgumentException("Date is leeg");
        }

        $dayId = $this->barcieGateway->GetDateId($date);

        $aanwezigheden = $this->barcieGateway->GetBarcieAanwezigheden();
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid->date == $date && $aanwezigheid->userId) {
                throw new UnexpectedValueException("Datum heeft nog aanwezigheden");
            }
        }
        if ($dayId === null) {
            throw new UnexpectedValueException("Dag bestaat niet");
        } else {
            $this->barcieGateway->DeleteBarcieDay($dayId);
        }
    }
}
