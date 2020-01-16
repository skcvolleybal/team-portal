<?php

class ToggleBhv implements IInteractorWithData
{

    public function __construct(JoomlaGateway $joomlaGateway, BarcieGateway $barcieGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data)
    {
        $barcielidId = $data->barcielidId ?? null;
        $date = DateFunctions::CreateDateTime($data->date ?? null);
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
            return;
        }

        $dienst = $this->barcieGateway->Getbarciedienst($dayId, $barcielidId, $shift);
        if ($dienst !== null) {
            $this->barcieGateway->ToggleBhv($dienst);
        }
    }
}
