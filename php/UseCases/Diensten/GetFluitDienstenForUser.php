<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\TelFluitGateway;

use TeamPortal\Entities\Barlid;
use TeamPortal\Entities\Persoon;

class GetFluitDienstenForUser implements Interactor
{
    public function __construct(
        WordPressGateway $wordPressGateway,
        TelFluitGateway $TelFluitGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->TelFluitGateway = $TelFluitGateway;
    }

    public function Execute(object $data = null) {

        $FluitLid = new Persoon($data->id, '', null);
        // GetFluitEnTelbeurtenFor
        return $this->TelFluitGateway->GetFluitbeurten($FluitLid);
    }

}