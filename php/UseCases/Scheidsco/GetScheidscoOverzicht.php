<?php

class GetScheidscoOverzicht implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        TelFluitGateway $telFluitGateway,
        NevoboGateway $NevoboGateway,
        ZaalwachtGateway $zaalwachtGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->nevoboGateway = $NevoboGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
    }

    public function Execute(object $data = null): array
    {
        $result = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 365);

        foreach ($wedstrijddagen as $wedstrijddag) {
            $wedstrijddag->zaalwacht = $this->zaalwachtGateway->GetZaalwacht($wedstrijddag->date);
            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as &$wedstrijd) {
                    $indeling = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
                    if ($indeling !== null) {
                        $wedstrijd->scheidsrechter = $indeling->scheidsrechter;
                        $wedstrijd->telteam = $indeling->telteam;
                    }
                }
            }

            $result[] = $this->MapToUseCaseModel($wedstrijddag);
        }

        return $result;
    }

    private function MapToUseCaseModel(Wedstrijddag $dag)
    {
        $result = new WedstrijddagModel($dag);

        foreach ($dag->speeltijden as $i => $speeltijd) {
            $result->speeltijden[] = new SpeeltijdModel($speeltijd);

            foreach ($speeltijd->wedstrijden as $wedstrijd) {
                $newWedstrijd = new WedstrijdModel($wedstrijd);
                $newWedstrijd->teams = $wedstrijd->team1->naam . " - " . $wedstrijd->team2->naam;
                $newWedstrijd->scheidsrechter = $wedstrijd->scheidsrechter ? $wedstrijd->scheidsrechter->naam : null;
                $newWedstrijd->tellers = $wedstrijd->telteam ? $wedstrijd->telteam->GetSkcNaam() : null;

                $result->speeltijden[$i]->wedstrijden[] = $newWedstrijd;
            }
        }
        return $result;
    }
}
