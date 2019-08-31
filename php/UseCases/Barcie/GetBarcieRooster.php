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
        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent geen teamcoordinator");
        }

        $aanwezigheden = $this->barcieGateway->GetBarcieAanwezigheden();
        $rooster = [];
        foreach ($aanwezigheden as $aanwezigheid) {
            $date = $aanwezigheid->date;

            $i = $this->GetDayIndex($rooster, $date);
            if ($i === null) {
                $datum = GetDutchDate(new DateTime($date));

                $newDate = (object) [
                    "date" => $date,
                    "datum" => $datum,
                    "shifts" => [(object) [
                        "barcieleden" => []
                    ]]
                ];
                $rooster[] = $newDate;
                $i = count($rooster) - 1;
            }
            if ($aanwezigheid->userId !== null) {
                $shiftNumber = intval($aanwezigheid->shift);
                for ($j = 0; $j < $shiftNumber; $j++) {
                    if (!isset($rooster[$i]->shifts[$j])) {
                        $rooster[$i]->shifts[] = (object) [
                            "barcieleden" => []
                        ];
                    }
                }
                $rooster[$i]->shifts[$shiftNumber - 1]->barcieleden[] = (object) [
                    "id" => $aanwezigheid->userId,
                    "naam" => $aanwezigheid->naam,
                    "isBhv" => $aanwezigheid->isBhv === "1",
                ];
            }
        }

        exit(json_encode($rooster));
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
