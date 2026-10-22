<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class AcceptSwaps implements Interactor
{
    public function __construct(
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\SwapGateway $swapGateway,
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->swapGateway = $swapGateway;
    }

    public function Execute(?object $data = null)
    {
        $swap = new ShiftModel($data->acceptSwap);
        $this->swapGateway->AcceptSwaps($swap);
    }
}
