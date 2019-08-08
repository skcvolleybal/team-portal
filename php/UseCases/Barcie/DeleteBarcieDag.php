<?php
include_once 'IInteractorWithData.php';
include_once 'BarcieGateway.php';
include_once 'JoomlaGateway.php';

class DeleteBarcieDag implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->barcieGateway = new BarcieGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            throw new UnexpectedValueException("Je bent geen scheidsco");
        }

        $date = $data->date ?? null;
        if ($date === null) {
            throw new InvalidArgumentException("Date is leeg");
        }

        $dayId = $this->barcieGateway->GetDateId($date);

        $aanwezigheden = $this->barcieGateway->GetBarcieAanwezigheden();
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid->date == $date && $aanwezigheid->userId) {
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
