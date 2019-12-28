<?php


class GespeeldeWedstrijdenGateway
{
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetGespeeldeWedstrijden()
    {
        $query = 'SELECT * FROM DWF_wedstrijden';
        return $this->database->Execute2($query);
    }

    public function AddWedstrijd($wedstrijd)
    {
        $query = 'INSERT INTO DWF_wedstrijden (id, skcTeam, otherTeam, setsSkcTeam, setsOtherTeam)
                  VALUES (?, ?, ?, ?, ?)';
        $params = [
            $wedstrijd->id,
            $wedstrijd->skcTeam,
            $wedstrijd->otherTeam,
            $wedstrijd->setsSkcTeam,
            $wedstrijd->setsOtherTeam
        ];
        $this->database->Execute2($query, $params);
    }

    public function AddPunt($wedstrijdId, $skcTeam, $set, $isSkcService, $isSkcPunt, $puntenSkcTeam, $puntenOtherTeam, $opstelling)
    {
        $query = 'INSERT INTO DWF_punten (matchId, skcTeam, `set`, isSkcService, isSkcPunt, puntenSkcTeam, puntenOtherTeam, ra, rv, mv, lv, la, ma)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $params = [
            $wedstrijdId,
            $skcTeam,
            $set,
            $isSkcService ? 'Y' : 'N',
            $isSkcPunt ? 'Y' : 'N',
            $puntenSkcTeam,
            $puntenOtherTeam,
            $opstelling[0],
            $opstelling[1],
            $opstelling[2],
            $opstelling[3],
            $opstelling[4],
            $opstelling[5]
        ];
        $this->database->Execute2($query, $params);
    }
}
