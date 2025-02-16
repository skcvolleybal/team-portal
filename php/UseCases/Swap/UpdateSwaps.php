<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

error_reporting(E_ALL ^ E_DEPRECATED); // Suppress warnings on PHP 8.0. Make sure to fix the usort() functions in this file for PHP 8.1. 

class UpdateSwaps implements Interactor
{
    public function __construct(
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\SwapGateway $swapGateway        

        ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->swapGateway = $swapGateway;
    }

    public function Execute(object $data = null)
    {
        $shift = new ShiftModel($data->newSwap);

        $this->swapGateway->UpdateSwaps($shift);
    }
}
