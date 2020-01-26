<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\JoomlaGateway;

class GetUsers implements Interactor
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $name = $data->naam ?? null;
        $result = [];

        if (strlen($name) >= 3) {
            $result = $this->joomlaGateway->GetUsersWithName($name);
        }

        return $result;
    }
}
