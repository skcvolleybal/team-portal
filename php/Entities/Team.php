<?php

class Team
{
    public ?int $id;
    public string $naam; // Bv. SKC HS 2
    public string $poule;
    public array $teamgenoten = [];
    public int $niveau;
    public static $alleSkcTeams = [];
    public ?string $facebook;

    public function __construct($naam, $id = null, $teamgenoten = [])
    {
        $this->id = $id;

        if ($this->isSkcFormat($naam)) {
            $sequence = substr($naam, 6);
            $this->naam = substr($naam, 0, 6) === 'Dames ' ? 'SKC DS ' . $sequence : 'SKC HS ' . $sequence;
        } else if ($this->isNevoboFormat($naam)) {
            $this->naam = strtoupper($naam);
        } else {
            $this->naam = $naam;
        }

        $this->teamgenoten = $teamgenoten;

        foreach (Team::$alleSkcTeams as $team) {
            if ($team->naam === $this->naam) {
                $this->niveau = $team->niveau;
                break;
            }
        }
    }

    function GetShortNotation()
    {
        return $this->naam[4] . substr($this->naam, 7);
    }

    function GetSkcNaam()
    {
        return ($this->naam[4] === 'D' ? 'Dames ' : 'Heren ') . substr($this->naam, 7);
    }

    private function isNevoboFormat($naam)
    {
        return preg_match('/^SKC [D|H]S \d+$/i', $naam);
    }

    private function isSkcFormat($naam)
    {
        return preg_match('/^(Heren|Dames) \d+$/i', $naam);
    }

    static function CreateTeamWithPoule($naam, $poule): Team
    {
        $newTeam = new Team($naam);
        $newTeam->poule = $poule;
        return $newTeam;
    }

    function IsMale(): bool
    {
        return substr($this->naam, 4, 1) === "H";
    }

    function IsSkcTeam()
    {
        return "SKC " === substr($this->naam, 0, 4);
    }

    public static function GetAlleHerenTeams()
    {
        return Team::GetAllTeamsByGender("H");
    }

    public static function GetAlleDamesTeams()
    {
        return Team::GetAllTeamsByGender("D");
    }

    private static function GetAllTeamsByGender($genderCharacter)
    {
        $result = [];
        foreach (Team::$alleSkcTeams as $team) {
            if ($team->naam[4] === $genderCharacter) {
                $result[] = $team;
            }
        }
        return $result;
    }

    public function Equals(?Team $team)
    {
        if ($team === null) {
            return false;
        }
        return $this->naam === $team->naam;
    }

    function GetWedstrijdOfTeam(array $wedstrijden): ?Wedstrijd
    {
        foreach ($wedstrijden as $wedstrijd) {
            $teamnaam = $this->naam;
            if ($wedstrijd->team1->naam == $teamnaam || $wedstrijd->team2->naam == $teamnaam) {
                return $wedstrijd;
            }
        }

        return null;
    }

    static function LoadAlleSkcTeams(): void
    {
        $string = file_get_contents("skc-teams.json");
        $teams = json_decode($string);
        foreach ($teams as $team) {
            $skcTeam = new Team($team->naam);
            $skcTeam->poule = $team->poule;
            $skcTeam->trainingstijden = $team->trainingstijden;
            $skcTeam->facebook = $team->facebook ?? null;
            $skcTeam->niveau =  Niveau::GetNiveauByString($team->niveau);
            Team::$alleSkcTeams[] = $skcTeam;
        }
    }
}

Team::LoadAlleSkcTeams();
