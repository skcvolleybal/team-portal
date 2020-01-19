<?php

class GetDwfPunten implements Interactor
{
    public function __construct(
        StatistiekenGateway $statistiekenGateway, 
        JoomlaGateway $joomlaGateway)
    {
        $this->statistiekenGateway = $statistiekenGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $matchId = $data->matchId ?? null;
        $matchIdregex = "/3000(B){0,1}[H|D]\d[A-Z] [(\d{2})|[A-Z]{2}/";
        if (!preg_match_all($matchIdregex, $matchId)) {
            throw new UnexpectedValueException("matchId klopt niet: '$data->matchId'. Bv: 3000 H4G DG");
        }
        $skcTeams = Team::GetAlleSkcTeams();
        foreach ($skcTeams as $skcTeam) {
            if ($skcTeam->naam == $data->team) {
                $team = $this->joomlaGateway->GetTeamByNaam($data->team);
                break;
            }
        }
        if ($team === null) {
            throw new UnexpectedValueException("Team klopt niet: '$data->Team'. Bv: SKC HS 2");
        }

        if ($team === null && $matchId === null) {
            $punten = $this->statistiekenGateway->GetAlleSkcPunten();
        } else if ($team !== null && $data->matchId !== null) {
            $punten = $this->statistiekenGateway->GetAllePuntenByMatchId($data->matchId, $team);
        } else if ($team !== null) {
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
                "ra" => StringToInt($punt->ra),
                "rv" => StringToInt($punt->rv),
                "mv" => StringToInt($punt->mv),
                "lv" => StringToInt($punt->lv),
                "la" => StringToInt($punt->la),
                "ma" => StringToInt($punt->ma)
            ];
        }


        return $result;
    }
}
