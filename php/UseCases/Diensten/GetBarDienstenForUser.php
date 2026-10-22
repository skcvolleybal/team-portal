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

    public function Execute(?object $data = null) {

        $user = $this->wordPressGateway->GetUser($data->id);
        return $this->BarcieGateway->GetBardienstenForUser($user);
    }

}