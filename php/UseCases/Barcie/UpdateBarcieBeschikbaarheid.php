<?php

class UpdateBarcieBeschikbaarheid implements IInteractorWithData
{

    public function __construct(
        JoomlaGateway $joomlaGateway,
        BarcieGateway $barcieGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            throw new UnauthorizedException();
        }

        if (!$this->joomlaGateway->IsBarcie($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen Barcie lid");
        }

        $date = DateFunctions::CreateDateTime($data->date);
        $barcielid = $this->joomlaGateway->GetUser($userId);
        $isBeschikbaar = $data->isBeschikbaar;

        $dayId = $this->barcieGateway->GetDateId($date);

        $beschikbaarheid = $this->barcieGateway->GetBeschikbaarheid($userId, $dayId) ?? new  Beschikbaarheid(null, new Persoon($barcielid->id, $barcielid->naam), $date, $isBeschikbaar);
        $beschikbaarheid->isBeschikbaar = $isBeschikbaar;
        if ($beschikbaarheid->id === null) {
            if ($beschikbaarheid->isBeschikbaar !== null) {
                $this->barcieGateway->InsertBeschikbaarheid($beschikbaarheid, $dayId);
            }
        } else {
            if ($beschikbaarheid->isBeschikbaar === null) {
                $this->barcieGateway->DeleteBeschikbaarheid($beschikbaarheid);
            } else {
                $this->barcieGateway->UpdateBeschikbaarheid($beschikbaarheid);
            }
        }
    }
}
