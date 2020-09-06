<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;
use TeamPortal\Entities\Beschikbaarheid;
use TeamPortal\Entities\Wedstrijd;

class GetBeschikbaarheid implements Interactor
{
    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\BeschikbaarheidGateway $beschikbaarheidGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->beschikbaarheidGateway = $beschikbaarheidGateway;
    }

    public function Execute(object $data = null): array
    {
        $user = $this->joomlaGateway->GetUser();
        $beschikbaarheden = $this->beschikbaarheidGateway->GetBeschikbaarheden($user);

        $wedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($user->team);
        $coachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($user->coachteam);

        $rooster = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 365);
        foreach ($wedstrijddagen as $wedstrijddag) {
            $speelWedstrijd = Wedstrijd::GetWedstrijdWithDate($wedstrijden, $wedstrijddag->date);
            $coachWedstrijd = Wedstrijd::GetWedstrijdWithDate($coachWedstrijden, $wedstrijddag->date);
            $eigenWedstrijden = array_filter([$speelWedstrijd, $coachWedstrijd], function ($value) {
                return $value !== null;
            });

            $wedstrijddag->eigenWedstrijden = $eigenWedstrijden;

            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                $speeltijd->isBeschikbaar = Beschikbaarheid::IsBeschikbaar($beschikbaarheden, $speeltijd->time);
                $speeltijd->isMogelijk = Beschikbaarheid::isFluitenMogelijk($eigenWedstrijden, $speeltijd->time);
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
