<?php

namespace TeamPortal\UseCases;

class ShiftModel
{
    public int $taskToSwapId;
    public int $swapForTaskId;
    public int $userWhoProposedId;
    public int $otherUserId;
    // public string $type;
    public ?string $date = null;
    public ?int $shift = null;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
