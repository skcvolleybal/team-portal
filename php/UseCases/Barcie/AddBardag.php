<?php

class AddBardag implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        BarcieGateway $barcieGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data = null): void
    {
        $date = DateFunctions::CreateDateTime($data->date);
        if ($date === false) {
            throw new InvalidArgumentException("Incorrecte dag: $data->data");
        }

        if (DateFunctions::GetYmdNotation($date) < DateFunctions::GetYmdNotation(new DateTime())) {
            throw new UnexpectedValueException("Dag ligt in het verleden");
        }

        $bardag = $this->barcieGateway->GetBardag($date);
        if ($bardag->id === null) {
            $this->barcieGateway->AddBardag($date);
        }
    }
}
