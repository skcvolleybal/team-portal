<?php

class GetDwfPunten implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->statistiekenGateway = new StatistiekenGateway($database);
    }

    public function Execute(object $data)
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
                "matchId" => $punt->matchId,
                "set" =>  intval($punt->set),
                "skcTeam" => $punt->skcTeam,
                "otherTeam" => $punt->otherTeam,
                "setsSkcTeam" => intval($punt->setsSkcTeam),
                "setsOtherTeam" => intval($punt->setsOtherTeam),
                "isSkcService" => $punt->isSkcService == "Y",
                "isSkcPunt" => $punt->isSkcPunt == "Y",
                "puntenSkcTeam" => intval($punt->puntenSkcTeam),
                "puntenOtherTeam" => intval($punt->puntenOtherTeam),
                "ra" => $this->ToInt($punt->ra),
                "rv" => $this->ToInt($punt->rv),
                "mv" => $this->ToInt($punt->mv),
                "lv" => $this->ToInt($punt->lv),
                "la" => $this->ToInt($punt->la),
                "ma" => $this->ToInt($punt->ma)
            ];
        }


        return $result;
    }

    private function ToInt($getal)
    {
        return $getal ? intval($getal) : null;
    }
}
