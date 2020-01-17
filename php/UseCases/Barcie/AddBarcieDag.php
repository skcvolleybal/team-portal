<?php

class AddBardag implements Interactor
{
    public function __construct(JoomlaGateway $joomlaGateway, BarcieGateway $barcieGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data): void
    {
        $date = DateFunctions::CreateDateTime($data->date);
        if ($date === false) {
            throw new InvalidArgumentException("Incorrecte dag: $data->data");
        }

        if (new DateTime() > $date) {
            throw new UnexpectedValueException("Dag ligt in het verleden");
        }

        $bardag = $this->barcieGateway->GetBardag($date);
        if ($bardag->id !== null) {
            throw new UnexpectedValueException("Dag bestaat al");
        } else {
            $this->barcieGateway->AddBardag($date);
        }
    }
}
