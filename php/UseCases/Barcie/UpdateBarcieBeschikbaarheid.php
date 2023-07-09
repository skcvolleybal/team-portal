<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways;
use UnexpectedValueException;

class UpdateBarcieBeschikbaarheid implements Interactor
{

    public function __construct(
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\BarcieGateway $barcieGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data = null)
    {
        $date = DateFunctions::CreateDateTime($data->date);
        $user = $this->wordPressGateway->GetUser();
        $isBeschikbaar = $data->isBeschikbaar;

        $bardag = $this->barcieGateway->GetBardag($date);
        if ($bardag->id === null) {
            throw new UnexpectedValueException("Dag '$date' bestaat niet");
        }

        $beschikbaarheid = $this->barcieGateway->GetBeschikbaarheid($user, $bardag);
        $beschikbaarheid->isBeschikbaar = $isBeschikbaar;
        if ($beschikbaarheid->id === null) {
            if ($beschikbaarheid->isBeschikbaar !== null) {
                $this->barcieGateway->InsertBeschikbaarheid($beschikbaarheid);
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
