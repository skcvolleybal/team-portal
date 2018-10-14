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

    private $nevoboGateway;

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            UnauthorizedResult();
        }

        $userIdForMatch = $data->spelerId ?? $userId;
        $matchId = $data->matchId;
        $aanwezigheid = $data->aanwezigheid;

        $this->aanwezigheidGateway->UpdateAanwezigheid($userIdForMatch, $matchId, $aanwezigheid);
        exit;
    }
}
