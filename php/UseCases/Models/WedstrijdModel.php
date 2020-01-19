<?php

class WedstrijdModel extends Overzichtsitem
{
    public string $wedstrijdId;
    public string $tijd;
    public string $team1;
    public bool $isTeam1;
    public bool $isCoachTeam1;
    public string $team2;
    public bool $isTeam2;
    public bool $isCoachTeam2;
    public ?string $scheidsrechter;
    public bool $isScheidsrechter;
    public ?string $tellers;
    public bool $isTellers;
    public string $locatie;

    public function __construct(Wedstrijd $wedstrijd)
    {
        $this->matchId = $wedstrijd->matchId;
        $this->tijd = DateFunctions::GetTime($wedstrijd->timestamp);
        $this->team1 = $wedstrijd->team1->naam;
        $this->team2 = $wedstrijd->team2->naam;
        $this->teams = $wedstrijd->team1->naam . " - " . $wedstrijd->team2->naam;
        $this->scheidsrechter = $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->naam : null;
        $this->scheidsrechterId = $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->id : null;
        $this->tellers = $wedstrijd->telteam !== null ? $wedstrijd->telteam->GetShortNotation() : null;
        $this->locatie = $wedstrijd->locatie;

        parent::__construct("wedstrijd", $wedstrijd->timestamp);
    }

    public function SetPersonalInformation(Wedstrijd $wedstrijd, Persoon $user, Team $team, Team $coachteam)
    {
        $this->isTeam1 = $wedstrijd->team1->Equals($team);
        $this->isCoachTeam1 = $wedstrijd->team1->Equals($coachteam);
        $this->isTeam2 = $wedstrijd->team2->Equals($team);
        $this->isCoachTeam2 = $wedstrijd->team2->Equals($coachteam);
        $this->isScheidsrechter = $user->Equals($wedstrijd->scheidsrechter);
        $this->isTellers = $team->Equals($wedstrijd->telteam);
    }
}
