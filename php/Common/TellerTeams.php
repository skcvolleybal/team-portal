<?php

namespace TeamPortal\Common;

use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Team;

/**
 * Maps the flat rows of the teller query to Team objects.
 *
 * The rows are grouped per team id, NOT per team name and NOT by comparing
 * consecutive rows: the query is not guaranteed to return the rows of a team
 * next to each other, so grouping on consecutive rows would split one team
 * over several Team objects (and, when two teams share a name, could merge
 * them).
 */
class TellerTeams
{
    /**
     * @param array $rows Rows with the properties id, naam, email, geteld, teamId and teamnaam.
     * @return Team[]
     */
    public static function FromRows(array $rows): array
    {
        $result = [];
        $teamsById = [];

        foreach ($rows as $row) {
            $teamId = (int) $row->teamId;

            if (!array_key_exists($teamId, $teamsById)) {
                $team = new Team($row->teamnaam, $teamId);
                $teamsById[$teamId] = $team;
                $result[] = $team;
            }

            $persoon = new Persoon($row->id, $row->naam, $row->email);
            $persoon->aantalKeerGeteld = $row->geteld;
            $persoon->spelertijd = "abc";
            $teamsById[$teamId]->teamgenoten[] = $persoon;
        }

        return $result;
    }
}
