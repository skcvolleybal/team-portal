<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Entities\DwfPunt;
use TeamPortal\Entities\DwfWedstrijd;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\ThuisUit;

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
        return $this->MapToDomainModel($rows);
    }

    public function GetGespeeldeWedstrijdenByTeam(Team $team): array
    {
        $query = 'SELECT 
                    *
                  FROM DWF_wedstrijden
                  WHERE skcTeam = ?';
        $params = [$team->naam];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToDomainModel($rows);
    }

    public function AddWedstrijd(DwfWedstrijd $wedstrijd): void
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

    public function AddPunt(
        string $wedstrijdId,
        string $location,
        Team $team,
        int $set,
        DwfPunt $punt,
        array $opstelling
    ): void {
        if ($location == ThuisUit::THUIS) {
            $puntenSkc = $punt->puntenThuisTeam;
            $puntenOtherTeam  = $punt->puntenUitTeam;
        } else {
            $puntenSkc = $punt->puntenUitTeam;
            $puntenOtherTeam  = $punt->puntenThuisTeam;
        }
        $isSkcService = $punt->serverendTeam == $location;
        $isSkcPunt = $punt->scorendTeam == $location;

        $query = 'INSERT INTO DWF_punten (matchId, skcTeam, `set`, isSkcService, isSkcPunt, puntenSkcTeam, puntenOtherTeam, ra, rv, mv, lv, la, ma)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $params = [
            $wedstrijdId,
            $team->naam,
            $set,
            $isSkcService ? 'Y' : 'N',
            $isSkcPunt ? 'Y' : 'N',
            $puntenSkc,
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

    private function MapToDomainModel(array $rows)
    {
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
}
