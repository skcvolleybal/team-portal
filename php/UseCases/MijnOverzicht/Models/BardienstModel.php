<?php

class BardienstModel extends Overzichtsitem
{
    public function __construct(Bardienst $dienst)
    {
        parent::__construct("bardienst", $dienst->bardag->date);
        $this->shift = $dienst->shift;
        $this->isBhv = $dienst->isBhv;
    }
}
