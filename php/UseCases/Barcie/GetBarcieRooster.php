<?php
include_once 'IInteractor.php';
include_once 'JoomlaGateway.php';
include_once 'BarcieGateway.php';

class GetBarcieRooster implements IInteractor
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->barcieGateway = new BarcieGateway($database);
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            throw new UnexpectedValueException("Je bent geen scheidsco");
        }

        $aanwezigheden = $this->barcieGateway->GetBarcieAanwezigheden();
        $rooster = [];
        foreach ($aanwezigheden as $aanwezigheid) {
            $date = $aanwezigheid->date;

            $i = $this->GetDayIndex($rooster, $aanwezigheid->date);
            if ($i === null) {
                $datum = GetDutchDate(new DateTime($date));
                $newDate = (object) [
                    "date" => $date,
                    "datum" => $datum,
                    "shifts" => [[
                        "barcieLeden" => [],
                    ]],
                ];
                $rooster[] = $newDate;
                $i = count($rooster) - 1;
            }
            if ($aanwezigheid->userId !== null) {
                $shift = intval($aanwezigheid->shift) - 1;
                for ($j = $shift; count($rooster[$i]->shifts) <= $shift; $j++) {
                    $rooster[$i]->shifts[] = (object) [
                        "barcieLeden" => [],
                    ];
                }
                $rooster[$i]->shifts[$shift]->barcieLeden[] = (object) [
                    "id" => $aanwezigheid->userId,
                    "naam" => $aanwezigheid->naam,
                    "isBhv" => $aanwezigheid->isBhv == "1",
                ];
            }
        }

        return (object) [
            "barcieDagen" => $rooster
        ];
    }

    private function GetDayIndex($rooster, $date)
    {
        foreach ($rooster as $i => $item) {
            if ($item->date == $date) {
                return $i;
            }
        }
        return null;
    }

    private function GetShiftIndex($shifts, $shift)
    {
        foreach ($shifts as $i => $shiftItem) {
            if ($item->date == $date) {
                return $i;
            }
        }
        return null;
    }
}
