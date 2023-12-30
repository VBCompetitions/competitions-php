<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;

use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\Group;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(Stage::class)]
#[CoversClass(Group::class)]
final class StageTest extends TestCase {
  public function testStageGetters() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'stage'))), 'competition.json');
        $stage = $competition->getStageById('L');
        $groups = $stage->getGroups();

        $this->assertEquals('Recreational League', $groups[0]->getName());
        $this->assertEquals('Some stage notes', $stage->getNotes());
        $this->assertEquals('This is a description', $stage->getDescription()[0]);
    }

    public function testStageMatchesWithAllOptionalFields() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'stage-matches-with-everything.json');
        $stage = $competition->getStageById('L');

        $this->assertEquals('Matches with Everything', $stage->getCompetition()->getName());
        $this->assertEquals('League', $stage->getName());
        $this->assertEquals('These are notes on the stage', $stage->getNotes());
        $this->assertIsArray($stage->getDescription());
        $this->assertCount(2, $stage->getDescription());
        $this->assertEquals('This is a description about the stage', $stage->getDescription()[0]);
        $this->assertEquals('This is some more words', $stage->getDescription()[1]);
        $this->assertTrue($stage->matchesHaveCourts());
        $this->assertTrue($stage->matchesHaveDates());
        $this->assertTrue($stage->matchesHaveDurations());
        $this->assertTrue($stage->matchesHaveMVPs());
        $this->assertTrue($stage->matchesHaveManagers());
        $this->assertTrue($stage->matchesHaveNotes());
        $this->assertTrue($stage->matchesHaveOfficials());
        $this->assertTrue($stage->matchesHaveStarts());
        $this->assertTrue($stage->matchesHaveVenues());
        $this->assertTrue($stage->matchesHaveWarmups());
    }

    public function testStageMatchesWithNoOptionalFields() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'stage-matches-with-nothing.json');
        $stage = $competition->getStageById('L');

        $this->assertNull($stage->getName());
        $this->assertNull($stage->getNotes());
        $this->assertNull($stage->getDescription());
        $this->assertFalse($stage->matchesHaveCourts());
        $this->assertFalse($stage->matchesHaveDates());
        $this->assertFalse($stage->matchesHaveDurations());
        $this->assertFalse($stage->matchesHaveMVPs());
        $this->assertFalse($stage->matchesHaveManagers());
        $this->assertFalse($stage->matchesHaveNotes());
        $this->assertFalse($stage->matchesHaveOfficials());
        $this->assertFalse($stage->matchesHaveStarts());
        $this->assertFalse($stage->matchesHaveVenues());
        $this->assertFalse($stage->matchesHaveWarmups());
    }

    public function testStageBlockTeamsInTwoGroups() : void
    {
        $this->expectExceptionMessage('Groups in the same stage cannot contain the same team. Groups {L:L1} and {L:L2} both contain the following team IDs: "TM2", "TM4"');
        new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'stage-teams-in-multiple-groups.json');
    }

    public function testStageGetTeamIDsFixed() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $incomplete_pool_team_ids = $incomplete_competition->getStageById('P')->getTeamIDs();
        $incomplete_division_team_ids = $incomplete_competition->getStageById('D')->getTeamIDs();

        $this->assertCount(8, $incomplete_pool_team_ids);
        $this->assertCount(0, $incomplete_division_team_ids);

        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $complete_pool_team_ids = $complete_competition->getStageById('P')->getTeamIDs();
        $complete_division_team_ids = $complete_competition->getStageById('D')->getTeamIDs();

        $this->assertCount(8, $complete_pool_team_ids);
        $this->assertCount(0, $complete_division_team_ids);
    }

    public function testStageGetTeamIDsKnown() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $incomplete_pool_team_ids = $incomplete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_KNOWN);
        $incomplete_division_team_ids = $incomplete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_KNOWN);

        $this->assertCount(8, $incomplete_pool_team_ids);
        $this->assertCount(0, $incomplete_division_team_ids);

        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $complete_pool_team_ids = $complete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_KNOWN);
        $complete_division_team_ids = $complete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_KNOWN);

        $this->assertCount(8, $complete_pool_team_ids);
        $this->assertCount(18, $complete_division_team_ids);
    }

    public function testStageGetTeamIDsMaybe() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $incomplete_pool_team_ids = $incomplete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_MAYBE);
        $incomplete_division_team_ids = $incomplete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_MAYBE);

        $this->assertCount(0, $incomplete_pool_team_ids);
        $this->assertCount(8, $incomplete_division_team_ids);

        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $complete_pool_team_ids = $complete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_MAYBE);
        $complete_division_team_ids = $complete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_MAYBE);

        $this->assertCount(0, $complete_pool_team_ids);
        $this->assertCount(0, $complete_division_team_ids);
    }

    public function testStageGetTeamIDsAll() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $incomplete_pool_team_ids = $incomplete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_ALL);
        $incomplete_division_team_ids = $incomplete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_ALL);

        $this->assertCount(8, $incomplete_pool_team_ids);
        $this->assertCount(18, $incomplete_division_team_ids);

        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $complete_pool_team_ids = $complete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_ALL);
        $complete_division_team_ids = $complete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_ALL);

        $this->assertCount(8, $complete_pool_team_ids);
        $this->assertCount(18, $complete_division_team_ids);
    }

    public function testStageGetTeamIDsPlaying() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $incomplete_pool_team_ids = $incomplete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_PLAYING);
        $incomplete_division_team_ids = $incomplete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_PLAYING);

        $this->assertCount(8, $incomplete_pool_team_ids);
        $this->assertCount(16, $incomplete_division_team_ids);

        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $complete_pool_team_ids = $complete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_PLAYING);
        $complete_division_team_ids = $complete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_PLAYING);

        $this->assertCount(8, $complete_pool_team_ids);
        $this->assertCount(16, $complete_division_team_ids);
    }

    public function testStageGetTeamIDsOfficiating() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $incomplete_pool_team_ids = $incomplete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_OFFICIATING);
        $incomplete_division_team_ids = $incomplete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_OFFICIATING);

        $this->assertCount(8, $incomplete_pool_team_ids);
        $this->assertCount(8, $incomplete_division_team_ids);

        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $complete_pool_team_ids = $complete_competition->getStageById('P')->getTeamIDs(VBC_TEAMS_OFFICIATING);
        $complete_division_team_ids = $complete_competition->getStageById('D')->getTeamIDs(VBC_TEAMS_OFFICIATING);

        $this->assertCount(8, $complete_pool_team_ids);
        $this->assertCount(8, $complete_division_team_ids);
    }

    public function testStageTeamHasMatches() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');

        $this->assertTrue($incomplete_competition->getStageById('P')->teamHasMatches('TM1'));
        $this->assertTrue($complete_competition->getStageById('P')->teamHasMatches('TM1'));

        $this->assertFalse($incomplete_competition->getStageById('D')->teamHasMatches('TM1'));
        $this->assertTrue($complete_competition->getStageById('D')->teamHasMatches('TM1'));
    }

    public function testStageTeamHasOfficiating() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');

        $this->assertTrue($incomplete_competition->getStageById('P')->teamHasOfficiating('TM1'));
        $this->assertTrue($complete_competition->getStageById('P')->teamHasOfficiating('TM1'));

        $this->assertFalse($incomplete_competition->getStageById('D')->teamHasOfficiating('TM1'));
        $this->assertTrue($complete_competition->getStageById('D')->teamHasOfficiating('TM1'));
    }

    public function testStageTeamMayHaveMatches() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $reffing_ref_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete-reffing-reference.json');

        $this->assertFalse($incomplete_competition->getStageById('P')->teamMayHaveMatches('TM1'));
        $this->assertFalse($complete_competition->getStageById('P')->teamMayHaveMatches('TM1'));
        $this->assertFalse($reffing_ref_competition->getStageById('P')->teamMayHaveMatches('TM1'));

        $this->assertTrue($incomplete_competition->getStageById('D')->teamMayHaveMatches('TM1'));
        $this->assertFalse($complete_competition->getStageById('D')->teamMayHaveMatches('TM1'));
        $this->assertTrue($reffing_ref_competition->getStageById('D')->teamMayHaveMatches('TM1'));

    }

    public function testStageGetMatchesAllInStage() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');

        $this->assertCount(12, $incomplete_competition->getStageById('P')->getMatches());
        // Check cached answer
        $this->assertCount(12, $incomplete_competition->getStageById('P')->getMatches());
        $this->assertCount(8, $incomplete_competition->getStageById('D')->getMatches());
    }

    public function testStageGetMatchesAllInGroup() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');

        $this->assertCount(6, $incomplete_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_ALL_IN_GROUP));
        $this->assertCount(0, $incomplete_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_ALL_IN_GROUP));

        $this->assertCount(6, $complete_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_ALL_IN_GROUP));
        $this->assertCount(4, $complete_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_ALL_IN_GROUP));
    }

    public function testStageGetMatchesPlaying() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');

        $this->assertCount(3, $incomplete_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_PLAYING));
        $this->assertCount(0, $incomplete_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_PLAYING));

        $this->assertCount(3, $complete_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_PLAYING));
        $this->assertCount(2, $complete_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_PLAYING));
    }

    public function testStageGetMatchesOfficiating() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $cross_group_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-cross-group-reffing.json');

        $this->assertCount(1, $incomplete_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_OFFICIATING));
        $this->assertCount(0, $incomplete_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_OFFICIATING));

        $this->assertCount(1, $complete_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_OFFICIATING));
        $this->assertCount(1, $complete_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_OFFICIATING));

        $this->assertCount(2, $cross_group_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_OFFICIATING));
        $this->assertCount(0, $cross_group_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_OFFICIATING));
    }

    public function testStageGetMatchesPlayingAndOfficiating() : void
    {
        $incomplete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-incomplete.json');
        $complete_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-complete.json');
        $cross_group_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'pools-knockout-cross-group-reffing.json');

        $this->assertCount(4, $incomplete_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING));
        $this->assertCount(0, $incomplete_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING));

        $this->assertCount(4, $complete_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING));
        $this->assertCount(3, $complete_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING));

        $this->assertCount(5, $cross_group_competition->getStageById('P')->getMatches('TM1', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING));
        $this->assertCount(0, $cross_group_competition->getStageById('D')->getMatches('TM1', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING));
    }

    public function testStageGetMatchDates() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'group-with-dates.json');
        $stageA = $competition->getStageById('HVAGP');
        $stageB = $competition->getStageById('HVAGP2');
        $datesA = $stageA->getMatchDates();
        $datesB = $stageB->getMatchDates();

        $this->assertCount(7, $datesA);
        $this->assertEquals(['2023-10-22', '2023-11-26', '2024-01-21', '2024-02-11', '2024-02-25', '2024-03-24', '2024-04-21'], $datesA);
        $this->assertCount(3, $datesB);
        $this->assertEquals(['2023-11-26', '2024-02-25', '2024-04-21'], $datesB);
    }

    public function testStageGetMatchDatesForTeam() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'group-with-dates.json');
        $stageA = $competition->getStageById('HVAGP');
        $stageB = $competition->getStageById('HVAGP2');
        $datesTMKA = $stageA->getMatchDates('TMK');
        $datesTMKB = $stageB->getMatchDates('TMK');

        $this->assertCount(2, $datesTMKA);
        $this->assertEquals(['2023-11-26', '2024-02-25'], $datesTMKA);
        $this->assertCount(2, $datesTMKB);
        $this->assertEquals(['2023-11-26', '2024-02-25'], $datesTMKB);
    }

    public function testStageGetMatchDatesForTeamPlaying() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'group-with-dates.json');
        $stageA = $competition->getStageById('HVAGP');
        $stageB = $competition->getStageById('HVAGP2');
        $datesTMKA = $stageA->getMatchDates('TMK', VBC_MATCH_PLAYING);
        $datesTMKB = $stageB->getMatchDates('TMK', VBC_MATCH_PLAYING);

        $this->assertCount(2, $datesTMKA);
        $this->assertEquals(['2023-11-26', '2024-02-25'], $datesTMKA);
        $this->assertCount(2, $datesTMKB);
        $this->assertEquals(['2023-11-26', '2024-02-25'], $datesTMKB);
    }

    public function testStageGetMatchDatesForTeamOfficiating() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'group-with-dates.json');
        $stageA = $competition->getStageById('HVAGP');
        $stageB = $competition->getStageById('HVAGP2');
        $datesTMMA = $stageA->getMatchDates('TMM', VBC_MATCH_OFFICIATING);
        $datesTMMB = $stageB->getMatchDates('TMM', VBC_MATCH_OFFICIATING);

        $this->assertCount(3, $datesTMMA);
        $this->assertEquals(['2023-11-26', '2024-02-25', '2024-04-21'], $datesTMMA);
        $this->assertCount(2, $datesTMMB);
        $this->assertEquals(['2023-11-26', '2024-02-25'], $datesTMMB);
    }

    public function testStageGetMatchesOnDate() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'group-with-dates.json');
        $stageA = $competition->getStageById('HVAGP');
        $matchesA = $stageA->getMatchesOnDate('2023-10-22');
        $matchesB = $stageA->getMatchesOnDate('2023-11-26');

        $this->assertCount(13, $matchesA);
        $this->assertEquals('GP1AM5', $matchesA[5]->getID());
        $this->assertCount(12, $matchesB);
        $this->assertEquals('GP1BM6', $matchesB[5]->getID());
    }

    public function testStageGetMatchesOnDateForTeam() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'group-with-dates.json');
        $stageA = $competition->getStageById('HVAGP');

        $matchesTMC = $stageA->getMatchesOnDate('2024-01-21', 'TMC');
        $this->assertCount(12, $matchesTMC);
        $this->assertEquals('GP2AM9', $matchesTMC[8]->getID());
    }

    public function testStageGetMatchesOnDateForTeamPlaying() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'group-with-dates.json');
        $stageA = $competition->getStageById('HVAGP');
        $matchesTMC = $stageA->getMatchesOnDate('2023-10-22', 'TMC', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);
        $this->assertCount(1, $matchesTMC);
        $this->assertInstanceOf('VBCompetitions\Competitions\GroupBreak', $matchesTMC[0]);

        $stageB = $competition->getStageById('HVAGP2');
        $matchesTMK = $stageB->getMatchesOnDate('2023-11-26', 'TMK', VBC_MATCH_PLAYING);
        $this->assertCount(3, $matchesTMK);
        $this->assertEquals('GP1CM7', $matchesTMK[1]->getID());
    }

    public function testStageGetMatchesOnDateForTeamOfficiating() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'stage'))),'group-with-dates.json');
        $stageB = $competition->getStageById('HVAGP2');
        $matchesTMM = $stageB->getMatchesOnDate('2024-02-25', 'TMM', VBC_MATCH_OFFICIATING);
        $this->assertCount(1, $matchesTMM);
        $this->assertEquals('GP2CM9', $matchesTMM[0]->getID());
    }
}
