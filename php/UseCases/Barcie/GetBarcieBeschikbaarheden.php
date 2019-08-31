<?php
include_once 'IInteractorWithData.php';
include_once 'BarcieGateway.php';
include_once 'JoomlaGateway.php';

class GetBarcieBeschikbaarheden implements IInteractorWithData
{

    public function __construct($database)
    {
        $this->barcieGateway = new BarcieGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent geen (helaas) geen teamcoordinator");
        }
        $date = $data->date ?? null;
        if (!$date) {
            throw new InvalidArgumentException("Date is leeg");
        }

        $barcieleden = $this->barcieGateway->GetBarcieleden();
        $beschikbaarheden = $this->barcieGateway->GetBeschikbaarhedenForDate($date);
        $result = (object) [
            "Ja" => [],
            "Nee" => [],
            "Onbekend" => [],
        ];

        foreach ($beschikbaarheden as $beschikbaarheid) {
            $barcielid = $this->GetBarcielidById($barcieleden, $beschikbaarheid->user_id);
            $newBeschikbaarheid = (object) [
                "id" => $barcielid->id,
                "naam" => $barcielid->naam,
                "aantalDiensten" => $barcielid->aantalDiensten,
            ];
            if ($beschikbaarheid->is_beschikbaar === "Ja") {
                $result->Ja[] = $newBeschikbaarheid;
            } else {
                $result->Nee[] = $newBeschikbaarheid;
            }

            $barcieleden = array_filter($barcieleden, function ($currentBarcielid) use ($barcielid) {
                return $barcielid->id !== $currentBarcielid->id;
            });
        }

        $result->Onbekend = array_values($barcieleden);

        exit(json_encode($result));
    }

    private function GetBarcielidById($barcieleden, $userId)
    {
        foreach ($barcieleden as $barcielid) {
            if ($barcielid->id == $userId) {
                return $barcielid;
            }
        }
        return null;
    }
}
