<?php

class GetBarcieBeschikbaarheden implements Interactor
{
    public function __construct(BarcieGateway $barcieGateway, JoomlaGateway $joomlaGateway)
    {
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $date = DateFunctions::CreateDateTime($data->date);
        if (!$date) {
            throw new InvalidArgumentException("Incorrecte Datum: $data->date");
        }

        $barleden = $this->barcieGateway->GetBarleden();
        $beschikbaarheden = $this->barcieGateway->GetBeschikbaarhedenForDate($date);

        $result = new Beschikbaarheidssamenvatting();
        foreach ($beschikbaarheden as $beschikbaarheid) {
            $barlid = $this->GetBarlid($barleden, $beschikbaarheid->persoon);
            if ($beschikbaarheid->isBeschikbaar) {
                $result->Ja[] = $barlid;
            } else {
                $result->Nee[] = $barlid;
            }

            $barleden = array_filter($barleden, function ($currentBarlid) use ($barlid) {
                return $barlid->id !== $currentBarlid->id;
            });
        }

        $result->Onbekend = array_values($barleden);

        return $result;
    }

    private function GetBarlid(array $barleden, Persoon $persoon)
    {
        foreach ($barleden as $barlid) {
            if ($barlid->id === $persoon->id) {
                return $barlid;
            }
        }
        return null;
    }
}
