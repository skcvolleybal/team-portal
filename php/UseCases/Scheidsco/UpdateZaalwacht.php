<?php

class UpdateZaalwacht implements Interactor
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
        $date = DateFunctions::CreateDateTime($data->date);
        if (!$date) {
            throw new UnexpectedValueException("Incorrecte datum: $data->date");
        }

        $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($date) ?? new Zaalwacht($date);
        $zaalwacht->team = $this->joomlaGateway->GetTeamByNaam($data->team);
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
