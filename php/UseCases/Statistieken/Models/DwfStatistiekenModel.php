<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\Utilities;
use TeamPortal\Entities\Spelsysteem;
use TeamPortal\Entities\Wedstrijdpunt;

class DwfStatistiekenModel
{
    public array $spelsystemen = [];
    public int $aantalPunten;
    public array $gespeeldePunten = [];
    public array $services = [];
    public array $plusminus = [];
    public array $plusminusAlleenVoor = [];
    public array $combinaties = [];
    public array $eigenCombinaties = [];
    public array $servicereeksen = [];

    public function __construct(array $spelers)
    {
        foreach ($spelers as $speler) {
            $plusminus = new PuntenModel("speler");
            $plusminus->rugnummer = $speler->rugnummer;
            $plusminus->naam = $speler->naam;
            $plusminus->afkorting = $speler->GetAfkorting();
            $plusminus->voornaam = $speler->GetEersteNaam();
            $this->plusminus[]  = clone $plusminus;

            $this->plusminusAlleenVoor[]  = clone $plusminus;

            $this->services[]  = clone $plusminus;
        }
    }

    public function AddPunt(Wedstrijdpunt $punt, array $spelverdelers, array $rugnummers)
    {
        $punt->spelsysteem = $punt->GetSpelsysteem($spelverdelers);
        $punt->rotatie = $punt->GetRotatie($spelverdelers);

        if ($punt->spelsysteem !== null && $punt->rotatie !== null) {
            $i = array_search($punt->spelsysteem, array_column($this->spelsystemen, 'type'));
            if ($i === false) {
                $this->AddSysteem($punt->spelsysteem);
            }

            $this->spelsystemen[$i]->totaalAantalPunten++;
            $this->AddPuntToRotatie($punt, $this->spelsystemen[$i]->puntenPerRotatie, $spelverdelers);
            if ($punt->isSkcService) {
                $this->AddPuntToRotatie($punt, $this->spelsystemen[$i]->puntenPerRotatieEigenService, $spelverdelers);
            } else {
                $this->AddPuntToRotatie($punt, $this->spelsystemen[$i]->puntenPerRotatieServiceontvangst, $spelverdelers);
            }

            $this->AddPuntToSpelers($punt, $this->plusminus, $rugnummers);
            $this->AddPuntToCombinaties($punt, $this->combinaties, $rugnummers);

            $voorspelers = $this->GetVoorspelers($punt);
            $this->AddPuntToSpelers($punt, $this->plusminusAlleenVoor, $voorspelers);

            if ($punt->isSkcService && $punt->rechtsachter !== null) {
                $this->AddPuntToSpelers($punt, $this->services, [$punt->rechtsachter]);
            }
        }
    }

    public function AddSysteem(string $type)
    {
        $spelsysteem = new Spelsysteem($type);
        $aantalRotaties = $type === Spelsysteem::VIJF_EEN ? 6 : 3;
        for ($i = 1; $i <= $aantalRotaties; $i++) {
            $spelsysteem->puntenPerRotatie[] = new PuntenModel("Rotatie $i");
            $spelsysteem->puntenPerRotatieEigenService[] = new PuntenModel("Rotatie $i");
            $spelsysteem->puntenPerRotatieServiceontvangst[] = new PuntenModel("Rotatie $i");
        }
        $this->spelsystemen[] = $spelsysteem;
    }

    private function GetVoorspelers(Wedstrijdpunt $punt)
    {
        $voorspelers = [$punt->rechtsvoor, $punt->midvoor, $punt->linksvoor];
        return array_filter($voorspelers, function ($speler) {
            return $speler != null;
        });
    }

    private function AddPuntToCombinaties(Wedstrijdpunt $punt, array &$combinaties, array $rugnummers)
    {
        $aantalRugnummers = count($rugnummers);
        for ($i = 0; $i < $aantalRugnummers - 1; $i++) {
            for ($j = $i + 1; $j < $aantalRugnummers; $j++) {
                $combinatie = $rugnummers[$i] < $rugnummers[$j] ? $rugnummers[$i] . "-" . $rugnummers[$j] : $rugnummers[$j] . "-" . $rugnummers[$i];
                $key = array_search($combinatie, array_column($combinaties, 'type'));
                if ($key === false) {
                    $newPuntenModel = new PuntenModel("combinaties");
                    $newPuntenModel->type = $combinatie;
                    $newPuntenModel->AddPunt($punt);
                    $combinaties[] = $newPuntenModel;
                } else {
                    $combinaties[$key]->AddPunt($punt);
                }
            }
        }
    }

    private function AddPuntToRotatie(Wedstrijdpunt $punt, array $rotaties, array $spelverdelers)
    {
        $i = $punt->GetRotatie($spelverdelers);
        $rotaties[$i]->AddPunt($punt);
    }

    private function AddPuntToSpelers(Wedstrijdpunt $punt, array &$spelers, array $rugnummers)
    {
        foreach ($rugnummers as $rugnummer) {
            $i = array_search($rugnummer, array_column($spelers, 'rugnummer'));
            if ($i !== false) {
                $spelers[$i]->AddPunt($punt);
            }
        }
    }

    public function CalculateRotatieStatistieken()
    {
        foreach ($this->spelsystemen as $spelsysteem) {
            $this->CalculateStatistieken($spelsysteem->puntenPerRotatie, false);
            $this->CalculateStatistieken($spelsysteem->puntenPerRotatieEigenService, false);
            $this->CalculateStatistieken($spelsysteem->puntenPerRotatieServiceontvangst, false);
        }
    }

    public function CalculateSpelersStatistieken()
    {
        $this->CalculateStatistieken($this->services);
        $this->CalculateStatistieken($this->plusminus);
        $this->CalculateStatistieken($this->plusminusAlleenVoor);
        $this->CalculateStatistieken($this->combinaties);
        $this->CalculateStatistieken($this->eigenCombinaties);
    }

    private function CalculateStatistieken(array &$samenvattingen, bool $isSorted = true)
    {
        foreach ($samenvattingen as $samenvatting) {
            $samenvatting->CalculatePercentages();
            $samenvatting->CalculatePlusminus();
        }
        PuntenModel::Normalize($samenvattingen);
        if ($isSorted) {
            usort($samenvattingen, [PuntenModel::class, "Compare"]);
        }
    }
}
