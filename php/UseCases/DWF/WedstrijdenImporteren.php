<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;
use TeamPortal\Entities\DwfPunt;
use TeamPortal\Entities\DwfWedstrijd;
use TeamPortal\Entities\DwfWissel;
use TeamPortal\Entities\ThuisUit;

class WedstrijdenImporteren implements Interactor
{
    public function __construct(
        Gateways\DwfGateway $dwfGateway,
        Gateways\GespeeldeWedstrijdenGateway $gespeeldeWedstrijdenGateway
    ) {
        $this->dwfGateway = $dwfGateway;
        $this->gespeeldeWedstrijdenGateway = $gespeeldeWedstrijdenGateway;
    }

    public function Execute(object $data = null)
    {
        $this->gespeeldeWedstrijden = $this->dwfGateway->GetGespeeldeWedstrijden();
        $this->opgeslagenWedstrijden = $this->gespeeldeWedstrijdenGateway->GetGespeeldeWedstrijden();

        foreach ($this->gespeeldeWedstrijden as $wedstrijd) {
            if ($this->IsWedstrijdAlOpgeslagen($wedstrijd)) {
                continue;
            }

            $locations = [];
            if ($wedstrijd->team1->IsSkcTeam()) {
                $locations[] = ThuisUit::THUIS;
            }
            if ($wedstrijd->team2->IsSkcTeam()) {
                $locations[] = ThuisUit::UIT;
            }

            foreach ($locations as $location) {
                if ($location == ThuisUit::THUIS) {
                    $skcTeam = $wedstrijd->team1;
                    $otherTeam = $wedstrijd->team2;
                    $setsSkcTeam = $wedstrijd->setsTeam1;
                    $setsOtherTeam = $wedstrijd->setsTeam2;
                } else {
                    $skcTeam = $wedstrijd->team2;
                    $otherTeam = $wedstrijd->team1;
                    $setsSkcTeam = $wedstrijd->setsTeam2;
                    $setsOtherTeam = $wedstrijd->setsTeam1;
                }

                $newWedstrijd = new DwfWedstrijd(
                    $wedstrijd->matchId,
                    $skcTeam,
                    $otherTeam,
                    $setsSkcTeam,
                    $setsOtherTeam
                );

                $this->gespeeldeWedstrijdenGateway->AddWedstrijd($newWedstrijd);
                $this->opgeslagenWedstrijden[] = $newWedstrijd;
                
                echo $skcTeam->naam . " - " . $otherTeam->naam . "<br>";
                ob_flush();
                flush();

                $wedstrijdverloop = $this->dwfGateway->GetWedstrijdVerloop($newWedstrijd);
                if ($wedstrijdverloop === null) {
                    continue;
                }

                foreach ($wedstrijdverloop->sets as $currentSet => $set) {
                    $opstelling = $set->{$location . "opstelling"};

                    foreach ($set->punten as $punt) {
                        switch (true) {
                            case $punt instanceof DwfPunt:
                                $this->gespeeldeWedstrijdenGateway->AddPunt(
                                    $wedstrijd->matchId,
                                    $location,
                                    $skcTeam,
                                    $currentSet + 1,
                                    $punt,
                                    $opstelling
                                );
                                if ($punt->serverendTeam != $punt->scorendTeam && $punt->scorendTeam == $location) {
                                    $opstelling = $this->Doordraaien($opstelling);
                                }
                                break;
                            case $punt instanceof DwfWissel:
                                if ($punt->team == $location) {
                                    try {
                                        $opstelling = $this->Wisselen($opstelling, $punt->spelerUit, $punt->spelerIn);
                                    } catch (\UnexpectedValueException $exception) {
                                        // This exception is only thrown with very rare cases where
                                        // a 'Uitzonderlijke spelerswissel' occurs. Only 1 match so far
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }

        echo "Done!";
    }

    private function IsWedstrijdAlOpgeslagen(DwfWedstrijd $wedstrijd): bool
    {
        foreach ($this->opgeslagenWedstrijden as $gespeeldeWedstrijd) {
            if ($wedstrijd->matchId == $gespeeldeWedstrijd->matchId) {
                return true;
            }
        }
        return false;
    }

    private function Wisselen(array $opstelling, int $uit, int $in): array
    {
        foreach ($opstelling as $i => $speler) {
            if ($speler == $uit) {
                $opstelling[$i] = $in;
                return $opstelling;
            }
        }

        if (in_array(null, $opstelling)) {
            return $opstelling;
        }

        throw new \UnexpectedValueException("Speler niet gevonden");
    }

    private function Doordraaien(array $opstelling): array
    {
        $tmp = $opstelling[0];
        $opstelling[0] = $opstelling[1];
        $opstelling[1] = $opstelling[2];
        $opstelling[2] = $opstelling[3];
        $opstelling[3] = $opstelling[4];
        $opstelling[4] = $opstelling[5];
        $opstelling[5] = $tmp;
        return $opstelling;
    }
}
