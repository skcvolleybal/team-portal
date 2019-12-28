<?php


class UpdateAanwezigheid implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->aanwezigheidGateway = new AanwezigheidGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            UnauthorizedResult();
        }

        $playerId = $data->spelerId ?? $userId;
        $matchId = $data->matchId;
        $isAanwezig = $data->isAanwezig;
        $rol = $data->rol;

        $aanwezigheid = $this->aanwezigheidGateway->GetAanwezigheid($playerId, $matchId, $rol);
        if ($aanwezigheid) {
            if ($isAanwezig === 'Onbekend') {
                $this->aanwezigheidGateway->Delete($aanwezigheid->id);
            } else {
                $this->aanwezigheidGateway->Update($aanwezigheid->id, $isAanwezig);
            }
        } else {
            if ($isAanwezig !== 'Onbekend') {
                $this->aanwezigheidGateway->Insert($playerId, $matchId, $isAanwezig, $rol);
            }
        }
    }
}
