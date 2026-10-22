<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class DeleteSwaps implements Interactor
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
        $this->swapGateway->DeleteSwaps($data);
    }
}
