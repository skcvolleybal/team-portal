<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\TelFluitGateway;

use TeamPortal\Entities\Barlid;
use TeamPortal\Entities\Persoon;

class GetTelDienstenForUser implements Interactor
{
    public function __construct(
        WordPressGateway $wordPressGateway,
        TelFluitGateway $TelFluitGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->TelFluitGateway = $TelFluitGateway;
    }

    public function Execute(object $data = null) {

        $user = $this->wordPressGateway->GetUser();

        $TelFluitLid = new Persoon($user->id, $user->naam, $user->email);
        // GetFluitEnTelbeurtenFor
        return $this->TelFluitGateway->GetFluitEnTelbeurtenForCalender($TelFluitLid);
    }

}