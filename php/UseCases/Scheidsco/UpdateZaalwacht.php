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

    public function Execute(object $data)
    {
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
