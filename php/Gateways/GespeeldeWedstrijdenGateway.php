<?php


class GespeeldeWedstrijdenGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetGespeeldeWedstrijden()
    {
        $query = 'SELECT * FROM DWF_wedstrijden';
        return $this->database->Execute($query);
    }

    public function AddWedstrijd(Wedstrijd $wedstrijd)
    {
        $query = 'INSERT INTO DWF_wedstrijden (id, skcTeam, otherTeam, setsSkcTeam, setsOtherTeam)
                  VALUES (?, ?, ?, ?, ?)';
        $params = [
            $wedstrijd->matchId,
            $wedstrijd->skcTeam,
            $wedstrijd->otherTeam,
            $wedstrijd->setsSkcTeam,
            $wedstrijd->setsOtherTeam
        ];
        $this->database->Execute($query, $params);
    }

    public function AddPunt(string $wedstrijdId, Team $skcTeam, int $set, bool $isSkcService, bool $isSkcPunt, int $puntenSkcTeam, int $puntenOtherTeam, array $opstelling)
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
        $this->database->Execute($query, $params);
    }
}
