<?php
include_once 'IInteractorWithData.php';
include_once 'ZaalwachtGateway.php';
include_once 'JoomlaGateway.php';

class UpdateZaalwacht implements IInteractorWithData
{
    private $zaalwachtGateway;
    private $joomlaGateway;

    public function __construct($database)
    {
        $this->zaalwachtGateway = new ZaalwachtGateway($database);
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

        $datum = $data->date ?? null;
        $teamnaam = $data->team ?? null;

        if (IsDateValid($datum) == false) {
            InternalServerError("Foute datum: $datum");
        }

        $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($datum);
        $team = $this->joomlaGateway->GetTeamByNaam($teamnaam);

        if ($zaalwacht) {
            if ($team == null) {
                $this->zaalwachtGateway->Delete($zaalwacht);
            } else {
                $this->zaalwachtGateway->Update($zaalwacht, $team);
            }
        } else {
            if ($team) {
                $this->zaalwachtGateway->Insert($datum, $team);
            }
        }

        exit();
    }
}
