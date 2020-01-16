<?php

class DeleteBarcieAanwezigheid implements IInteractorWithData
{

    public function __construct(
        BarcieGateway $barcieGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
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

        $dienst = $this->barcieGateway->GetBarciedienst($dayId, $barcielidId, $shift);
        if ($dienst !== null) {
            $this->barcieGateway->DeleteBarciedienst($dienst);
        }
    }
}
