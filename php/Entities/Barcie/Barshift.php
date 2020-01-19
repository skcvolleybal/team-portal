<?php
class Barshift
{
    public ?int $id;
    public int $shift;
    public array $barleden = [];

    public function __construct(int $shift, int $id = null)
    {
        $this->id = $id;
        $this->shift = $shift;
    }
}
