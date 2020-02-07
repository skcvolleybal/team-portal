<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Spelsysteem;
use TeamPortal\Entities\Wedstrijdpunt;
use TeamPortal\Gateways\GespeeldeWedstrijdenGateway;
use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\UseCases\Interactor;
use UnexpectedValueException;

class GetDwfStatistieken implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        GespeeldeWedstrijdenGateway $gespeeldeWedstrijdenGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->gespeeldeWedstrijdenGateway = $gespeeldeWedstrijdenGateway;
        $this->posities = ["rechtsachter", "rechtsvoor", "midvoor", "linksvoor", "linksachter", "midachter"];
    }

    function Execute(object $data = null)
    {
        $user = $this->joomlaGateway->GetUser();
        $matchId = $data->matchId;
        if ($user->team == null) {
            return null;
        }

        $this->team = $user->team;
        $user->rugnummer = $this->joomlaGateway->GetRugnummerOfPersoon($user);
        $this->user = $user;

        $spelers = $this->joomlaGateway->GetTeamgenoten($user->team);
        $result = new DwfStatistiekenModel($spelers);
        $spelverdelers = [];
        foreach ($spelers as $speler) {
            if ($speler->IsSpelverdeler()) {
                $spelverdelers[] = $speler->rugnummer;
            }
        }

        $wedstrijden = $this->gespeeldeWedstrijdenGateway->GetGespeeldeWedstrijdenByTeam($user->team);
        foreach ($wedstrijden as $wedstrijd) {
            if ($matchId && $wedstrijd->matchId !== $matchId) {
                continue;
            }

            $punten = $this->gespeeldeWedstrijdenGateway->GetAllePuntenByMatchId($wedstrijd->matchId, $user->team);
            foreach ($punten as $punt) {
                $punt->spelsysteem = $this->GetSpelsysteem($punt, $spelverdelers);
                $punt->rotatie = $this->GetRotatie($punt, $spelverdelers);

                if ($punt->spelsysteem !== null && $punt->rotatie !== null) {
                    $bin = $punt->spelsysteem === Spelsysteem::VIJF_EEN ? $result->spelsystemen[0] : $result->spelsystemen[1];
                    $bin->totaalAantalPunten++;
                    $this->AddPuntToRotatie($punt, $bin->puntenPerRotatie, $spelverdelers);
                    if ($punt->isSkcService) {
                        $this->AddPuntToRotatie($punt, $bin->puntenPerRotatieEigenService, $spelverdelers);
                    } else {
                        $this->AddPuntToRotatie($punt, $bin->puntenPerRotatieServiceontvangst, $spelverdelers);
                    }

                    $rugnummers = $this->GetRugnummers($punt);
                    $this->AddPuntToSpelers($punt, $result->plusminus, $rugnummers);
                    $this->AddPuntToCombinaties($punt, $result->combinaties, $rugnummers);

                    $voorspelers = $this->GetVoorspelers($punt);
                    $this->AddPuntToSpelers($punt, $result->plusminusAlleenVoor, $voorspelers);

                    if ($punt->isSkcService && $punt->rechtsachter !== null) {
                        $this->AddPuntToSpelers($punt, $result->services, [$punt->rechtsachter]);
                    }
                }
            }
        }

        $spelers = $this->gespeeldeWedstrijdenGateway->GetGespeeldePunten($user->team, $matchId);
        foreach ($spelers as $speler) {
            if ($speler->naam) {
                $result->gespeeldePunten[] = (object) [
                    'naam' => $speler->naam,
                    'voornaam' => $speler->GetEersteNaam(),
                    'afkorting' => $speler->GetAfkorting(),
                    "aantalGespeeldePunten" => $speler->aantalGespeeldePunten,
                ];
            }
        }

        $this->CalculateRotatieStatistieken($result);

        $this->CalculateSpelersstatistieken($result->services);
        usort($result->services, [PuntenModel::class, "Compare"]);

        $this->CalculateSpelersstatistieken($result->plusminus);
        usort($result->plusminus, [PuntenModel::class, "Compare"]);

        $this->CalculateSpelersstatistieken($result->plusminusAlleenVoor);
        usort($result->plusminusAlleenVoor, [PuntenModel::class, "Compare"]);

        foreach ($result->combinaties as $combinatie) {
            if (preg_match('/(\d{1,2})-(\d{0,2})/', $combinatie->type, $matches) > 0) {
                $combinatie->speler1 = $this->GetSpelersnaamByRugnummer($matches[1], $spelers);
                $combinatie->speler2 = $this->GetSpelersnaamByRugnummer($matches[2], $spelers);

                if (in_array($user->rugnummer, [$matches[1], $matches[2]])) {
                    $result->eigenCombinaties[] = clone $combinatie;
                }
            }
        }
        $this->CalculateSpelersstatistieken($result->combinaties);
        usort($result->combinaties, [PuntenModel::class, "Compare"]);

        $this->CalculateSpelersstatistieken($result->eigenCombinaties);
        usort($result->eigenCombinaties, [PuntenModel::class, "Compare"]);

        return $result;
    }

    private function GetSpelersnaamByRugnummer(int $rugnummer, array $spelers)
    {
        $key = array_search($rugnummer, array_column($spelers, 'rugnummer'));
        if ($key === false) {
            $speler = $this->joomlaGateway->GetSpelerByRugnummer($rugnummer, $this->team);
        } else {
            $speler = $spelers[$key];
        }

        return $speler != null ? $speler->naam : null;
    }

    private function AddPuntToCombinaties(Wedstrijdpunt $punt, array &$combinaties, array $rugnummers)
    {
        $aantalRugnummers = count($rugnummers);
        for ($i = 0; $i < $aantalRugnummers - 1; $i++) {
            for ($j = $i + 1; $j < $aantalRugnummers; $j++) {
                $combinatie = $rugnummers[$i] < $rugnummers[$j] ? $rugnummers[$i] . "-" . $rugnummers[$j] : $rugnummers[$j] . "-" . $rugnummers[$i];
                $key = array_search($combinatie, array_column($combinaties, 'type'));
                if ($key === false) {
                    $newPuntenModel = new PuntenModel("combinaties");
                    $newPuntenModel->type = $combinatie;
                    $newPuntenModel->AddPunt($punt);
                    $combinaties[] = $newPuntenModel;
                } else {
                    $combinaties[$key]->AddPunt($punt);
                }
            }
        }
    }

    private function CalculateRotatieStatistieken(DwfStatistiekenModel $result)
    {
        foreach ($result->spelsystemen as $spelsysteem) {
            $this->CalculateSpelersstatistieken($spelsysteem->puntenPerRotatie);
            $this->CalculateSpelersstatistieken($spelsysteem->puntenPerRotatieEigenService);
            $this->CalculateSpelersstatistieken($spelsysteem->puntenPerRotatieServiceontvangst);
        }
    }

    private function CalculateSpelersstatistieken(array $spelers)
    {
        foreach ($spelers as $speler) {
            $speler->CalculatePercentages();
            $speler->CalculatePlusminus();
        }
        PuntenModel::Normalize($spelers);
    }

    public function AddPuntToRotatie(Wedstrijdpunt $punt, array $rotaties, array $spelverdelers)
    {
        $i = $this->GetRotatie($punt, $spelverdelers);
        $rotaties[$i]->AddPunt($punt);
    }

    public function AddPuntToSpelers(Wedstrijdpunt $punt, array &$spelers, array $rugnummers)
    {
        foreach ($rugnummers as $rugnummer) {
            $i = array_search($rugnummer, array_column($spelers, 'rugnummer'));
            if ($i !== false) {
                $spelers[$i]->AddPunt($punt);
                continue;
            }
            $newSpeler = $this->joomlaGateway->GetSpelerByRugnummer($rugnummer, $this->team);
            if ($newSpeler === null) {
                continue;
            }

            $puntModel = new PuntenModel("invaller");
            $puntModel->naam = $newSpeler->naam;
            $puntModel->afkorting = $newSpeler->GetAfkorting();
            $puntModel->voornaam = $newSpeler->GetEersteNaam();
            $puntModel->rugnummer = $newSpeler->rugnummer;
            $spelers[] = $puntModel;
        }
    }

    private function GetVoorspelers(Wedstrijdpunt $punt)
    {
        $voorspelers = [$punt->rechtsvoor, $punt->midvoor, $punt->linksvoor];
        return array_filter($voorspelers, function ($speler) {
            return $speler != null;
        });
    }

    public function GetSpelerByRugnummer(array $spelers, int $rugnummer)
    {
        foreach ($spelers as $speler) {
            if ($speler->rugnummer === $rugnummer) {
                return $speler;
            }
        }
        throw new UnexpectedValueException("Speler zit in ");
    }

    public function GetRugnummers(Wedstrijdpunt $punt)
    {
        $result = [];
        foreach ($this->posities as $positie) {
            if ($punt->{$positie}) {
                $result[] = $punt->{$positie};
            }
        }
        return $result;
    }

    private function GetSpelsysteem(Wedstrijdpunt $punt, array $spelverdelerIds)
    {
        $aantalSpelverdelers = 0;
        foreach ($this->posities as $positie) {
            if (in_array($punt->{$positie}, $spelverdelerIds)) {
                $aantalSpelverdelers++;
            }
        }

        switch ($aantalSpelverdelers) {
            case 1:
                return Spelsysteem::VIJF_EEN;
            case 2:
                return Spelsysteem::VIER_TWEE;
            default:
                return null;
        }
    }

    private function GetRotatie(Wedstrijdpunt $punt, array $spelverdelerIds)
    {
        foreach ($this->posities as $i => $positie) {
            if (in_array($punt->{$positie}, $spelverdelerIds)) {
                return $i;
            }
        }

        return null;
    }
}
