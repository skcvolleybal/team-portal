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
                        SELECT ra as rugnummer FROM dwf_punten P inner join dwf_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT rv as rugnummer FROM dwf_punten P inner join dwf_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT mv as rugnummer FROM dwf_punten P inner join dwf_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT lv as rugnummer FROM dwf_punten P inner join dwf_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT la as rugnummer FROM dwf_punten P inner join dwf_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                        UNION ALL
                        SELECT ma as rugnummer FROM dwf_punten P inner join dwf_wedstrijden W on P.matchId = W.id where W.team1 = :team || W.team2 = :team
                    ) T1
                    GROUP BY rugnummer ORDER BY gespeeldePunten DESC
                  ) T2
                  LEFT JOIN (
                    SELECT C.id, U.name as naam, C.cb_rugnummer as rugnummer
                    FROM j3_users U
                    INNER JOIN j3_user_usergroup_map M ON U.id = M.user_id
                    INNER JOIN j3_usergroups G on M.group_id = G.id
                    INNER JOIN j3_comprofiler C ON U.id = C.user_id
                    where G.title = :skcTeam
                  ) R ON T2.rugnummer = R.rugnummer";
        $params = [
            new Param(":team", $team, PDO::PARAM_STR),
            new Param(":skcTeam", $skcTeam, PDO::PARAM_STR),
        ];
        return $this->database->Execute($query, $params);
    }
}
