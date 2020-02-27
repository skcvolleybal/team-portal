<?php

namespace TeamPortal\UseCases;

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
        $matchId = $data->matchId ?? "";

        $user = $this->joomlaGateway->GetUser();
        if ($user->team == null) {
            return null;
        }

        $this->team = $user->team;

        $spelers = $this->joomlaGateway->GetTeamgenoten($user->team);
        $result = new DwfStatistiekenModel($spelers);
        $spelverdelers = $this->GetSpelverdelerIds($spelers);
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
                $spelerIds = $punt->GetSpelerIds();
                $this->AddMissingSpelers($spelers, $spelerIds);
                $result->AddPunt($punt, $spelverdelers, $spelerIds);
            }
        }

        $result->gespeeldePunten = $this->gespeeldeWedstrijdenGateway->GetGespeeldePunten($user->team, $matchId);
        $result->servicereeksen = $this->gespeeldeWedstrijdenGateway->GetLangsteServicereeksen();

        $result->CalculateRotatieStatistieken();
        $result->CalculateSpelersstatistieken();

        foreach ($result->combinaties as $combinatie) {
            if (preg_match('/(\d{3,4})-(\d{3,4})/', $combinatie->type, $matches) > 0) {
                $combinatie->speler1 = $this->GetSpelerById($spelers, $matches[1]);
                $combinatie->speler2 = $this->GetSpelerById($spelers, $matches[2]);

                if (in_array($user->id, [$matches[1], $matches[2]])) {
                    $result->eigenCombinaties[] = clone $combinatie;
                }
            }
        }

        return $result;
    }

    private function AddMissingSpelers(array &$spelers, array $spelerIds)
    {
        foreach ($spelerIds as $spelerId) {
            $i = array_search($spelerId, array_column($spelers, 'id'));
            if ($i !== false) {
                continue;
            }
            $newSpeler = $this->joomlaGateway->GetUser($spelerId);
            if ($newSpeler === null) {
                continue;
            }

            $spelers[] = $newSpeler;
        }
    }

    private function GetSpelverdelerIds(array $spelers): array
    {
        $spelverdelerIds = [];
        foreach ($spelers as $speler) {
            if ($speler->IsSpelverdeler()) {
                $spelverdelerIds[] = $speler->id;
            }
        }
        return $spelverdelerIds;
    }

    public function GetSpelerById(array $spelers, int $userId)
    {
        foreach ($spelers as $speler) {
            if ($speler->id === $userId) {
                return $speler;
            }
        }
        throw new UnexpectedValueException("Speler zit er niet bij");
    }
}
