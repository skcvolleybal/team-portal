<?php

class MijnOverzicht implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway,
        TelFluitGateway $telFluitGateway,
        ZaalwachtGateway $zaalwachtGateway,
        BarcieGateway $barcieGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data = null)
    {
        $overzicht = [];

        $user = $this->joomlaGateway->GetUser();
        $user->team = $this->joomlaGateway->GetTeam($user);

        $zaalwachten = $this->zaalwachtGateway->GetZaalwachtenOfUser($user);
        foreach ($zaalwachten as $zaalwacht) {
            $this->AddZaalwachtToOverzicht($overzicht, $zaalwacht);
        }

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        $wedstrijden = $this->telFluitGateway->GetFluitEnTelbeurten($user);
        foreach ($wedstrijden as $wedstrijd) {
            $uscMatch = Wedstrijd::GetWedstrijdWithMatchId($uscWedstrijden, $wedstrijd->matchId);
            $wedstrijd->AppendInformation($uscMatch);
            $this->AddWedstrijdToOverzicht($overzicht, $wedstrijd);
        }

        $bardiensten = $this->barcieGateway->GetBardienstenForUser($user);
        foreach ($bardiensten as $bardienst) {
            $this->AddBardienstToOverzicht($overzicht, $bardienst);
        }

        $team = $this->joomlaGateway->GetTeam($user);
        $speelWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
        foreach ($speelWedstrijden as $wedstrijd) {
            $fluitEnTelWedstrijd = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
            $wedstrijd->AppendInformation($fluitEnTelWedstrijd);

            $this->AddWedstrijdToOverzicht($overzicht, $wedstrijd);
        }

        $coachteam = $this->joomlaGateway->GetCoachTeam($user);
        if ($coachteam) {
            $wedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($coachteam);
            foreach ($wedstrijden as $wedstrijd) {
                $this->AddWedstrijdToOverzicht($overzicht, $wedstrijd);
            }
        }

        usort($overzicht, Wedstrijddag::class . "::Compare");
        foreach ($overzicht as $dag) {
            usort($dag->speeltijden, Speeltijd::class . "::Compare");
        }

        return $this->MapToUseCaseModel($overzicht, $user, $team, $coachteam);
    }

    private function MapToUseCaseModel(array $wedstrijddagen, Persoon $persoon, ?Team $team, ?Team $coachteam): array
    {
        $result = [];
        foreach ($wedstrijddagen as $wedstrijddag) {
            $newWedstrijddag = new WedstrijddagModel($wedstrijddag);

            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                $newSpeeltijd = new SpeeltijdModel($speeltijd);
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    $newWedstrijd = new WedstrijdModel($wedstrijd);
                    $newWedstrijd->SetPersonalInformation($wedstrijd, $persoon, $team, $coachteam);
                    $newSpeeltijd->wedstrijden[] = $newWedstrijd;
                }

                $newWedstrijddag->speeltijden[] = $newSpeeltijd;
            }
            $result[] = $newWedstrijddag;
        }

        return $result;
    }

    private function AddZaalwachtToOverzicht(array &$dagen, Zaalwacht $zaalwacht): void
    {
        foreach ($dagen as $dag) {
            if (DateFunctions::AreDatesEqual($dag->date, $zaalwacht->date)) {
                $dag->zaalwacht = $zaalwacht;
                return;
            }
        }
        $dag = new Wedstrijddag($zaalwacht->date);
        $dag->zaalwacht = $zaalwacht;
        $dagen[] = $dag;
    }

    private function AddWedstrijdToOverzicht(array &$dagen, Wedstrijd $wedstrijd): void
    {
        foreach ($dagen as $dag) {
            if (DateFunctions::AreDatesEqual($dag->date, $wedstrijd->timestamp)) {
                foreach ($dag->speeltijden as $speeltijd) {
                    if ($speeltijd->time == $wedstrijd->timestamp) {
                        $speeltijd->wedstrijden[] = $wedstrijd;
                        return;
                    }
                }
                $speeltijd = new Speeltijd($wedstrijd->timestamp);
                $speeltijd->wedstrijden[] = $wedstrijd;
                $dag->speeltijden[] = $speeltijd;
                return;
            }
        }

        $dag = new Wedstrijddag($wedstrijd->timestamp);
        $speeltijd = new Speeltijd($wedstrijd->timestamp);
        $speeltijd->wedstrijden[] = $wedstrijd;
        $dag->speeltijden[] = $speeltijd;
        $dagen[] = $dag;
    }

    private function AddBardienstToOverzicht(array &$dagen, Bardienst $dienst)
    {
        foreach ($dagen as $dag) {
            if (DateFunctions::AreDatesEqual($dag->date, $dienst->bardag->date)) {
                $dag->bardiensten[] = $dienst;
                return;
            }
        }

        $dag = new Wedstrijddag($dienst->bardag->date);
        $dag->bardiensten[] = $dienst;
        $dagen[] = $dag;
    }
}
