<?php


class GetBarcieRooster implements IInteractor
{

    public function __construct(JoomlaGateway $joomlaGateway, BarcieGateway $barcieGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            throw new UnauthorizedException();
        }

        if (!$this->joomlaGateway->isBarcie($userId)) {
            throw new UnexpectedValueException("Je bent geen barcie");
        }

        $barciediensten = $this->barcieGateway->GetBarciediensten();
        $rooster = [];
        foreach ($barciediensten as $barciedienst) {
            $i = $this->GetDayIndex($rooster, $barciedienst->date);
            if ($i === null) {
                $rooster[] = (object) [
                    "date" => DateFunctions::GetYmdNotation($barciedienst->date),
                    "datum" => DateFunctions::GetDutchDate($barciedienst->date),
                    "shifts" => [(object) [
                        "barcieleden" => []
                    ]]
                ];
                $i = count($rooster) - 1;
            }

            if ($barciedienst->persoon !== null) {
                for ($j = 0; $j < $barciedienst->shift; $j++) {
                    if (!isset($rooster[$i]->shifts[$j])) {
                        $rooster[$i]->shifts[] = (object) [
                            "barcieleden" => []
                        ];
                    }
                }

                $rooster[$i]->shifts[$barciedienst->shift - 1]->barcieleden[] = (object) [
                    "id" => $barciedienst->persoon->id,
                    "naam" => $barciedienst->persoon->naam,
                    "isBhv" => $barciedienst->isBhv,
                ];
            }
        }

        return $rooster;
    }

    private function GetDayIndex(array $rooster, DateTime $date)
    {
        foreach ($rooster as $i => $item) {
            if ($item->date == $date) {
                return $i;
            }
        }
        return null;
    }
}
