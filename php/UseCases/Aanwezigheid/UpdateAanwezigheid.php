<?php

include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';
include_once 'NevoboGateway.php';
include_once 'AanwezigheidGateway.php';

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

        $aanwezigheid = $this->aanwezigheidGateway->GetAanwezigheid($playerId, $matchId);
        if ($aanwezigheid) {
            if ($isAanwezig === 'Onbekend') {
                $this->aanwezigheidGateway->Delete($playerId, $matchId);
            } else {
                $this->aanwezigheidGateway->Update($playerId, $matchId, $isAanwezig);
            }
        } else {
            if ($isAanwezig !== 'Onbekend') {
                $this->aanwezigheidGateway->Insert($playerId, $matchId, $isAanwezig);
            }
        }
    }
}
