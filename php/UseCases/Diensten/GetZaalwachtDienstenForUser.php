<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\ZaalwachtGateway;
use TeamPortal\Gateways\NevoboGateway;

use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\Zaalwacht;


class GetZaalwachtDienstenForUser implements Interactor
{
    public function __construct(
        WordPressGateway $wordPressGateway,
        ZaalwachtGateway $ZaalwachtGateway,
        NevoboGateway $NevoboGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->ZaalwachtGateway = $ZaalwachtGateway;
        $this->NevoboGateway = $NevoboGateway;
    }

    public function Execute(object $data = null) {

        $user = $this->wordPressGateway->GetUser();
        $user->team = $this->wordPressGateway->GetTeam($user);

        $persoon = new Persoon($user->id, $user->naam, $user->email);

        $zaalWachtenOpDag = $this->ZaalwachtGateway->GetZaalwachtenOfUser($persoon);

        return $this->getZaalwachtenOpDagForUser($user, $zaalWachtenOpDag);
    }

    private function getZaalwachtenOpDagForUser(Persoon $user, array $zaalwachten) {
        $nieuweZaalwachtTijden = [];
        $wedstrijden = $this->NevoboGateway->GetWedstrijdenForTeam($user->team);
        foreach ($zaalwachten as $zaalwacht) {
            if ($zaalwacht->eersteZaalwacht->naam == $user->team->naam) {
                $nieuweZaalwachtTijden[] = $this->getZaalwachtTime($wedstrijden, $zaalwacht);
            } elseif ($zaalwacht->tweedeZaalwacht->naam == $user->team->naam) {
                $nieuweZaalwachtTijden[] = $this->getZaalwachtTime($wedstrijden, $zaalwacht);
            }
        }

        return $nieuweZaalwachtTijden;
    }

    private function getZaalwachtTime(array $wedstrijden, Zaalwacht $zaalwacht) {
        $teamNaamOffset = 9; # There is a team called Aspasia - Timios, de '-' fucks up our aligment so if a team plays them the name will be SKC .. - Aspasia so yh fuck them. This will break once we get more than 99 gents or womens teams
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp->format('Y-m-d') == $zaalwacht->date->format('Y-m-d')) {
                # If de dag van je wedstrijd en de dag dat je zaalwacht hebt is tzelfde dan
                # moet je dus zaalwacht doen
                if (trim(substr($zaalwacht->eersteZaalwacht->naam, $teamNaamOffset)) == trim(substr($wedstrijd->team1->naam, $teamNaamOffset))) {
                    # Team heeft 1e zaalwacht
                    return $wedstrijd->timestamp->modify('-1 hour');
                }
                elseif (trim(substr($zaalwacht->tweedeZaalwacht->naam, 0, $teamNaamOffset)) == trim(substr($wedstrijd->team1->naam, 0, $teamNaamOffset)))
                    # Team heeft 2e zaalwacht 
                    return $wedstrijd->timestamp->modify('+1 hour 30 minutes');
            }
        }
    }
}