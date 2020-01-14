<?php

class DeleteBarcieDag implements IInteractorWithData
{
    public function __construct(JoomlaGateway $joomlaGateway, BarcieGateway $barcieGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent geen teamcoordinator");
        }

        $date = DateFunctions::CreateDateTime($data->date ?? null);
        if ($date === null) {
            throw new InvalidArgumentException("Date is leeg");
        }

        $dayId = $this->barcieGateway->GetDateId($date);

        $barciediensten = $this->barcieGateway->GetBarciediensten();
        foreach ($barciediensten as $barciedienst) {
            if ($barciedienst->persoon) {
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
