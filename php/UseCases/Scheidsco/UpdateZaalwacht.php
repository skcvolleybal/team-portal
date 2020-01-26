<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Zaalwacht;
use TeamPortal\Gateways;

class UpdateZaalwacht implements Interactor
{
    public function __construct(
        Gateways\ZaalwachtGateway $zaalwachtGateway,
        Gateways\JoomlaGateway $joomlaGateway
    ) {
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $date = DateFunctions::CreateDateTime($data->date);
        if (!$date) {
            throw new \UnexpectedValueException("Incorrecte datum: $data->date");
        }

        $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($date) ?? new Zaalwacht(null, $date, null);
        $zaalwacht->team = $this->joomlaGateway->GetTeamByNaam($data->team);
        if ($zaalwacht->id === null) {
            $this->zaalwachtGateway->Insert($zaalwacht);
        } else {
            if ($zaalwacht->team === null) {
                $this->zaalwachtGateway->Delete($zaalwacht);
            } else {
                $this->zaalwachtGateway->Update($zaalwacht);
            }
        }
    }
}
