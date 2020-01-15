<?php
class Barcieshift
{
    public ?int $id;
    public int $shift;
    public array $barcieleden;

    public function __construct(int $shift, int $id = null)
    {
        $this->id = $id;
        $this->shift = $shift;
    }
}
