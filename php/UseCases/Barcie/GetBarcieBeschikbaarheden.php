<?php

class GetBarcieBeschikbaarheden implements IInteractorWithData
{
    public function __construct(BarcieGateway $barcieGateway, JoomlaGateway $joomlaGateway)
    {
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent geen (helaas) geen teamcoordinator");
        }
        
        $date = DateFunctions::CreateDateTime($data->date ?? null);
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
            $barcielid = $this->barcieGateway->GetBarcielidById($beschikbaarheid->persoon->id);
            $newBeschikbaarheid = (object) [
                "id" => $barcielid->id,
                "naam" => $barcielid->naam,
                "aantalDiensten" => $barcielid->aantalDiensten,
            ];
            if ($beschikbaarheid->isBeschikbaar) {
                $result->Ja[] = $newBeschikbaarheid;
            } else {
                $result->Nee[] = $newBeschikbaarheid;
            }

            $barcieleden = array_filter($barcieleden, function ($currentBarcielid) use ($barcielid) {
                return $barcielid->id !== $currentBarcielid->id;
            });
        }

        $result->Onbekend = array_values($barcieleden);

        return $result;
    }

    private function GetBarcielidById(array $barcieleden, Persoon $persoon)
    {
        foreach ($barcieleden as $barcielid) {
            if ($barcielid->id == $persoon->id) {
                return $barcielid;
            }
        }
        return null;
    }
}
