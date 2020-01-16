<?php

class AddBarcieAanwezigheid implements Interactor
{
    public function __construct(BarcieGateway $barcieGateway, JoomlaGateway $joomlaGateway)
    {
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data)
    {
        $barcielidId = $data->barcielidId ?? null;
        $barcielid = $this->barcieGateway->GetBarcielidById($barcielidId);
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
            throw new UnexpectedValueException("Er bestaat geen barciedag $date");
        }

        $barciedienst = $this->barcieGateway->GetBarciedienst($dayId, $barcielidId, $shift) ?? new Barciedienst($date, $barcielid, $shift, false);
        if ($barciedienst->id === null) {
            $this->barcieGateway->InsertBarciedienst($barciedienst, $dayId);
        }
    }
}
