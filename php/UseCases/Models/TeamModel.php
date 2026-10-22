<?php

namespace TeamPortal\UseCases;

class TeamModel
{
    public string $naam;
    public int $aantalZaalwachten = 0;
    public int $aantal = 0;
    public array $teamgenoten = [];
    public bool $isMogelijk = true;
    public ?string $eigenTijd = null;
}
