<?php

namespace TeamPortal\Entities;

class Speler extends Persoon
{
    public bool $isInvaller;

    function __construct(int $id, string $naam, bool $isInvaller = false)
    {
        $this->isInvaller = $isInvaller;
        parent::__construct($id, $naam, "");
    }
}
