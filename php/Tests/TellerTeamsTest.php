<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use PHPUnit\Framework\TestCase;
use TeamPortal\Common\TellerTeams;

class TellerTeamsTest extends TestCase
{
    private function row(
        int $id,
        string $naam,
        int $teamId,
        string $teamnaam,
        int $geteld = 0,
    ): object {
        $row = new \stdClass();
        $row->id = $id;
        $row->naam = $naam;
        $row->email = strtolower($naam) . "@example.com";
        $row->geteld = $geteld;
        $row->teamId = $teamId;
        $row->teamnaam = $teamnaam;

        return $row;
    }

    /**
     * The teller query has no guaranteed row order (its GROUP BY starts with
     * the user id), so the rows of one team are scattered over the result.
     * Every team must still end up as exactly one Team object.
     */
    public function testTeamsAreNotSplitWhenRowsAreInterleaved()
    {
        $rows = [
            $this->row(1, "Jan", 37, "Heren 1"),
            $this->row(2, "Piet", 38, "Heren 2"),
            $this->row(3, "Klaas", 37, "Heren 1"),
            $this->row(4, "Bart", 38, "Heren 2"),
            $this->row(5, "Jan", 46, "Dames 1"),
        ];

        $teams = TellerTeams::FromRows($rows);

        $this->assertCount(3, $teams);

        $membersPerTeam = [];
        foreach ($teams as $team) {
            $membersPerTeam[$team->id] = count($team->teamgenoten);
        }

        ksort($membersPerTeam);
        $this->assertSame(
            [37 => 2, 38 => 2, 46 => 1],
            $membersPerTeam,
            "Elk team hoort precies één groep te krijgen, met al zijn leden.",
        );
    }

    public function testEveryMemberIsPlacedExactlyOnce()
    {
        $rows = [
            $this->row(1, "Jan", 37, "Heren 1"),
            $this->row(2, "Piet", 38, "Heren 2"),
            $this->row(3, "Klaas", 37, "Heren 1"),
            $this->row(4, "Bart", 38, "Heren 2"),
        ];

        $teams = TellerTeams::FromRows($rows);

        $memberIds = [];
        foreach ($teams as $team) {
            foreach ($team->teamgenoten as $teamgenoot) {
                $memberIds[] = $teamgenoot->id;
            }
        }

        sort($memberIds);
        $this->assertSame([1, 2, 3, 4], $memberIds);
        $this->assertSame(
            count($memberIds),
            count(array_unique($memberIds)),
            "Een lid mag maar in één team staan.",
        );
    }

    public function testMemberIsPlacedUnderItsOwnTeam()
    {
        $rows = [
            $this->row(1, "Jan", 37, "Heren 1"),
            $this->row(2, "Piet", 38, "Heren 2"),
            $this->row(3, "Klaas", 37, "Heren 1"),
        ];

        $teams = TellerTeams::FromRows($rows);

        $teamPerMember = [];
        foreach ($teams as $team) {
            foreach ($team->teamgenoten as $teamgenoot) {
                $teamPerMember[$teamgenoot->id] = $team->id;
            }
        }

        ksort($teamPerMember);
        $this->assertSame([1 => 37, 2 => 38, 3 => 37], $teamPerMember);
    }

    public function testEmptyResultGivesNoTeams()
    {
        $this->assertSame([], TellerTeams::FromRows([]));
    }
}
