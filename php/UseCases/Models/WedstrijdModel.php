<?php

class WedstrijdModel extends Overzichtsitem
{
    public string $wedstrijdId;
    public string $tijd;
    public string $team1;
    public bool $isTeam1 = false;
    public bool $isCoachTeam1 = false;
    public string $team2;
    public bool $isTeam2 = false;
    public bool $isCoachTeam2 = false;
    public ?string $scheidsrechter;
    public bool $isScheidsrechter = false;
    public ?string $tellers;
    public bool $isTellers = false;
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

    public function SetPersonalInformation(Persoon $user)
    {
        if ($user->team) {
            $this->isTeam1 = $this->team1 ===  $user->team->naam;
            $this->isTeam2 = $this->team2 === $user->team->naam;
            $this->isTellers = $this->tellers === $user->team->naam;
        }

        if ($user->coachteam) {
            $this->isCoachTeam1 = $this->team1 === $user->coachteam->naam;
            $this->isCoachTeam2 = $this->team2 === $user->coachteam->naam;
        }

        $this->isScheidsrechter = $user->naam === $this->scheidsrechter;
    }
}
