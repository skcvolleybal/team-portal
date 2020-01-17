<?php
class StatistiekenGateway
{
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetGespeeldePunten($team)
    {
        $skcTeam = ToSkcName($team);
        $query = 'SELECT R.naam, T2.rugnummer, T2.gespeeldePunten FROM (
                    SELECT rugnummer, count(*) gespeeldePunten FROM (
                        SELECT ra AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = ? || W.team2 = ?
                        UNION ALL
                        SELECT rv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = ? || W.team2 = ?
                        UNION ALL
                        SELECT mv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = ? || W.team2 = ?
                        UNION ALL
                        SELECT lv AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = ? || W.team2 = ?
                        UNION ALL
                        SELECT la AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = ? || W.team2 = ?
                        UNION ALL
                        SELECT ma AS rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = ? || W.team2 = ?
                    ) T1
                    GROUP BY rugnummer ORDER BY gespeeldePunten DESC
                  ) T2
                  LEFT JOIN (
                    SELECT C.id, U.name AS naam, C.cb_rugnummer AS rugnummer
                    FROM J3_users U
                    INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                    INNER JOIN J3_usergroups G on M.group_id = G.id
                    INNER JOIN J3_comprofiler C ON U.id = C.user_id
                    where G.title = ?
                  ) R ON T2.rugnummer = R.rugnummer';
        $params = [$team, $skcTeam];
        return $this->database->Execute($query, $params);
    }

    public function GetAllePuntenByTeam($team)
    {
        $query = 'SELECT * FROM DWF_punten P
                  INNER JOIN DWF_wedstrijden W ON P.matchId = W.id
                  WHERE P.skcTeam = ?
                  ORDER BY P.id';
        $params = [$team];
        return $this->database->Execute($query, $params);
    }

    public function GetAllePuntenByMatchId($matchId, $team)
    {
        $query = 'SELECT * FROM DWF_punten P
                  INNER JOIN DWF_wedstrijden W ON P.matchId = W.id
                  WHERE P.matchId = ? AND P.skcTeam = ?
                  ORDER BY P.id';
        $params = [$matchId, $team];
        return $this->database->Execute($query, $params);
    }

    public function GetAlleSkcPunten()
    {
        $query = 'SELECT * FROM DWF_punten P
                  INNER JOIN DWF_wedstrijden W ON P.matchId = W.id
                  ORDER BY P.id';
        return $this->database->Execute($query);
    }
}
