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
        $query = "SELECT R.naam, T2.rugnummer, T2.gespeeldePunten FROM (
                    SELECT rugnummer, count(*) gespeeldePunten FROM (
                        SELECT ra as rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT rv as rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT mv as rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT lv as rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT la as rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT ma as rugnummer FROM DWF_punten P inner join DWF_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                    ) T1
                    GROUP BY rugnummer ORDER BY gespeeldePunten DESC
                  ) T2
                  LEFT JOIN (
                    SELECT C.id, U.name as naam, C.cb_rugnummer as rugnummer
                    FROM J3_users U
                    INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                    INNER JOIN J3_usergroups G on M.group_id = G.id
                    INNER JOIN J3_comprofiler C ON U.id = C.user_id
                    where G.title = :skcTeam
                  ) R ON T2.rugnummer = R.rugnummer";
        $params = [
            new Param(":team", $team, PDO::PARAM_STR),
            new Param(":skcTeam", $skcTeam, PDO::PARAM_STR),
        ];
        return $this->database->Execute($query, $params);
    }
}
