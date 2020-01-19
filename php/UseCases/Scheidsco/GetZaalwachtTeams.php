<?php

class GetZaalwachtTeams implements Interactor
{
    public function __construct(
        ZaalwachtGateway $zaalwachtGateway,
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway
    ) {
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null): object
    {
        $date = DateFunctions::CreateDateTime($data->date);
        if ($date === null) {
            throw new InvalidArgumentException("Geen datum meegegeven");
        }

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        $samenvattingen = $this->zaalwachtGateway->GetZaalwachtSamenvatting();
        $spelendeTeams = $this->GetSpelendeTeamsForDate($uscWedstrijden, $date);

        $result = new Teamsamenvatting();
        foreach ($samenvattingen as $samenvatting) {
            if (in_array($samenvatting->team->GetSkcNaam(), $spelendeTeams)) {
                $result->spelendeTeams[] = $this->MapToUsecaseModel($samenvatting);
            } else {
                $result->overigeTeams[] = $this->MapToUsecaseModel($samenvatting);
            }
        }
        return $result;
    }

    private function GetSpelendeTeamsForDate($wedstrijden, $date)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            if (DateFunctions::AreDatesEqual($wedstrijd->timestamp, $date)) {
                $result[] = $wedstrijd->team1->GetSkcNaam();
            }
        }
        return $result;
    }

    private function MapToUsecaseModel($samenvatting)
    {
        return (object) [
            "naam" => $samenvatting->team->GetSkcNaam(),
            "aantal" => $samenvatting->aantal,
        ];
    }
}
