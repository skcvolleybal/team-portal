<?php

class GetDwfPunten implements Interactor
{
    public function __construct(
        StatistiekenGateway $statistiekenGateway,
        JoomlaGateway $joomlaGateway
    ) {
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
        $skcTeams = Team::$alleSkcTeams;
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
            $result[] = new Wedstrijdpunt(
                null,
                $punt->matchId,
                $punt->skcTeam,
                intval($punt->set),
                $punt->isSkcService == "Y",
                $punt->isSkcPunt == "Y",
                intval($punt->puntenSkcTeam),
                intval($punt->puntenOtherTeam),
                StringToInt($punt->ra),
                StringToInt($punt->rv),
                StringToInt($punt->mv),
                StringToInt($punt->lv),
                StringToInt($punt->la),
                StringToInt($punt->ma)
            );
        }

        return $result;
    }
}
