<?php

class UpdateZaalwacht implements IInteractorWithData
{
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

        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen teamcoordinator");
        }

        $datum = $data->date ?? null;
        $teamnaam = $data->team ?? null;

        if (IsDateValid($datum) == false) {
            throw new UnexpectedValueException("Foute datum: $datum");
        }

        $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($datum);
        $team = $this->joomlaGateway->GetTeamByNaam($teamnaam);

        if ($zaalwacht) {
            if ($team == null) {
                $this->zaalwachtGateway->Delete($zaalwacht);
            } else {
                $this->zaalwachtGateway->Update($zaalwacht, $team);
            }
        } else if ($team) {
            $this->zaalwachtGateway->Insert($datum, $team);
        }

        exit();
    }
}
