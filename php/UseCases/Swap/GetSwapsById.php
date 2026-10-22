<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class GetSwapsById implements Interactor
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
        return $this->swapGateway->GetSwapsById($data);
    }
}
