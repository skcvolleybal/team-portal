<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Entities\DwfPunt;
use TeamPortal\Entities\DwfWedstrijd;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\ThuisUit;
use TeamPortal\Entities\Wedstrijdpunt;

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

    public function GetGespeeldePunten(Team $team): array
    {
        $query = 'SELECT 
                    U.id, 
                    U.name AS naam, 
                    email, 
                    C.cb_rugnummer AS rugnummer,
                    P.aantalGespeeldePunten
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G on M.group_id = G.id
                  INNER JOIN J3_comprofiler C ON U.id = C.user_id
                  LEFT JOIN (    
                    SELECT rugnummer, count(*) aantalGespeeldePunten FROM (
                      SELECT ra AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam || W.otherTeam = :nevobonaam
                      UNION ALL
                      SELECT rv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam || W.otherTeam = :nevobonaam
                      UNION ALL
                      SELECT mv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam || W.otherTeam = :nevobonaam
                      UNION ALL
                      SELECT lv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam || W.otherTeam = :nevobonaam
                      UNION ALL
                      SELECT la AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam || W.otherTeam = :nevobonaam
                      UNION ALL
                      SELECT ma AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam || W.otherTeam = :nevobonaam
                    ) T1
                    GROUP BY rugnummer ORDER BY aantalGespeeldePunten DESC
                  ) P ON C.cb_rugnummer = P.rugnummer
                  where G.title = :skcnaam';
        $params = [
            "nevobonaam" => $team->naam,
            "skcnaam" => $team->GetSkcNaam()
        ];
        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new DwfSpeler(
                new Persoon($row->id, $row->naam, $row->email),
                $row->aantalGespeeldePunten ?? 0
            );
        }
        return $result;
    }

    public function GetAllePuntenByTeam(Team $team): array
    {
        $query = 'SELECT P.* 
                  FROM DWF_punten P
                  INNER JOIN DWF_wedstrijden W ON P.matchId = W.id
                  WHERE P.skcTeam = ?
                  ORDER BY P.id';
        $params = [$team->naam];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToDwfPunten($rows);
    }

    public function GetAllePuntenByMatchId(string $matchId, Team $team): array
    {
        $query = 'SELECT P.* 
                  FROM DWF_punten P
                  INNER JOIN DWF_wedstrijden W ON P.matchId = W.id
                  WHERE P.matchId = ? AND P.skcTeam = ?
                  ORDER BY P.id';
        $params = [$matchId, $team->naam];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToDwfPunten($rows);
    }

    public function GetAlleSkcPunten(): array
    {
        $query = 'SELECT P.* 
                  FROM DWF_punten P
                  INNER JOIN DWF_wedstrijden W ON P.matchId = W.id
                  ORDER BY P.id';
        $rows = $this->database->Execute($query);
        return $this->MapToDwfPunten($rows);
    }

    private function MapToDwfPunten(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Wedstrijdpunt(
                $row->id,
                $row->matchId,
                new Team($row->skcTeam),
                $row->set,
                $row->isSkcService === "Y",
                $row->isSkcPunt === "Y",
                $row->puntenSkcTeam,
                $row->puntenOtherTeam,
                $row->ra,
                $row->rv,
                $row->mv,
                $row->lv,
                $row->la,
                $row->ma,
            );
        }

        return $result;
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
