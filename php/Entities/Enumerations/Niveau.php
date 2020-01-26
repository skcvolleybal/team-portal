<?php

namespace TeamPortal\Entities;

class Niveau
{
    const EREDIVISIE = 0;
    const TOPDIVISIE = 1;
    const EERSTE_DIVISIE = 2;
    const TWEEDE_DIVISIE = 3;
    const DERDE_DIVISIE = 4;
    const PROMOTIEKLASSE = 5;
    const EERSTE_KLASSE = 6;
    const TWEEDE_KLASSE = 7;
    const DERDE_KLASSE = 8;
    const VIERDE_KLASSE = 9;

    static function GetNiveauByString(string $niveau)
    {
        $niveau = strtolower($niveau);
        switch ($niveau) {
            case "eredivisie";
                $niveau =  NIVEAU::EREDIVISIE;
                break;
            case "topdivisie";
                $niveau =  NIVEAU::TOPDIVISIE;
                break;
            case "1e divisie";
                $niveau =  NIVEAU::EERSTE_DIVISIE;
                break;
            case "2e divisie";
                $niveau =  NIVEAU::TWEEDE_DIVISIE;
                break;
            case "3e divisie";
                $niveau =  NIVEAU::DERDE_DIVISIE;
                break;
            case "promotieklasse";
                $niveau =  NIVEAU::PROMOTIEKLASSE;
                break;
            case "1e klasse";
                $niveau = NIVEAU::EERSTE_KLASSE;
                break;
            case "2e klasse";
                $niveau = NIVEAU::TWEEDE_KLASSE;
                break;
            case "3e klasse";
                $niveau = NIVEAU::DERDE_KLASSE;
                break;
            case "4e klasse";
                $niveau = NIVEAU::VIERDE_KLASSE;
                break;
            default:
                throw new \UnexpectedValueException("Niveau bestaat niet: $niveau");
        }

        return $niveau;
    }

    public static function GetNiveauString(int $niveau)
    {
        switch ($niveau) {
            case 0;
                $niveauString =  "Eredivisie";
                break;
            case 1;
                $niveauString =  "Topdivisie";
                break;
            case 2;
                $niveauString =  '1e divisie';
                break;
            case 3;
                $niveauString =  "2e divisie";
                break;
            case 4;
                $niveauString =  "3e divisie";
                break;
            case 5;
                $niveauString =  "Promotieklasse";
                break;
            case 6;
                $niveauString = "1e klasse";
                break;
            case 7;
                $niveauString = "2e klasse";
                break;
            case 8;
                $niveauString = "3e klasse";
                break;
            case 9;
                $niveauString = "4e klasse";
                break;
            default:
                throw new \UnexpectedValueException("Niveau bestaat niet: $niveau");
        }
        return $niveauString;
    }
}
