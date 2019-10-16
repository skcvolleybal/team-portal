<?php
include_once 'IInteractorWithData.php';
include_once 'StatistiekenGateway.php';

class GetDwfPunten implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->statistiekenGateway = new StatistiekenGateway($database);
    }

    public function Execute($data)
    {
        $matchIdregex = "/3000(B){0,1}[H|D]\d[A-Z] [(\d{2})|[A-Z]{2}/";
        $team = $data->team ?? null;
        $matchId = $data->matchId ?? null;

        if ($matchId && !preg_match_all($matchIdregex, $matchId)) {
            throw new UnexpectedValueException("Error: matchId: '$matchId', klopt niet. Bv: 3000 H4G DG");
        }
        if ($team && isSkcFormat($team)) {
            throw new UnexpectedValueException("Error: team: '$team', klopt niet. Bv: SKC HS 2");
        }

        if (empty($team) && empty($matchId)) {
            $punten = $this->statistiekenGateway->GetAlleSkcPunten();
        } else if (!empty($team) && !empty($matchId)) {
            $punten = $this->statistiekenGateway->GetAllePuntenByMatchId($matchId, $team);
        } else if (!empty($team)) {
            $team = ToNevoboName($team);
            $punten = $this->statistiekenGateway->GetAllePuntenByTeam($team);
        } else {
            throw new UnexpectedValueException("Error: team: '$matchId', '$team'");
        }

        $result = [];
        foreach ($punten as $punt) {
            $result[] = (object) [
                "id" =>  $punt->id,
                "matchId" => $punt->matchId,
                "set" =>  $punt->set,
                "skcTeam" => $punt->skcTeam,
                "otherTeam" => $punt->otherTeam,
                "setsSkcTeam" => $punt->setsSkcTeam,
                "setsOtherTeam" => $punt->setsOtherTeam,
                "isSkcService" =>  $punt->isSkcService,
                "isSkcPunt" => $punt->isSkcPunt,
                "puntenSkcTeam" => $punt->puntenSkcTeam,
                "puntenOtherTeam" => $punt->puntenOtherTeam,
                "ra" => $punt->ra,
                "rv" =>  $punt->rv,
                "mv" =>  $punt->mv,
                "lv" =>  $punt->lv,
                "la" =>  $punt->la,
                "ma" =>  $punt->ma
            ];
        }


        return $result;
    }
}
