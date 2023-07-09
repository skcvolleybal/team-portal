<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways;

class DeleteBarcieAanwezigheid implements Interactor
{
    public function __construct(
        Gateways\BarcieGateway $barcieGateway,
        Gateways\WordPressGateway $wordPressGateway
    ) {
        $this->barcieGateway = $barcieGateway;
        $this->wordPressGateway = $wordPressGateway;
    }

    public function Execute(object $data = null): void
    {
        if ($data->barlidId === null) {
            throw new InvalidArgumentException("barlidId is leeg");
        }
        if ($data->date === null) {
            throw new InvalidArgumentException("Date is leeg");
        }
        if ($data->shift === null) {
            throw new InvalidArgumentException("Shift is leeg");
        }

        $date = DateFunctions::CreateDateTime($data->date);
        $barlid = $this->wordPressGateway->GetUser($data->barlidId);
        $bardag = $this->barcieGateway->GetBardag($date);
        if ($bardag->id === null) {
            return;
        }

        $dienst = $this->barcieGateway->GetBardienst($bardag, $barlid, $data->shift);
        if ($dienst !== null) {
            $this->barcieGateway->DeleteBardienst($dienst);
        }
    }
}
