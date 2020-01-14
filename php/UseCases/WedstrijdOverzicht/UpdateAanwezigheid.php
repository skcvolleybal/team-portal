<?php


class UpdateAanwezigheid implements IInteractorWithData
{
    public function __construct(JoomlaGateway $joomlaGateway, AanwezigheidGateway $aanwezigheidGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
        $this->aanwezigheidGateway = $aanwezigheidGateway;
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            throw new UnauthorizedException();
        }

        $spelerId = $data->spelerId ?? $userId;
        $speler = $this->joomlaGateway->GetUser($spelerId);
        $matchId = $data->matchId;
        $isAanwezig = $data->isAanwezig;
        $rol = $data->rol;

        $aanwezigheid = $this->aanwezigheidGateway->GetAanwezigheid($spelerId, $matchId, $rol) ?? new Aanwezigheid($matchId, new Persoon($speler->id, $speler->naam), $isAanwezig, $rol);
        $aanwezigheid->isAanwezig = $isAanwezig;
        if ($aanwezigheid->id === null) {
            if ($aanwezigheid->isAanwezig !== null) {
                $this->aanwezigheidGateway->Insert($aanwezigheid);
            }
        } else {
            if ($aanwezigheid->isAanwezig === null) {
                $this->aanwezigheidGateway->Delete($aanwezigheid);
            } else {
                $this->aanwezigheidGateway->Update($aanwezigheid);
            }
        }
    }
}
