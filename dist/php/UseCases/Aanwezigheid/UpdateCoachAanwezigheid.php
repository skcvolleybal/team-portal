<?php
include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';
include_once 'AanwezigheidGateway.php';

class UpdateCoachAanwezigheid implements IInteractorWithData
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

        $keuze = $data->aanwezigheid ?? null;
        $matchId = $data->matchId ?? null;
        if (!$keuze) {
            InternalServerError("Aanwezigheid niet gezet");
        }
        if (!$matchId) {
            InternalServerError("matchId niet gezet");
        }

        $aanwezigheid = $this->aanwezigheidGateway->GetCoachAanwezigheid($userId, $matchId);
        if ($aanwezigheid) {
            if ($keuze == 'Onbekend') {
                $this->aanwezigheidGateway->DeleteCoachAanwezigheid($userId, $matchId);
            } else {
                $this->aanwezigheidGateway->UpdateCoachAanwezigheid($userId, $matchId, $keuze);
            }
        } else {
            $this->aanwezigheidGateway->InsertCoachAanwezigheid($userId, $matchId, $keuze);
        }

        exit;
    }
}
