<?php

class Team
{
    public ?int $id;
    public string $naam; // Bv. SKC HS 2
    public string $poule;
    public array $teamgenoten = [];
    public int $niveau;
    public static $alleNiveaus;
    public int $aantalKeerGeteld;

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
        foreach (Team::$alleNiveaus as $team => $niveau) {
            if ($team === $this->naam) {
                $this->niveau = $niveau;
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

    public static function GetAlleHerenTeams()
    {
        return Team::GetAllTeamsByGender("H");
    }

    public static function GetAlleDamesTeams()
    {
        return Team::GetAllTeamsByGender("D");
    }

    private static function GetAllTeamsByGender($genderletter)
    {
        $result = [];
        foreach (Team::$alleNiveaus as $teamnaam => $niveau) {
            if ($teamnaam[4] === $genderletter) {
                $result[] = new Team($teamnaam);
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
}

Team::$alleNiveaus = (object) [
    "SKC DS 1" => Niveau::PROMOTIEKLASSE,
    "SKC DS 2" => Niveau::EERSTE_KLASSE,
    "SKC DS 3" => Niveau::TWEEDE_KLASSE,
    "SKC DS 4" => Niveau::TWEEDE_KLASSE,
    "SKC DS 5" => Niveau::DERDE_KLASSE,
    "SKC DS 6" => Niveau::DERDE_KLASSE,
    "SKC DS 7" => Niveau::DERDE_KLASSE,
    "SKC DS 8" => Niveau::DERDE_KLASSE,
    "SKC DS 9" => Niveau::DERDE_KLASSE,
    "SKC DS 10" => Niveau::VIERDE_KLASSE,
    "SKC DS 11" => Niveau::VIERDE_KLASSE,
    "SKC DS 12" => Niveau::VIERDE_KLASSE,
    "SKC DS 13" => Niveau::VIERDE_KLASSE,
    "SKC DS 14" => Niveau::VIERDE_KLASSE,
    "SKC DS 15" => Niveau::VIERDE_KLASSE,

    "SKC HS 1" => Niveau::EERSTE_KLASSE,
    "SKC HS 2" => Niveau::EERSTE_KLASSE,
    "SKC HS 3" => Niveau::TWEEDE_KLASSE,
    "SKC HS 4" => Niveau::DERDE_KLASSE,
    "SKC HS 5" => Niveau::DERDE_KLASSE,
    "SKC HS 6" => Niveau::VIERDE_KLASSE,
    "SKC HS 7" => Niveau::VIERDE_KLASSE,
    "SKC HS 8" => Niveau::VIERDE_KLASSE,
    "SKC HS 9" => Niveau::VIERDE_KLASSE
];
