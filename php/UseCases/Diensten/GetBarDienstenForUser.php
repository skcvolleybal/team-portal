<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\BarcieGateway;

use TeamPortal\Entities\Barlid;
use TeamPortal\Entities\Persoon;

class GetBarDienstenForUser implements Interactor
{
    public function __construct(
        WordPressGateway $wordPressGateway,
        BarcieGateway $BarcieGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->BarcieGateway = $BarcieGateway;
    }

    public function Execute(object $data = null) {

        if ($data->id === null) {
            throw new InvalidArgumentException("ID is niet set");
        }

        $user = get_user_by('ID', $data->id);

        $barlid = new Barlid(
            new Persoon($user->ID, $user->display_name, $user->user_email),
            0
        );

        return $this->BarcieGateway->GetBardienstenForUser($barlid);
    }

}