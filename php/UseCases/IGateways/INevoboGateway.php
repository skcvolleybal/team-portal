<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Team;

interface INevoboGateway
{
    function GetStandForPoule(string $poule): array;
    function GetProgrammaForPoule(string $poule): array;
    function GetProgrammaForSporthal(string $sporthal = 'LDNUN'): array;
    function GetWedstrijddagenForSporthal(string $sporthal = 'LDNUN', int $dagen = 7): array;
    function GetProgrammaForVereniging(): array;
    function GetWedstrijdenForTeam(?Team $team): array;
    function GetUitslagenForTeam(?Team $team): array;
    function GetUitslagenForVereniging(): array;
    function DoesTeamExist(string $vereniging, string $gender, int $sequence): bool;
}
