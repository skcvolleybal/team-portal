<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Entities\DwfOpstelling;
use TeamPortal\Entities\DwfPunt;
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
        Team $team,
        int $set,
        DwfPunt $punt,
        DwfOpstelling $opstelling
    ): void {
        $isSkcService = $punt->serverendTeam === ThuisUit::THUIS;
        $isSkcPunt = $punt->scorendTeam === ThuisUit::THUIS;

        $query = 'INSERT INTO DWF_punten (matchId, skcTeam, `set`, isSkcService, isSkcPunt, puntenSkcTeam, puntenOtherTeam, ra, rv, mv, lv, la, ma, rotatie)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $params = [
            $wedstrijdId,
            $team->naam,
            $set,
            $isSkcService ? 'Y' : 'N',
            $isSkcPunt ? 'Y' : 'N',
            $punt->puntenThuisTeam,
            $punt->puntenUitTeam,
            $opstelling->GetUserIdRechtsachter(),
            $opstelling->GetUserIdRechtsvoor(),
            $opstelling->GetUserIdMidvoor(),
            $opstelling->GetUserIdLinksvoor(),
            $opstelling->GetUserIdLinksachter(),
            $opstelling->GetUserIdMidAchter(),
            $opstelling->rotatie
        ];
        $this->database->Execute($query, $params);
    }

    public function GetGespeeldePunten(Team $team, string $matchId = ""): array
    {
        $query = 'SELECT 
                    U.id, 
                    U.name AS naam, 
                    U.email,
                    C.cb_rugnummer as rugnummer,
                    C.cb_positie as positie,
                    C.cb_nevobocode as relatiecode,
                    P.aantalGespeeldePunten
                  FROM (
                    SELECT userId, count(*) aantalGespeeldePunten FROM (
                        SELECT ra AS userId FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                        UNION ALL
                        SELECT rv AS userId FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                        UNION ALL
                        SELECT mv AS userId FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                        UNION ALL
                        SELECT lv AS userId FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                        UNION ALL
                        SELECT la AS userId FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                        UNION ALL
                        SELECT ma AS userId FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.skcTeam = :nevobonaam AND (W.id = :matchId OR :matchId = "")
                    ) T1
                    GROUP BY userId ORDER BY aantalGespeeldePunten DESC
                  ) AS P
                  INNER JOIN J3_users U ON P.userId = U.id
                  INNER JOIN J3_comprofiler C ON U.id = C.user_id
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G on M.group_id = G.id
                  WHERE G.title = :skcnaam
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
            $persoon = new Persoon($row->id, $row->naam, $row->email);
            $persoon->aantalGespeeldePunten = $row->aantalGespeeldePunten ?? 0;
            $result[] = $persoon;
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
                    S.services,
                    U.name AS naam,
                    W.skcTeam,
                    S.set,
                    W.otherTeam
                  FROM (
                    SELECT P.matchId, ra, skcTeam, `set`, COUNT(*) AS services 
                    FROM DWF_punten P
                    WHERE isSkcService = 'Y' AND ra IS NOT null
                    GROUP BY P.matchId, skcTeam, `set`, isSkcService, ra, rotatie
                    ORDER BY services desc
                    LIMIT 10
                  ) S
                  INNER JOIN DWF_wedstrijden W ON S.matchId = W.id AND S.skcTeam = W.skcTeam
                  INNER JOIN J3_users U ON S.ra = U.id";
        return $this->database->Execute($query);
    }

    private function MapToDwfPunten(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $punt = new Wedstrijdpunt(
                $row->matchId,
                $row->set,
                $row->isSkcService === "Y",
                $row->isSkcPunt === "Y",
                $row->puntenSkcTeam,
                $row->puntenOtherTeam
            );
            $punt->SetOpstelling($row->ra, $row->rv, $row->mv, $row->lv, $row->la, $row->ma);

            $result[] = $punt;
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
