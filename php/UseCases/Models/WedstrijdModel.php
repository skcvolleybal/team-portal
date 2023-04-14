<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Wedstrijd;

class WedstrijdModel extends Overzichtsitem
{
    public string $wedstrijdId;
    public string $tijd;
    public string $datum_long;
    public string $team1;
    public bool $isTeam1 = false;
    public bool $isCoachTeam1 = false;
    public string $team2;
    public bool $isTeam2 = false;
    public bool $isCoachTeam2 = false;
    public ?string $scheidsrechter;
    public bool $isScheidsrechter = false;
    public array $tellers = [null, null];
    public bool $isTellers = false;
    public ?string $locatie;
    public array $coaches = [];

    public function __construct(Wedstrijd $wedstrijd)
    {
        $this->matchId = $wedstrijd->matchId;
        $this->tijd = $wedstrijd->timestamp !== null ? DateFunctions::GetTime($wedstrijd->timestamp) : "NA";
        $this->datum_long = $wedstrijd->timestamp !== null ? DateFunctions::GetDutchDateLong($wedstrijd->timestamp) : "NA";
        $this->team1 = $wedstrijd->team1->naam;
        $this->team2 = $wedstrijd->team2->naam;
        $this->teams = $wedstrijd->team1->naam . " - " . $wedstrijd->team2->naam;
        $this->scheidsrechter = $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->naam : null;
        $this->scheidsrechterId = $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->id : null;
        $this->tellers = [
            $wedstrijd->tellers[0] !== null ? $wedstrijd->tellers[0]->naam : null,
            $wedstrijd->tellers[1] !== null ? $wedstrijd->tellers[1]->naam : null
        ];
        $this->locatie = $wedstrijd->locatie;
        $this->uitslag = $wedstrijd->uitslag;
        $this->setstanden = $wedstrijd->setstanden;

        parent::__construct("wedstrijd", $wedstrijd->timestamp);
    }

    public function SetPersonalInformation(Persoon $user)
    {
        if ($user->team) {
            $this->isTeam1 = $this->team1 ===  $user->team->naam;
            $this->isTeam2 = $this->team2 === $user->team->naam;
            $this->isTellers = $this->tellers === $user->team->GetShortNotation();
        }

        $this->isCoachTeam1 = false;
        $this->isCoachTeam2 = false;
        foreach ($user->coachteams as $team) {
            if ($this->team1 === $team->naam) $this->isCoachTeam1 = true;
            if ($this->team2 === $team->naam) $this->isCoachTeam2 = true;
        }

        $this->isScheidsrechter = $user->naam === $this->scheidsrechter;
    }
}
