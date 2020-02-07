<?php

namespace TeamPortal\UseCases;

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
    }

    function Execute(object $data = null)
    {
        $matchId = $data->matchId;

        $user = $this->joomlaGateway->GetUser();
        if ($user->team == null) {
            return null;
        }

        $this->team = $user->team;

        $spelers = $this->joomlaGateway->GetTeamgenoten($user->team);
        $result = new DwfStatistiekenModel($spelers);
        $spelverdelers = $this->GetSpelverdelers($spelers);
        if (count($spelverdelers) === 0) {
            throw new UnexpectedValueException("Het aantal spelverdelers in jouw team is 0. Dit kan je in het profiel (van de spelverdeler) aanpassen.");
        }

        $wedstrijden = $this->gespeeldeWedstrijdenGateway->GetGespeeldeWedstrijdenByTeam($user->team);
        foreach ($wedstrijden as $wedstrijd) {
            if (!empty($matchId) && $wedstrijd->matchId !== $matchId) {
                continue;
            }

            $punten = $this->gespeeldeWedstrijdenGateway->GetAllePuntenByMatchId($wedstrijd->matchId, $user->team);
            foreach ($punten as $punt) {
                $rugnummers = $punt->GetRugnummers($punt);
                $this->AddMissingSpelers($spelers, $rugnummers);
                $result->AddPunt($punt, $spelverdelers, $rugnummers);
            }
        }

        $result->gespeeldePunten = $this->gespeeldeWedstrijdenGateway->GetGespeeldePunten($user->team, $matchId);

        $result->CalculateRotatieStatistieken();
        $result->CalculateSpelersstatistieken();

        foreach ($result->combinaties as $combinatie) {
            if (preg_match('/(\d{1,2})-(\d{0,2})/', $combinatie->type, $matches) > 0) {
                $combinatie->speler1 = $this->GetSpelersnaamByRugnummer($matches[1], $spelers);
                $combinatie->speler2 = $this->GetSpelersnaamByRugnummer($matches[2], $spelers);

                if (in_array($user->rugnummer, [$matches[1], $matches[2]])) {
                    $result->eigenCombinaties[] = clone $combinatie;
                }
            }
        }

        return $result;
    }

    private function AddMissingSpelers(array &$spelers, array $rugnummers)
    {
        foreach ($rugnummers as $rugnummer) {
            $i = array_search($rugnummer, array_column($spelers, 'rugnummer'));
            if ($i !== false) {
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



    private function GetSpelverdelers(array $spelers): array
    {
        $spelverdelers = [];
        foreach ($spelers as $speler) {
            if ($speler->IsSpelverdeler()) {
                $spelverdelers[] = $speler->rugnummer;
            }
        }
        return $spelverdelers;
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

    public function GetSpelerByRugnummer(array $spelers, int $rugnummer)
    {
        foreach ($spelers as $speler) {
            if ($speler->rugnummer === $rugnummer) {
                return $speler;
            }
        }
        throw new UnexpectedValueException("Speler zit er niet bij");
    }
}
