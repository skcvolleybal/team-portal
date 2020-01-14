<?php

class UpdateZaalwacht implements IInteractorWithData
{
    public function __construct(
        ZaalwachtGateway $zaalwachtGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            throw new UnauthorizedException();
        }

        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen teamcoordinator");
        }

        $datum = $data->date ?? null;
        $teamnaam = $data->team ?? null;
        $date = DateFunctions::CreateDateTime($datum);
        if (!$date) {
            throw new UnexpectedValueException("Foute datum: $datum");
        }


        $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($date) ?? new Zaalwacht($date);
        $zaalwacht->team = $this->joomlaGateway->GetTeamByNaam($teamnaam);
        if ($zaalwacht->id === null) {
            $this->zaalwachtGateway->Insert($zaalwacht);
        } else {
            if ($zaalwacht->team === null) {
                $this->zaalwachtGateway->Delete($zaalwacht);
            } else {
                $this->zaalwachtGateway->Update($zaalwacht);
            }
        }
    }
}
