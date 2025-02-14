<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\ZaalwachtGateway;

use TeamPortal\Entities\Barlid;
use TeamPortal\Entities\Persoon;

class GetZaalwachtDienstenForUser implements Interactor
{
    public function __construct(
        WordPressGateway $wordPressGateway,
        ZaalwachtGateway $ZaalwachtGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->ZaalwachtGateway = $ZaalwachtGateway;
    }

    public function Execute(object $data = null) {

        $user = $this->wordPressGateway->GetUser();
        return $this->ZaalwachtGateway->GetZaalwachtenOfUser($user);
    }

}