<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use UnexpectedValueException;

class AddBarcieAanwezigheid implements Interactor
{
    public function __construct(
        IWordPressGateway $wordPressGateway,
        IBarcieGateway $barcieGateway
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
            throw new UnexpectedValueException("Er bestaat geen bardag $date");
        }

        $bardienst = $this->barcieGateway->GetBardienst($bardag, $barlid, $data->shift);
        if ($bardienst->id === null) {
            $this->barcieGateway->InsertBardienst($bardienst, $bardag->id);
        }
    }
}
