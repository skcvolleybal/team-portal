<?php
include_once 'IInteractorWithData.php';
include_once 'TelFluitGateway.php';
include_once 'JoomlaGateway.php';

class UpdateScheidsrechter implements IInteractorWithData
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
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }

        $matchId = $data->matchId ?? null;
        $scheidsrechter = $data->scheidsrechter ?? null;

        if ($matchId == null) {
            InternalServerError("matchId is null");
        }

        $scheidsrechter = $this->joomlaGateway->GetScheidsrechterByName($scheidsrechter);

        $wedstrijd = $this->telFluitGateway->GetWedstrijd($matchId);
        if ($wedstrijd == null) {
            if ($scheidsrechter) {
                $this->telFluitGateway->Insert($matchId, $scheidsrechter['id'], null);
            }
        } else {
            if ($scheidsrechter == null) {
                if ($wedstrijd['telteamId'] == null) {
                    $this->telFluitGateway->Delete($matchId);
                } else {
                    $this->telFluitGateway->Update($matchId, null, $wedstrijd['telteamId']);
                }
            } else {
                $this->telFluitGateway->Update($matchId, $scheidsrechter['id'], $wedstrijd['telteamId']);
            }
        }

        exit();
    }

}
