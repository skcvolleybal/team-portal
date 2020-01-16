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

    public function Execute(object $data)
    {
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
