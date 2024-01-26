<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Scheidsrechter;
use TeamPortal\Entities\Team;

interface IWordPressGateway
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
    function GetCoachteams(Persoon $user): array;
    function GetCoaches(Team $team): array;
    function GetTrainers(Team $team): array;
    function Login(string $username, string $password): bool;
    function GetAllSpelers();
}
