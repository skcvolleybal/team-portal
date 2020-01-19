<?php

class WedstrijdenImporteren implements Interactor
{
    public function __construct($database)
    {
        $configuration = include('./../configuration.php');

        $this->dwfGateway = new DwfGateway($configuration->dwfUsername, $configuration->dwfPassword);
        $this->gespeeldeWedstrijdenGateway = new GespeeldeWedstrijdenGateway($database);
    }

    public function Execute(object $data = null)
    {
        $this->gespeeldeWedstrijden = $this->dwfGateway->GetGespeeldeWedstrijden();
        $this->opgeslagenWedstrijden = $this->gespeeldeWedstrijdenGateway->GetGespeeldeWedstrijden();

        foreach ($this->gespeeldeWedstrijden as $wedstrijd) {
            if ($this->IsWedstrijdAlOpgeslagen($wedstrijd)) {
                continue;
            }

            $wedstrijdverloop = $this->dwfGateway->GetWedstrijdVerloop($wedstrijd->matchId);
            if ($wedstrijdverloop === null) {
                continue;
            }

            $teams = [];
            if (strpos($wedstrijd->team1, "SKC ", 0) === 0) {
                $teams[] = "thuis";
            }
            if (strpos($wedstrijd->team2, "SKC ", 0) === 0) {
                $teams[] = "uit";
            }
            foreach ($teams as $team) {
                foreach ($wedstrijdverloop->sets as $currentSet => $set) {
                    $opstelling = $set->beginopstellingen->{$team};
                    if ($team == "thuis") {
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

                    foreach ($set->punten as $punt) {
                        switch ($punt->type) {
                            case "punt":
                                if ($team == "thuis") {
                                    $skcPunten = $punt->puntenThuisTeam;
                                    $tegenstandPunten  = $punt->puntenUitTeam;
                                } else {
                                    $skcPunten = $punt->puntenUitTeam;
                                    $tegenstandPunten  = $punt->puntenThuisTeam;
                                }

                                $this->gespeeldeWedstrijdenGateway->AddPunt(
                                    $wedstrijd->matchId,
                                    $skcTeam,
                                    $currentSet + 1,
                                    $punt->serverendTeam == $team,
                                    $punt->scorendTeam == $team,
                                    $skcPunten,
                                    $tegenstandPunten,
                                    $opstelling
                                );
                                if ($punt->serverendTeam != $punt->scorendTeam && $punt->scorendTeam == $team) {
                                    $opstelling = $this->Doordraaien($opstelling);
                                }
                                break;
                            case "wissel":
                                if ($punt->team == $team) {
                                    $opstelling = $this->Wisselen($opstelling, $punt->spelerUit, $punt->spelerIn);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }

                $newWedstrijd = (object) [
                    "matchId" => $wedstrijd->matchId,
                    "skcTeam" => $skcTeam,
                    "otherTeam" => $otherTeam,
                    "setsSkcTeam" => $setsSkcTeam,
                    "setsOtherTeam" => $setsOtherTeam,
                ];

                $this->gespeeldeWedstrijdenGateway->AddWedstrijd($newWedstrijd);
            }
        }

        return "Done!";
    }

    private function IsWedstrijdAlOpgeslagen($wedstrijd)
    {
        foreach ($this->opgeslagenWedstrijden as $gespeeldeWedstrijd) {
            if ($wedstrijd->matchId == $gespeeldeWedstrijd->matchId) {
                return true;
            }
        }
        return false;
    }

    private function Wisselen($opstelling, $uit, $in)
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

        throw new Exception("Speler niet gevonden");
    }

    private function Doordraaien($opstelling)
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
