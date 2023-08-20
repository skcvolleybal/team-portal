<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\WordPressGateway;

class GetUsers implements Interactor
{
    public function __construct(WordPressGateway $wordPressGateway)
    {
        $this->wordPressGateway = $wordPressGateway;
    }

    public function Execute(object $data = null)
    {
        $name = $data->naam ?? null;
        $result = [];

        if (strlen($name) >= 3) {
            $result = $this->wordPressGateway->GetUsersWithName($name);
        }

        return $result;
    }
}
