<?php

class GetFluitBeschikbaarheid implements Interactor
{
    private $uscCode = 'LDNUN';

    public function __construct(
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway,
        FluitBeschikbaarheidGateway $fluitBeschikbaarheidGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->fluitBeschikbaarheidGateway = $fluitBeschikbaarheidGateway;
    }

    public function Execute(): iterable
    {
        $user = $this->joomlaGateway->GetUser();
        $team = $this->joomlaGateway->GetTeam($user);
        $coachTeam = $this->joomlaGateway->GetCoachTeam($user);
        $beschikbaarheden = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheden($user);

        $wedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
        $coachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($coachTeam);

        $rooster = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal($this->uscCode, 365);
        foreach ($wedstrijddagen as $wedstrijddag) {
            $dag = new TeamPortalWedstrijddag($wedstrijddag);

            $speelWedstrijd = Wedstrijd::GetWedstrijdWithDate($wedstrijden, $wedstrijddag->date);
            $coachWedstrijd = Wedstrijd::GetWedstrijdWithDate($coachWedstrijden, $wedstrijddag->date);
            $eigenWedstrijden = array_filter([$speelWedstrijd, $coachWedstrijd], function ($value) {
                return $value !== null;
            });
            foreach ($eigenWedstrijden as $wedstrijd) {
                $dag->eigenWedstrijden[] = new TeamPortalWedstrijd($wedstrijd, $team, $coachTeam);
            }

            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                $speeltijd->isBeschikbaar = Beschikbaarheid::IsBeschikbaar($beschikbaarheden, $speeltijd->time);
                $speeltijd->isMogelijk = Beschikbaarheid::isFluitenMogelijk($eigenWedstrijden, $speeltijd->time);
                $dag->AddSpeeltijd($speeltijd, $team, $coachTeam);
            }

            $rooster[] = $dag;
        }

        return $rooster;
    }
}

class TeamPortalWedstrijd
{
    public string $datum;
    public string $tijd;
    public string $team1;
    public bool $isTeam1;
    public bool $isCoachTeam1;
    public string $team2;
    public bool $isTeam2;
    public bool $isCoachTeam2;
    public string $locatie;

    public function __construct($wedstrijd, $team, $coachTeam)
    {
        $this->datum = DateFunctions::GetDutchDate($wedstrijd->timestamp);
        $this->tijd = $wedstrijd->timestamp->format('H:i');
        $this->team1 = $wedstrijd->team1->naam;
        $this->isTeam1 = $wedstrijd->team1->Equals($team);
        $this->isCoachTeam1 = $wedstrijd->team1->Equals($coachTeam);
        $this->team2 = $wedstrijd->team2->naam;
        $this->isTeam2 = $wedstrijd->team2->Equals($team);
        $this->isCoachTeam2 = $wedstrijd->team2->Equals($coachTeam);
        $this->locatie = $wedstrijd->GetShortLocatie();
    }
}

class TeamPortalSpeeltijd
{
    public string $tijd;
    public array $wedstrijden = [];
    public ?bool $isBeschikbaar;
    public ?bool $isMogelijk;
}

class TeamPortalWedstrijddag
{
    public string $date;
    public array $speeltijden = [];
    public array $bardiensten = [];
    public array $eigenWedstrijden = [];
    public string $zaalwacht;

    public function __construct(Wedstrijddag $dag)
    {
        $this->date = DateFunctions::GetYmdNotation($dag->date);
        if ($dag->zaalwacht !== null) {
            $this->zaalwacht = $dag->zaalwacht->naam;
        }
    }

    public function AddSpeeltijd(Speeltijd $speeltijd, ?Team $team, ?Team $coachTeam)
    {
        $tijd = new TeamPortalSpeeltijd();
        $tijd->isBeschikbaar = $speeltijd->isBeschikbaar;
        $tijd->isMogelijk = $speeltijd->isMogelijk;
        $tijd->tijd = DateFunctions::GetTime($speeltijd->time);
        foreach ($speeltijd->wedstrijden as $wedstrijd) {
            $tijd->wedstrijden[] = new TeamPortalWedstrijd($wedstrijd, $team, $coachTeam);
        }
        $this->speeltijden[] = $tijd;
    }
}
