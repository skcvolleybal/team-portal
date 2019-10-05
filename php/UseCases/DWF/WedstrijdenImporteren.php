<?php
include_once 'IInteractor.php';
include_once 'DwfGateway.php';
include_once 'GespeeldeWedstrijdenGateway.php';

class WedstrijdenImporteren implements IInteractor
{
    public function __construct($database)
    {
        $configuration = include('./../configuration.php');

        $this->dwfGateway = new DwfGateway($configuration->dwfUsername, $configuration->dwfPassword);
        $this->gespeeldeWedstrijdenGateway = new GespeeldeWedstrijdenGateway($database);
    }

    public function Execute()
    {
        $this->gespeeldeWedstrijden = $this->dwfGateway->GetGespeeldeWedstrijden();
        $this->opgeslagenWedstrijden = $this->gespeeldeWedstrijdenGateway->GetGespeeldeWedstrijden();

        foreach ($this->gespeeldeWedstrijden as $wedstrijd) {
            if ($this->IsWedstrijdAlOpgeslagen($wedstrijd)) {
                continue;
            }

            $wedstrijdVerloop = $this->dwfGateway->GetWedstrijdVerloop($wedstrijd->id);
            if ($wedstrijdVerloop != null) {
                foreach (["team1", "team2"] as $team) {
                    if (strpos($wedstrijd->{$team}, "SKC ") !== 0) {
                        continue;
                    }

                    $isThuisWedstrijd = $team === "team1";

                    foreach ($wedstrijdVerloop as $setIndex => $set) {
                        $opstelling = $isThuisWedstrijd ? $set->thuis : $set->uit;
                        $punten = $wedstrijdVerloop[$setIndex]->punten;
                        $thuisServeert = $wedstrijdVerloop[$setIndex]->beginService == "thuis";
                        foreach ($punten as $punt) {
                            switch ($punt->type) {
                                case "punt":
                                    $this->gespeeldeWedstrijdenGateway->AddPunt(
                                        $wedstrijd,
                                        $setIndex + 1,
                                        $opstelling,
                                        $thuisServeert,
                                        $punt->isThuispunt,
                                        $punt->puntenTeam1,
                                        $punt->puntenTeam2
                                    );
                                    if ($punt->isThuispunt == $isThuisWedstrijd && $thuisServeert == $isThuisWedstrijd) {
                                        $opstelling = $this->Doordraaien($opstelling);
                                    }
                                    $thuisServeert = $punt->isThuispunt;
                                    break;
                                case "wissel":
                                    if ($punt->isThuisWissel == $isThuisWedstrijd) {
                                        $opstelling = $this->Wisselen($opstelling, $punt);
                                    }
                                    break;
                                default:
                                    echo "onbekend type " . $punt->type;
                                    break;
                            }
                        }
                    }
                }

                $this->gespeeldeWedstrijdenGateway->AddWedstrijd($wedstrijd);
            }
        }

        exit("Done!");
    }

    private function IsWedstrijdAlOpgeslagen($wedstrijd)
    {
        foreach ($this->opgeslagenWedstrijden as $gespeeldeWedstrijd) {
            if ($wedstrijd->id == $gespeeldeWedstrijd->id) {
                return true;
            }
        }
        return false;
    }

    private function Wisselen($opstelling, $wissel)
    {
        foreach ($opstelling as $i => $positie) {
            if ($wissel->uit == $positie) {
                $opstelling[$i] = $wissel->in;
                return $opstelling;
            }
        }

        if (in_array(null, $opstelling)) {
            return $opstelling;
        }

        return $opstelling; //throw new Exception("Speler niet gevonden");
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
