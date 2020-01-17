<?php


class UpdateAanwezigheid implements Interactor
{
    public function __construct(JoomlaGateway $joomlaGateway, AanwezigheidGateway $aanwezigheidGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
        $this->aanwezigheidGateway = $aanwezigheidGateway;
    }

    public function Execute(object $data)
    {
        $spelerId = $data->spelerId;
        $user = $spelerId === null ? $this->joomlaGateway->GetUser() : $this->joomlaGateway->GetUser($spelerId);
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
