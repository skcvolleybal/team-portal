<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Entities\DwfPunt;
use TeamPortal\Entities\DwfSpeler;
use TeamPortal\Entities\DwfWedstrijd;
use TeamPortal\Entities\Persoon;
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
        $query = 'SELECT W.*
                  FROM DWF_wedstrijden W
                  WHERE skcTeam = ? AND 
                        id IN (SELECT matchId FROM DWF_punten)';
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

    public function GetGespeeldePunten(Team $team, string $matchId = ""): array
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
                      SELECT ra AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                      UNION ALL
                      SELECT rv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                      UNION ALL
                      SELECT mv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                      UNION ALL
                      SELECT lv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                      UNION ALL
                      SELECT la AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                      UNION ALL
                      SELECT ma AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                    ) T1
                    GROUP BY rugnummer ORDER BY aantalGespeeldePunten DESC
                  ) P ON C.cb_rugnummer = P.rugnummer
                  where G.title = :skcnaam
                  ORDER BY
                    CASE 
                        WHEN cb_positie = "Spelverdeler" THEN 1
                        WHEN cb_positie = "Midden" THEN 2
                        WHEN cb_positie = "Buiten" THEN 3
                        WHEN cb_positie = "Diagonaal" THEN 4
                        WHEN cb_positie = "Libero" THEN 5
                        ELSE 6
                    END';
        $params = [
            "nevobonaam" => $team->naam,
            "skcnaam" => $team->GetSkcNaam(),
            "matchId" => $matchId
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

    public function GetLangsteServicereeksen()
    {
        $query = "SELECT 
                    matchId,
                    S.skcTeam,
                    otherTeam,
                    `set`,
                    ra AS rugnummer,
                    naam,
                    services
                  FROM (
                    SELECT `set`, P.matchId, ra, skcTeam, COUNT(*) AS services 
                    FROM DWF_punten P
                    WHERE isSkcService = 'Y' AND ra IS NOT null
                    GROUP BY P.matchId, `set`, ra, puntenOtherTeam
                    ORDER BY services desc
                    LIMIT 1, 10
                  ) S
                  INNER JOIN DWF_wedstrijden W ON S.matchId = W.id AND S.skcTeam = W.skcTeam
                  LEFT JOIN (
                    SELECT U.id, NAME AS naam, CONCAT('SKC ', substr(G.title, 1, 1), 'S ', SUBSTR(G.title, 7, 1)) AS teamnaam, cb_rugnummer AS rugnummer
                    FROM J3_users U 
                    INNER JOIN J3_comprofiler C ON U.id = C.user_id
                    INNER JOIN j3_user_usergroup_map M ON U.id = M.user_id
                    INNER JOIN j3_usergroups G ON M.group_id = G.id
                    WHERE G.parent_id = (SELECT id FROM j3_usergroups WHERE title = 'Teams') AND cb_rugnummer IS NOT null
                  ) T1 ON S.ra = T1.rugnummer AND S.skcTeam = T1.teamnaam
                  ORDER BY services DESC";
        return $this->database->Execute($query);
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
