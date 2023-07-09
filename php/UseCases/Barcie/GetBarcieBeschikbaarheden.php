<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Barlid;
use TeamPortal\Entities\Persoon;
use TeamPortal\Gateways\BarcieGateway;
use TeamPortal\Gateways\WordPressGateway;

class GetBarcieBeschikbaarheden implements Interactor
{
    public function __construct(
        BarcieGateway $barcieGateway,
        WordPressGateway $wordPressGateway
    ) {
        $this->barcieGateway = $barcieGateway;
        $this->wordPressGateway = $wordPressGateway;
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
            if ($barlid === null) {
                continue;
            }

            if ($beschikbaarheid->isBeschikbaar) {
                $result->Ja[] = $barlid;
            } else {
                $result->Nee[] = $barlid;
            }

            $barleden = array_filter($barleden, function ($currentBarlid) use ($barlid) {
                return $barlid->id !== $currentBarlid->id;
            });
        }

        $compareFunction = "Compare";
        usort($result->Ja, [Barlid::class, $compareFunction]);
        usort($result->Nee, [Barlid::class, $compareFunction]);
        usort($result->Onbekend, [Barlid::class, $compareFunction]);

        $result->Onbekend = array_values($barleden);

        return $result;
    }

    private function GetBarlid(array $barleden, Persoon $persoon): ?Persoon
    {
        foreach ($barleden as $barlid) {
            if ($barlid->id === $persoon->id) {
                return $barlid;
            }
        }
        return null;
    }
}
