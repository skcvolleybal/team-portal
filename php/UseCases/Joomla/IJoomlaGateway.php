<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Scheidsrechter;
use TeamPortal\Entities\Team;

interface IJoomlaGateway
{
    function GetUser(?int $userId = null): ?Persoon;
    function GetLoggedInUser(): ?Persoon;
    function GetScheidsrechter(?int $userId): ?Scheidsrechter;
    function GetTeamByNaam(?string $naam): ?Team;
    function GetUsersWithName(string $name): array;
    function IsScheidsrechter(?Persoon $user): bool;
    function IsWebcie(?Persoon $user): bool;
    function IsTeamcoordinator(?Persoon $user): bool;
    function IsBarcie(?Persoon $user): bool;
    function GetTeam(Persoon $user): ?Team;
    function GetTeamgenoten(?Team $team): array;
    function GetCoachTeam(Persoon $user): ?Team;
    function GetCoaches(Team $team): array;
    function GetTrainers(Team $team): array;
    function GetUsersInGroup(string $groupname): array;
    function InitJoomla(): void;
    function Login(string $username, string $password): bool;
    function GetSpelerByRugnummer(int $rugnummer, Team $team): ?Persoon;
    function GetRugnummerOfPersoon(Persoon $user);
    function GetAllSpelers();
}
