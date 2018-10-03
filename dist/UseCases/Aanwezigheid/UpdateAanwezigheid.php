<?php

include 'IInteractorWithData.php';
include 'UserGateway.php';
include 'NevoboGateway.php';
include 'AanwezigheidGateway.php';

class UpdateAanwezigheid implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
        $this->aanwezigheidGateway = new AanwezigheidGateway($database);
    }

    private $nevoboGateway;

    public function Execute($data)
    {
        $userId = $this->userGateway->GetUserId();

        if ($userId == null) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

        $userIdForMatch = $data->spelerId ?? $userId;
        $matchId = $data->matchId;
        $aanwezigheid = $data->aanwezigheid;

        $this->aanwezigheidGateway->UpdateAanwezigheid($userIdForMatch, $matchId, $aanwezigheid);
        exit;
    }
}
