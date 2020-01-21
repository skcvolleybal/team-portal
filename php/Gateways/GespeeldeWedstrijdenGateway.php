<?php


class GespeeldeWedstrijdenGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetGespeeldeWedstrijden(): array
    {
        $query = 'SELECT 
                    *
                  FROM DWF_wedstrijden';
        $rows = $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new DwfWedstrijd(
                $row->id,
                new Team($row->skcTeam),
                new Team($row->otherTeam),
                $row->setsSkcTeam,
                $row->setsOtherTeam
            );
        }
        return $result;
    }

    public function AddWedstrijd(DwfWedstrijd $wedstrijd)
    {
        $query = 'INSERT INTO DWF_wedstrijden (id, skcTeam, otherTeam, setsSkcTeam, setsOtherTeam)
                  VALUES (?, ?, ?, ?, ?)';
        $params = [
            $wedstrijd->matchId,
            $wedstrijd->team1->naam,
            $wedstrijd->team2->naam,
            $wedstrijd->setsTeam1,
            $wedstrijd->setsTeam2
        ];
        $this->database->Execute($query, $params);
    }

    public function AddPunt(string $wedstrijdId, Team $skcTeam, int $set, bool $isSkcService, bool $isSkcPunt, int $puntenSkcTeam, int $puntenOtherTeam, array $opstelling)
    {
        $query = 'INSERT INTO DWF_punten (matchId, skcTeam, `set`, isSkcService, isSkcPunt, puntenSkcTeam, puntenOtherTeam, ra, rv, mv, lv, la, ma)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $params = [
            $wedstrijdId,
            $skcTeam->naam,
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
