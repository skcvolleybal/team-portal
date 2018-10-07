<?php
include 'IInteractorWithData.php';
include 'IndelingGateway.php';
include 'UserGateway.php';

class UpdateScheidscoWedstrijd implements IInteractorWithData
{
    private $indelingGateway;
    private $userGateway;

    public function __construct($database)
    {
        $this->indelingGateway = new IndelingGateway($database);
        $this->userGateway = new UserGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->userGateway->GetUserId();
        if ($userId == null) {
            UnauthorizedResult();
        }

        if (!$this->userGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }

        $matchId = $data->matchId ?? null;
        $scheidsrechter = $data->scheidsrechter ?? null;
        $telteam = $data->telteam ?? null;

        if ($matchId == null) {
            InternalServerError("matchId is null");
        }

        $this->indelingGateway->UpdateScheidscoWedstrijd($matchId, $scheidsrechter, $telteam);
        exit();
    }

}
