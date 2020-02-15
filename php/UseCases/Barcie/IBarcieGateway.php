<?php

namespace TeamPortal\UseCases;

use DateTime;
use TeamPortal\Entities\Barbeschikbaarheid;
use TeamPortal\Entities\Bardag;
use TeamPortal\Entities\Bardienst;
use TeamPortal\Entities\Persoon;

interface IBarcieGateway
{
    function MapToBardagen(array $rows): array;
    function GetBardagen(): array;
    function GetBardag(DateTime $date): Bardag;
    function AddBardag(DateTime $date);
    function GetBeschikbaarheden(Persoon $persoon): array;
    function GetBeschikbaarhedenForDate(DateTime $date): array;
    function GetBeschikbaarheid(Persoon $user, Bardag $bardag): Barbeschikbaarheid;
    function UpdateBeschikbaarheid(Barbeschikbaarheid $beschikbaarheid): void;
    function DeleteBeschikbaarheid(Barbeschikbaarheid $beschikbaarheid): void;
    function InsertBeschikbaarheid(Barbeschikbaarheid $beschikbaarheid): void;
    function GetBardienst(Bardag $dag, Persoon $user, int $shift): Bardienst;
    function GetBardiensten(): array;
    function GetBarleden(): array;
    function InsertBardienst(Bardienst $dienst): void;
    function DeleteBardienst(Bardienst $bardienst): void;
    function DeleteBardag(Bardag $bardag): void;
    function ToggleBhv(Bardienst $bardienst): void;
    function GetBardienstenForUser(Persoon $user): array;
    function MapToBardiensten(array $rows): array;
    function MapToBeschikbaarheden(array $rows): array;
}
