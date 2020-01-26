<?php

namespace TeamPortal\Entities;

class Aanwezigheid
{
    public ?int $id;
    public Persoon $persoon;
    public string $matchId;
    public ?bool $isAanwezig;
    public string $rol;

    public function __construct(string $matchId, Persoon $persoon, ?bool $isAanwezig, string $rol, $id = null)
    {
        $this->id = $id;
        $this->persoon = $persoon;
        $this->isAanwezig = $isAanwezig;
        $this->rol = $rol;
        $this->matchId = $matchId;
    }

    public function IsCoach(): bool
    {
        return $this->rol === 'coach';
    }
}
