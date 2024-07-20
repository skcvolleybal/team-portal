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

        if ($data->id === null) {
            throw new InvalidArgumentException("ID is niet set");
        }

        $user = get_user_by('ID', $data->id);

        

        $TelFluitLid = new Persoon($user->ID, $user->display_name, $user->user_email);
        // GetFluitEnTelbeurtenFor
        return $this->TelFluitGateway->GetFluitEnTelbeurtenForCalender($TelFluitLid);
    }

}