<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Bardienst;

class BardienstModel extends Overzichtsitem
{
    public function __construct(Bardienst $dienst)
    {
        parent::__construct("bardienst", $dienst->bardag->date);
        $this->shift = $dienst->shift;
        $this->isBhv = $dienst->isBhv;
        $this->bardagid = $dienst->bardag->id;
        $this->id = $dienst->id;
        $this->userid = $dienst->persoon->id;
    }
}
