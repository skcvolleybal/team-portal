<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Barshift;

class BarshiftModel
{
    public int $shift;
    public array $barleden = [];

    public function __construct(Barshift $shift)
    {
        $this->shift = $shift->shift;
    }
}
