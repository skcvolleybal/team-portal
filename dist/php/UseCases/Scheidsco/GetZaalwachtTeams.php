<?php
include 'IInteractor.php';
include 'UserGateway.php';
include 'IndelingGateway.php';

class GetZaalwachtTeams implements IInteractor
{
    private $indelingGateway;
    private $userGateway;

    public function __construct($database)
    {
        $this->indelingGateway = new IndelingGateway($database);
        $this->userGateway = new UserGateway($database);
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
        $zaalwachtTeams = $this->indelingGateway->GetZaalwachtTeams();
        foreach ($zaalwachtTeams as $team) {
            $result[] = $this->MapToUsecaseModel($team);
        }
        exit(json_encode($result));
    }

    private function MapToUsecaseModel($team)
    {
        $naam = $team['team'];

        return [
            "naam" => $naam[0] . substr($naam, 6),
            "geteld" => $team['geteld'],
            "zaalwacht" => $team['zaalwacht'],
        ];
    }
}
