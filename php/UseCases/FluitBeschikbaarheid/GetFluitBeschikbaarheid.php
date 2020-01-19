<?php

class GetFluitBeschikbaarheid implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway,
        FluitBeschikbaarheidGateway $fluitBeschikbaarheidGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->fluitBeschikbaarheidGateway = $fluitBeschikbaarheidGateway;
    }

    public function Execute(object $data = null): array
    {
        $user = $this->joomlaGateway->GetUser();
        $team = $this->joomlaGateway->GetTeam($user);
        $coachteam = $this->joomlaGateway->GetCoachTeam($user);
        $beschikbaarheden = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheden($user);

        $wedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
        $coachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($coachteam);

        $rooster = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 365);
        foreach ($wedstrijddagen as $wedstrijddag) {
            $dag = new WedstrijddagModel($wedstrijddag);

            $speelWedstrijd = Wedstrijd::GetWedstrijdWithDate($wedstrijden, $wedstrijddag->date);
            $coachWedstrijd = Wedstrijd::GetWedstrijdWithDate($coachWedstrijden, $wedstrijddag->date);
            $eigenWedstrijden = array_filter([$speelWedstrijd, $coachWedstrijd], function ($value) {
                return $value !== null;
            });
            foreach ($eigenWedstrijden as $wedstrijd) {
                $newWedstrijd = new WedstrijdModel($wedstrijd);
                $newWedstrijd->SetPersonalInformation($wedstrijd, $user, $team, $coachteam);
                $dag->eigenWedstrijden[] = $newWedstrijd;
            }

            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                $newSpeeltijd = new SpeeltijdModel($speeltijd);
                $newSpeeltijd->isBeschikbaar = Beschikbaarheid::IsBeschikbaar($beschikbaarheden, $speeltijd->time);
                $newSpeeltijd->isMogelijk = Fluitbeschikbaarheid::isFluitenMogelijk($eigenWedstrijden, $speeltijd->time);
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    $newWedstrijd = new WedstrijdModel($wedstrijd);
                    $newWedstrijd->SetPersonalInformation($wedstrijd, $user, $team, $coachteam);
                    $newSpeeltijd->wedstrijden[] = $newWedstrijd;
                }
                $dag->speeltijden[] = $newSpeeltijd;
            }

            $rooster[] = $dag;
        }

        return $rooster;
    }
}
