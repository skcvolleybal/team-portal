<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\WordPressGateway;

class GetCurrentUser implements Interactor
{
    public function __construct(WordPressGateway $wordPressGateway)
    {
        $this->wordPressGateway = $wordPressGateway;
    }

    public function Execute(object $data = null)
    {
        return $this->wordPressGateway->GetUser();
    }
}
