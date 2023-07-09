<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class UpdateAanwezigheid implements Interactor
{
    public function __construct(
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\AanwezigheidGateway $aanwezigheidGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->aanwezigheidGateway = $aanwezigheidGateway;
    }

    public function Execute(object $data = null)
    {
        $spelerId = $data->spelerId;
        $user = $spelerId === null ? $this->wordPressGateway->GetUser() : $this->wordPressGateway->GetUser($spelerId);
        $user->team = $this->wordPressGateway->GetTeam($user);
        $matchId = $data->matchId;
        $isAanwezig = $data->isAanwezig;
        $rol = $data->rol;
        if ($rol === 'invaller') {
            $rol = 'speler';
        }

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
