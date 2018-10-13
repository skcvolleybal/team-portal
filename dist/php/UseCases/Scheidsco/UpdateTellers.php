<?php
include 'IInteractorWithData.php';
include 'TelFluitGateway.php';
include 'JoomlaGateway.php';

class UpdateTellers implements IInteractorWithData
{
    private $telFluitGateway;
    private $joomlaGateway;

    public function __construct($database)
    {
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId == null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }

        $matchId = $data->matchId ?? null;
        $tellers = $data->tellers ?? null;

        if ($matchId == null) {
            InternalServerError("matchId is null");
        }

        $team = $this->joomlaGateway->GetTeamByNaam($tellers);
        $wedstrijd = $this->telFluitGateway->GetWedstrijd($matchId);
        if ($wedstrijd == null) {
            if ($team != null) {
                $this->telFluitGateway->Insert($matchId, null, $team['id']);
            }
        } else {
            if ($team == null) {
                if ($wedstrijd['scheidsrechter_id'] == null) {
                    $this->telFluitGateway->Delete($matchId);
                }
            } else {
                $this->telFluitGateway->Update($matchId, $wedstrijd['scheidsrechter_id'], $team['id']);
            }
        }

        exit();
    }

}
