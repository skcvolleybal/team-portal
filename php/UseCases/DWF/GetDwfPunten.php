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
        $input = $data->input ?? null;
        if (empty($input)) {
            $punten = $this->statistiekenGateway->GetAlleSkcPunten();
        } else if (isNevoboFormat($input) || isSkcFormat($input)) {
            $punten = $this->statistiekenGateway->GetAllePuntenByTeam($input);
        } else if (preg_match_all($matchIdregex, $input)) {
            $punten = $this->statistiekenGateway->GetAllePuntenByMatchId($input);
        } else {
            throw new UnexpectedValueException("Input is niets: $input");
        }

        $result = [];
        foreach ($punten as $punt) {
            $result[] = (object) [
                "id" =>  $punt->id,
                "matchId" => $punt->matchId,
                "currentSet" =>  $punt->currentSet,
                "team1" => $punt->team1,
                "team2" => $punt->team2,
                "setsTeam1" => $punt->setsTeam1,
                "setsTeam2" => $punt->setsTeam2,
                "isThuisService" =>  $punt->isThuisService,
                "isThuisPunt" => $punt->isThuisPunt,
                "puntenTeam1" => $punt->puntenTeam1,
                "puntenTeam2" => $punt->puntenTeam2,
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
