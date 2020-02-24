<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\DwfPunt;
use TeamPortal\Entities\DwfWedstrijd;
use TeamPortal\Entities\DwfWissel;
use TeamPortal\Entities\ThuisUit;
use TeamPortal\Gateways\DwfGateway;
use TeamPortal\Gateways\GespeeldeWedstrijdenGateway;
use TeamPortal\Gateways\JoomlaGateway;
use UnexpectedValueException;

class WedstrijdenImporteren implements Interactor
{
    public function __construct(
        DwfGateway $dwfGateway,
        GespeeldeWedstrijdenGateway $gespeeldeWedstrijdenGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->dwfGateway = $dwfGateway;
        $this->gespeeldeWedstrijdenGateway = $gespeeldeWedstrijdenGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $this->gespeeldeWedstrijden = $this->dwfGateway->GetGespeeldeWedstrijden();
        $this->opgeslagenWedstrijden = $this->gespeeldeWedstrijdenGateway->GetGespeeldeWedstrijden();

        foreach ($this->gespeeldeWedstrijden as $wedstrijd) {
            if ($this->IsWedstrijdAlOpgeslagen($wedstrijd)) {
                continue;
            }

            $this->dwfGateway->AppendWedstrijdVerloop($wedstrijd);

            if ($wedstrijd->team1->IsSkcTeam()) {
                $this->SaveWedstrijd($wedstrijd);
            }
            if ($wedstrijd->team2->IsSkcTeam()) {
                $wedstrijd->WisselTeams();
                $this->SaveWedstrijd($wedstrijd);
            }

            echo $wedstrijd->team1->naam . " - " . $wedstrijd->team2->naam . "<br>";
            ob_flush();
            flush();

            $this->opgeslagenWedstrijden[] = $wedstrijd;
        }

        echo "Done!";
    }

    private function SaveWedstrijd(DwfWedstrijd $wedstrijd)
    {
        $this->gespeeldeWedstrijdenGateway->AddWedstrijd($wedstrijd);

        if (count($wedstrijd->sets) === 0) {
            return;
        }

        foreach ($wedstrijd->sets as $currentSet => $set) {
            $opstelling = $set->thuisopstelling;

            foreach ($set->punten as $punt) {
                switch (true) {
                    case $punt instanceof DwfPunt:
                        $this->gespeeldeWedstrijdenGateway->AddPunt(
                            $wedstrijd->matchId,
                            $wedstrijd->team1,
                            $currentSet + 1,
                            $punt,
                            $opstelling
                        );
                        if ($punt->serverendTeam != $punt->scorendTeam && $punt->scorendTeam == ThuisUit::THUIS) {
                            $opstelling->Doordraaien();
                        }
                        break;
                    case $punt instanceof DwfWissel:
                        if ($punt->team === ThuisUit::THUIS) {
                            try {
                                $veldspeler = $wedstrijd->team1->GetSpelerByRugnummer($punt->veldspeler);
                                $bankspeler = $wedstrijd->team1->GetSpelerByRugnummer($punt->bankspeler);
                                $opstelling->WisselSpeler($veldspeler, $bankspeler);
                            } catch (UnexpectedValueException $exception) {
                                // This exception is only thrown with very rare cases where
                                // an 'Uitzonderlijke spelerswissel' occurs. Only 1 match so far
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }
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
}
