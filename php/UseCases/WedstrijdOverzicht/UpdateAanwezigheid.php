<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class UpdateAanwezigheid implements Interactor
{
    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\AanwezigheidGateway $aanwezigheidGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->aanwezigheidGateway = $aanwezigheidGateway;
    }

    public function Execute(object $data = null)
    {
        $spelerId = $data->spelerId;
        $user = $spelerId === null ? $this->joomlaGateway->GetUser() : $this->joomlaGateway->GetUser($spelerId);
        $user->team = $this->joomlaGateway->GetTeam($user);
        $matchId = $data->matchId;
        $isAanwezig = $data->isAanwezig;
        $rol = $data->rol;

        $aanwezigheid = $this->aanwezigheidGateway->GetAanwezigheid($user, $matchId, $rol);
        $aanwezigheid->isAanwezig = $isAanwezig;
        if ($aanwezigheid->id === null) {
            if ($aanwezigheid->isAanwezig !== null) {
                $this->aanwezigheidGateway->Insert($aanwezigheid);
            }
        } else {
            if ($aanwezigheid->isAanwezig === null) {
                $this->aanwezigheidGateway->Delete($aanwezigheid);
            } else {
                $this->aanwezigheidGateway->Update($aanwezigheid);
            }
        }
    }
}
