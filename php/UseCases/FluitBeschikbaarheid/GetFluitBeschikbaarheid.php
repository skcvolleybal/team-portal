<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;
use TeamPortal\Entities;

class GetFluitBeschikbaarheid implements Interactor
{
    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\FluitBeschikbaarheidGateway $fluitBeschikbaarheidGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->fluitBeschikbaarheidGateway = $fluitBeschikbaarheidGateway;
    }

    public function Execute(object $data = null): array
    {
        $user = $this->joomlaGateway->GetUser();        
        $beschikbaarheden = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheden($user);

        $wedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($user->team);
        $coachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($user->coachteam);

        $rooster = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 365);
        foreach ($wedstrijddagen as $wedstrijddag) {
            $speelWedstrijd = Entities\Wedstrijd::GetWedstrijdWithDate($wedstrijden, $wedstrijddag->date);
            $coachWedstrijd = Entities\Wedstrijd::GetWedstrijdWithDate($coachWedstrijden, $wedstrijddag->date);
            $eigenWedstrijden = array_filter([$speelWedstrijd, $coachWedstrijd], function ($value) {
                return $value !== null;
            });

            $wedstrijddag->eigenWedstrijden = $eigenWedstrijden;

            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                $speeltijd->isBeschikbaar = Entities\Beschikbaarheid::IsBeschikbaar($beschikbaarheden, $speeltijd->time);
                $speeltijd->isMogelijk = Entities\Fluitbeschikbaarheid::isFluitenMogelijk($eigenWedstrijden, $speeltijd->time);
            }

            $dag = new WedstrijddagModel($wedstrijddag);
            foreach ($dag->eigenWedstrijden as $wedstrijd) {
                $wedstrijd->SetPersonalInformation($user);
            }
            foreach ($dag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    $wedstrijd->SetPersonalInformation($user);
                }
            }
            $rooster[] = $dag;
        }

        return $rooster;
    }
}
