<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Zaalwacht;
use TeamPortal\Gateways;
use UnexpectedValueException;

class UpdateZaalwacht implements Interactor
{
    public function __construct(
        Gateways\ZaalwachtGateway $zaalwachtGateway,
        Gateways\WordPressGateway $wordPressGateway
    ) {
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->wordPressGateway = $wordPressGateway;
    }

    public function Execute(object $data = null)
    {
        $date = DateFunctions::CreateDateTime($data->date);
        if (!$date) {
            throw new UnexpectedValueException("Incorrecte datum: $data->date");
        }

        $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($date) ?? new Zaalwacht(null, $date, null);
        if ($data->zaalwachttype === 'eerste') {
            $zaalwacht->eersteZaalwacht = $this->wordPressGateway->GetTeamByNaam($data->team);
        } else {
            $zaalwacht->tweedeZaalwacht = $this->wordPressGateway->GetTeamByNaam($data->team);
        }

        if ($zaalwacht->id === null) {
            $this->zaalwachtGateway->Insert($zaalwacht);
        } else {
            if ($zaalwacht->eersteZaalwacht === null && $zaalwacht->tweedeZaalwacht === null) {
                $this->zaalwachtGateway->Delete($zaalwacht);
            } else {
                $this->zaalwachtGateway->Update($zaalwacht);
            }
        }
    }
}
