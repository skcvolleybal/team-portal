<?php

namespace TeamPortal\Entities;

use UnexpectedValueException;

class DwfOpstelling
{
    private array $opstelling = [null, null, null, null, null, null];

    public function GetUserIdRechtsachter()
    {
        return $this->GetUserIdOfPosition(0);
    }
    public function GetUserIdRechtsvoor()
    {
        return $this->GetUserIdOfPosition(1);
    }
    public function GetUserIdMidvoor()
    {
        return $this->GetUserIdOfPosition(2);
    }
    public function GetUserIdLinksvoor()
    {
        return $this->GetUserIdOfPosition(3);
    }
    public  function GetUserIdLinksachter()
    {
        return $this->GetUserIdOfPosition(4);
    }
    public  function GetUserIdMidachter()
    {
        return $this->GetUserIdOfPosition(5);
    }

    function SetSpeler(DwfSpeler $speler, int $positie)
    {
        if ($positie < 0 || $positie > 5) {
            throw new UnexpectedValueException("Gekke positie");
        }

        $this->opstelling[$positie] = $speler;
    }

    function WisselSpeler(DwfSpeler $veldspeler, DwfSpeler $bankspeler)
    {
        for ($j = 0; $j < 6; $j++) {
            if ($this->opstelling[$j] !== null && $this->opstelling[$j]->rugnummer === $veldspeler->rugnummer) {
                $this->opstelling[$j] = $bankspeler;
                return;
            }
        }
    }

    function Doordraaien(): void
    {
        $tmp = $this->opstelling[0];
        $this->opstelling[0] = $this->opstelling[1];
        $this->opstelling[1] = $this->opstelling[2];
        $this->opstelling[2] = $this->opstelling[3];
        $this->opstelling[3] = $this->opstelling[4];
        $this->opstelling[4] = $this->opstelling[5];
        $this->opstelling[5] = $tmp;
    }

    function Terugdraaien(): void
    {
        $tmp = $this->opstelling[5];
        $this->opstelling[5] = $this->opstelling[4];
        $this->opstelling[4] = $this->opstelling[3];
        $this->opstelling[3] = $this->opstelling[2];
        $this->opstelling[2] = $this->opstelling[1];
        $this->opstelling[1] = $this->opstelling[0];
        $this->opstelling[0] = $tmp;
    }

    private function GetUserIdOfPosition(int $positie): ?int
    {
        $speler = $this->opstelling[$positie];
        if ($speler === null) {
            return null;
        }
        return $speler->id;
    }
}
