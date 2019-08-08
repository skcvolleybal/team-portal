<?php
include_once 'IInteractorWithData.php';
include_once 'BarcieGateway.php';
include_once 'JoomlaGateway.php';

class GetBarcieLeden implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->barcieGateway = new BarcieGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            throw new UnexpectedValueException("Je bent geen scheidsco");
        }
        $date = $data->date ?? null;
        if (!$date) {
            throw new InvalidArgumentException("Date is leeg");
        }

        $barcieLeden = $this->barcieGateway->GetBarcieLeden();
        $this->beschikbaarheden = $this->barcieGateway->GetBeschikbaarhedenForDate($date);
        $beschikbaarheden = (object) [
            "Ja" => [],
            "Nee" => [],
            "Onbekend" => [],
        ];
        foreach ($barcieLeden as $barcieLid) {
            $beschikbaarheid = $this->GetBeschikbaarheid($barcieLid->id);
            $beschikbaarheden[$beschikbaarheid][] = (object) [
                "id" => $barcieLid->id,
                "naam" => $barcieLid->naam,
                "aantalDiensten" => $barcieLid['aantalDiensten'],
            ];
        }

        return (object) [
            "barcieLeden" => $beschikbaarheden,
        ];
    }

    private function GetBeschikbaarheid($userId)
    {
        foreach ($this->beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->userId == $userId) {
                return $beschikbaarheid->beschikbaarheid;
            }
        }
        return "Onbekend";
    }
}
