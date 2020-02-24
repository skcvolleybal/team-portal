<?php

namespace TeamPortal\Entities;

class ThuisUit
{
    public const THUIS = "thuis";
    public const UIT = "uit";

    public static function WisselTeam(string $team): string
    {
        return $team === ThuisUit::THUIS ? ThuisUit::UIT : ThuisUit::THUIS;
    }
}
