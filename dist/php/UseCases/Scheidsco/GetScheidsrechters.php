<?php
include 'IInteractor.php';
include 'UserGateway.php';
include 'IndelingGateway.php';

class GetScheidsrechters implements IInteractor
{
    private $indelingGateway;

    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
        $this->indelingGateway = new IndelingGateway($database);
    }

    public function Execute()
    {
        $userId = $this->userGateway->GetUserId();
        if ($userId == null) {
            UnauthorizedResult();
        }

        if (!$this->userGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }
        $result = [];
        $scheidsrechters = $this->indelingGateway->GetScheidsrechters();
        foreach ($scheidsrechters as $scheidsrechter) {
            $result[] = $this->MapToUsecaseModel($scheidsrechter);
        }
        exit(json_encode($result));
    }

    private function MapToUsecaseModel($scheidsrechter)
    {
        $team = $scheidsrechter['team'];
        $niveau = empty($scheidsrechter['niveau']) ? 'Geen niveau' : $scheidsrechter['niveau'];

        if ($team != null) {
            $team = $team[0] . $team[6];
        } else {
            $team = 'Geen Team';
        }

        return [
            "naam" => $scheidsrechter['naam'],
            "niveau" => $niveau,
            "gefloten" => $scheidsrechter['gefloten'],
            "team" => $team,
        ];
    }
}
