<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Group;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\Knockout;
use VBCompetitions\Competitions\League;
use VBCompetitions\Competitions\MatchType;
use VBCompetitions\Competitions\GroupType;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(Stage::class)]
#[CoversClass(League::class)]
#[CoversClass(Group::class)]
#[CoversClass(GroupMatch::class)]
final class GroupTest extends TestCase {
    public function testGroupGetMatch() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $this->assertEquals('LG', $group->getID());
        $this->assertEquals('TM2', $group->getMatch('LG1')->getHomeTeam()->getID());
    }

    public function testGroupGetMatchSkipBreak() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $this->assertEquals('TM1', $group->getMatch('LG6')->getHomeTeam()->getID());
    }

    public function testGroupGetMatchOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $this->expectExceptionMessage('Match with ID FOO not found');
        $group->getMatch('FOO');
    }

    public function testGroupGetTeamIDsSimple() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $expected_teams_sorted = ['TM1', 'TM2', 'TM3', 'TM4'];
        $expected_teams_by_name = ['TM2', 'TM1', 'TM4', 'TM3'];

        $found_teams = $group->getTeamIDs();
        $this->assertEquals($expected_teams_by_name, $found_teams);

        $found_teams = $group->getTeamIDs(VBC_TEAMS_FIXED_ID);
        $this->assertEquals($expected_teams_by_name, $found_teams);

        $found_teams = $group->getTeamIDs(VBC_TEAMS_KNOWN);
        $this->assertEquals($expected_teams_by_name, $found_teams);

        $found_teams = $group->getTeamIDs(VBC_TEAMS_MAYBE);
        sort($found_teams, SORT_STRING);
        $this->assertEquals([], $found_teams);

        $found_teams = $group->getTeamIDs(VBC_TEAMS_ALL);
        sort($found_teams, SORT_STRING);
        $this->assertEquals($expected_teams_sorted, $found_teams);
    }

    public function testGroupGetTeamIDsMaybes() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-maybes.json');

        $fina_teams = $competition->getStage('S4')->getGroup('FINA')->getTeamIDs(VBC_TEAMS_MAYBE);
        $this->assertCount(6, $fina_teams);
        $this->assertContains('TM1', $fina_teams);
        $this->assertContains('TM2', $fina_teams);
        $this->assertContains('TM3', $fina_teams);
        $this->assertContains('TM4', $fina_teams);
        $this->assertContains('TM5', $fina_teams);
        $this->assertContains('TM6', $fina_teams);

        $finb_teams = $competition->getStage('S4')->getGroup('FINB')->getTeamIDs(VBC_TEAMS_MAYBE);
        $this->assertCount(7, $finb_teams);
        $this->assertContains('TM3', $finb_teams);
        $this->assertContains('TM4', $finb_teams);
        $this->assertContains('TM5', $finb_teams);
        $this->assertContains('TM6', $finb_teams);
        $this->assertContains('TM7', $finb_teams);
        $this->assertContains('TM8', $finb_teams);
        $this->assertContains('TM9', $finb_teams);

        $sfb_teams = $competition->getStage('S3')->getGroup('SFB')->getTeamIDs(VBC_TEAMS_MAYBE);
        $this->assertCount(2, $sfb_teams);
        $this->assertContains('TM5', $sfb_teams);
        $this->assertContains('TM6', $sfb_teams);
    }

    public function testGroupMatchesWithMatchingIDs() : void
    {
        $this->expectExceptionMessage('Group {L:LG}: matches with duplicate IDs {LG1} not allowed');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'group-same-match-id.json');
    }

    public function testGroupContinuousMatchesWithoutComplete() : void
    {
        $this->expectExceptionMessage('Group {L:LG}, match ID {LG3}, missing field "complete"');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'group-continuous-missing-complete.json');
    }

    public function testGroupGroupMatchesWithAllOptionalFields() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-matches-with-everything.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $this->assertEquals('Test League Table by PD', $group->getCompetition()->getName());
        $this->assertEquals('League', $group->getStage()->getName());
        $this->assertEquals('League 1', $group->getName());
        $this->assertEquals('These are notes on the group', $group->getNotes());
        $this->assertIsArray($group->getDescription());
        $this->assertCount(2, $group->getDescription());
        $this->assertEquals('This is a description about the group', $group->getDescription()[0]);
        $this->assertEquals('This is some more words', $group->getDescription()[1]);
        $this->assertTrue($group->matchesHaveCourts());
        $this->assertTrue($group->matchesHaveDates());
        $this->assertTrue($group->matchesHaveDurations());
        $this->assertTrue($group->matchesHaveMVPs());
        $this->assertTrue($group->matchesHaveManagers());
        $this->assertTrue($group->matchesHaveNotes());
        $this->assertTrue($group->matchesHaveOfficials());
        $this->assertTrue($group->matchesHaveStarts());
        $this->assertTrue($group->matchesHaveVenues());
        $this->assertTrue($group->matchesHaveWarmups());

        $this->assertTrue($group->allTeamsKnown());
    }

    public function testGroupGroupMatchesWithNoOptionalFields() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-matches-with-nothing.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $this->assertNull($group->getName());
        $this->assertNull($group->getNotes());
        $this->assertNull($group->getDescription());
        $this->assertFalse($group->matchesHaveCourts());
        $this->assertFalse($group->matchesHaveDates());
        $this->assertFalse($group->matchesHaveDurations());
        $this->assertFalse($group->matchesHaveMVPs());
        $this->assertFalse($group->matchesHaveManagers());
        $this->assertFalse($group->matchesHaveNotes());
        $this->assertFalse($group->matchesHaveOfficials());
        $this->assertFalse($group->matchesHaveStarts());
        $this->assertFalse($group->matchesHaveVenues());
        $this->assertFalse($group->matchesHaveWarmups());

        $this->assertTrue($group->allTeamsKnown());
    }

    public function testGroupGetMatchesAllInGroup() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $matches = $group->getMatches('TM1', VBC_MATCH_ALL_IN_GROUP);
        $this->assertCount(7, $matches);
        $matchThree = $matches[2];
        $this->assertInstanceOf(GroupMatch::class, $matchThree);
        $this->assertEquals('TM2', $matchThree->getHomeTeam()->getID());
        $this->assertEquals('TM3', $matchThree->getAwayTeam()->getID());
        if ($matchThree instanceof GroupMatch) {
            $this->assertEquals('1', $matchThree->getCourt());
        }
    }

    public function testGroupGetMatchesUnknownTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $matches = $group->getMatches(CompetitionTeam::UNKNOWN_TEAM_ID);
        $this->assertCount(7, $matches);
        $matchThree = $matches[2];
        $this->assertInstanceOf(GroupMatch::class, $matchThree);
        $this->assertEquals('TM2', $matchThree->getHomeTeam()->getID());
        $this->assertEquals('TM3', $matchThree->getAwayTeam()->getID());
        if ($matchThree instanceof GroupMatch) {
            $this->assertEquals('1', $matchThree->getCourt());
        }
    }

    public function testGroupGetMatchesKnownTeamPlaying() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $matches = $group->getMatches('TM2', VBC_MATCH_PLAYING);
        $this->assertCount(3, $matches);
        $matchThree = $matches[2];
        $this->assertInstanceOf(GroupMatch::class, $matchThree);
        $this->assertEquals('TM1', $matchThree->getHomeTeam()->getID());
        $this->assertEquals('TM2', $matchThree->getAwayTeam()->getID());
    }

    public function testGroupGetMatchesKnownTeamOfficiating() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $matches = $group->getMatches('TM2', VBC_MATCH_OFFICIATING);
        $this->assertCount(2, $matches);
        $matchTwo = $matches[1];
        $this->assertInstanceOf(GroupMatch::class, $matchTwo);
        $this->assertEquals('TM3', $matchTwo->getHomeTeam()->getID());
        $this->assertEquals('TM4', $matchTwo->getAwayTeam()->getID());
    }

    public function testGroupGetMatchesKnownTeamAll() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $matches = $group->getMatches('TM2', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);
        $this->assertCount(5, $matches);
        $matchFour = $matches[3];
        $this->assertInstanceOf(GroupMatch::class, $matchFour);
        $this->assertEquals('TM3', $matchFour->getHomeTeam()->getID());
        $this->assertEquals('TM4', $matchFour->getAwayTeam()->getID());
    }

    public function testGroupGetMatchesWithReferencesKnownTeamPlaying() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'complete-group-knockout.json');
        $group = $competition->getStage('C')->getGroup('CP');

        $matches = $group->getMatches('TM2', VBC_MATCH_PLAYING);
        $this->assertCount(3, $matches);
        $matchTwo = $matches[1];
        $this->assertInstanceOf(GroupMatch::class, $matchTwo);
        $this->assertEquals('TM2', $competition->getTeam($matchTwo->getHomeTeam()->getID())->getID());
        $this->assertEquals('TM6', $competition->getTeam($matchTwo->getAwayTeam()->getID())->getID());
        $matchThree = $matches[2];
        $this->assertInstanceOf(GroupMatch::class, $matchTwo);
        $this->assertEquals('TM2', $competition->getTeam($matchThree->getHomeTeam()->getID())->getID());
        $this->assertEquals('TM3', $competition->getTeam($matchThree->getAwayTeam()->getID())->getID());
    }

    public function testGroupGetMatchesWithReferencesKnownTeamOfficiating() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'complete-group-knockout.json');
        $group = $competition->getStage('C')->getGroup('CP');

        $matches = $group->getMatches('TM2', VBC_MATCH_OFFICIATING);
        $this->assertCount(1, $matches);
        $matchOne = $matches[0];
        $this->assertInstanceOf(GroupMatch::class, $matchOne);
        $this->assertEquals('TM2', $competition->getTeam($matchOne->getOfficials()->getTeamID())->getID());
    }

    public function testGroupGetMatchesWithReferencesKnownTeamAll() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'complete-group-knockout.json');
        $group = $competition->getStage('C')->getGroup('CP');

        $matches = $group->getMatches('TM2', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);
        $this->assertCount(4, $matches);
        $matchFour = $matches[3];
        $this->assertInstanceOf(GroupMatch::class, $matchFour);
        $this->assertEquals('TM6', $competition->getTeam($matchFour->getHomeTeam()->getID())->getID());
        $this->assertEquals('TM7', $competition->getTeam($matchFour->getAwayTeam()->getID())->getID());
    }

    public function testGroupGetMatchDatesFromGroup() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-with-dates.json');
        $groupA = $competition->getStage('HVAGP')->getGroup('A');
        $groupB = $competition->getStage('HVAGP')->getGroup('B');
        $datesA = $groupA->getMatchDates();
        $datesB = $groupB->getMatchDates();

        $this->assertCount(4, $datesA);
        $this->assertEquals(['2023-10-22', '2024-01-21', '2024-02-11', '2024-03-24'], $datesA);
        $this->assertCount(3, $datesB);
        $this->assertEquals(['2023-11-26', '2024-02-25', '2024-04-21'], $datesB);
    }

    public function testGroupGetMatchDatesForTeamFromGroup() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-with-dates.json');
        $groupA = $competition->getStage('HVAGP')->getGroup('A');
        $datesTMC = $groupA->getMatchDates('TMC');

        $this->assertCount(3, $datesTMC);
        $this->assertEquals(['2024-01-21', '2024-02-11', '2024-03-24'], $datesTMC);
    }

    public function testGroupGetMatchDatesForTeamPlaying() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-with-dates.json');
        $groupC = $competition->getStage('HVAGP2')->getGroup('C');
        $datesTMK = $groupC->getMatchDates('TMK', VBC_MATCH_PLAYING);

        $this->assertCount(2, $datesTMK);
        $this->assertEquals(['2023-11-26', '2024-02-25'], $datesTMK);
    }

    public function testGroupGetMatchDatesForTeamOfficiating() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-with-dates.json');
        $groupC = $competition->getStage('HVAGP2')->getGroup('C');
        $datesTMM = $groupC->getMatchDates('TMM', VBC_MATCH_OFFICIATING);

        $this->assertCount(2, $datesTMM);
        $this->assertEquals(['2023-11-26', '2024-02-25'], $datesTMM);
    }

    public function testGroupGetMatchesOnDate() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-with-dates.json');
        $groupA = $competition->getStage('HVAGP')->getGroup('A');
        $matches = $groupA->getMatchesOnDate('2023-10-22');

        $this->assertCount(13, $matches);
        $this->assertEquals('GP1AM5', $matches[5]->getID());
    }

    public function testGroupGetMatchesOnDateForTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-with-dates.json');
        $groupA = $competition->getStage('HVAGP')->getGroup('A');
        $matchesTMC = $groupA->getMatchesOnDate('2024-01-21', 'TMC');
        $this->assertCount(12, $matchesTMC);
        $this->assertEquals('GP2AM9', $matchesTMC[8]->getID());
    }

    public function testGroupGetMatchesOnDateForTeamPlaying() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-with-dates.json');
        $groupA = $competition->getStage('HVAGP')->getGroup('A');
        $matchesTMC = $groupA->getMatchesOnDate('2023-10-22', 'TMC', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);
        $this->assertCount(1, $matchesTMC);
        $this->assertInstanceOf('VBCompetitions\Competitions\GroupBreak', $matchesTMC[0]);

        $groupC = $competition->getStage('HVAGP2')->getGroup('C');
        $matchesTMK = $groupC->getMatchesOnDate('2023-11-26', 'TMK', VBC_MATCH_PLAYING);
        $this->assertCount(3, $matchesTMK);
        $this->assertEquals('GP1CM7', $matchesTMK[1]->getID());
    }

    public function testGroupGetMatchesOnDateForTeamOfficiating() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'group-with-dates.json');
        $groupC = $competition->getStage('HVAGP2')->getGroup('C');
        $matchesTMM = $groupC->getMatchesOnDate('2024-02-25', 'TMM', VBC_MATCH_OFFICIATING);
        $this->assertCount(1, $matchesTMM);
        $this->assertEquals('GP2CM9', $matchesTMM[0]->getID());
    }

    public function testGroupAllTeamsKnown() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'incomplete-group-multi-stage.json');
        $poolA = $competition->getStage('P')->getGroup('A');
        $poolB = $competition->getStage('P')->getGroup('B');
        $finals = $competition->getStage('F')->getGroup('F');

        $this->assertTrue($poolA->allTeamsKnown());
        $this->assertTrue($poolB->allTeamsKnown());
        $this->assertFalse($finals->allTeamsKnown());
    }

    public function testGroupTeamHasMatches() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'incomplete-group-multi-stage.json');
        $poolA = $competition->getStage('P')->getGroup('A');
        $finals = $competition->getStage('F')->getGroup('F');

        $this->assertTrue($poolA->teamHasMatches('TM1'));
        $this->assertFalse($poolA->teamHasMatches('TM5'));
        $this->assertTrue($finals->teamHasMatches('TM2'));
        $this->assertTrue($finals->teamHasMatches('TM3'));
        $this->assertFalse($finals->teamHasMatches('TM6'));
    }

    public function testGroupTeamHasOfficiating() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'incomplete-group-multi-stage.json');
        $poolA = $competition->getStage('P')->getGroup('A');
        $finals = $competition->getStage('F')->getGroup('F');

        $this->assertTrue($poolA->teamHasOfficiating('TM1'));
        $this->assertFalse($poolA->teamHasOfficiating('TM5'));
        $this->assertTrue($finals->teamHasOfficiating('TM3'));
        $this->assertFalse($finals->teamHasOfficiating('TM2'));
    }

    public function testGroupTeamMayHaveMatches() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__,'groups'))),'incomplete-group-multi-stage.json');
        $poolA = $competition->getStage('P')->getGroup('A');
        $finals = $competition->getStage('F')->getGroup('F');

        $this->assertFalse($poolA->teamMayHaveMatches('TM1'));
        $this->assertTrue($finals->teamMayHaveMatches('TM2'));
        $this->assertTrue($finals->teamMayHaveMatches('TM6'));
        $this->assertTrue($finals->teamMayHaveMatches('TM7'));
        $this->assertFalse($finals->teamMayHaveMatches('unknown-team-reference'));
    }

    public function testGroupSettersGetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'group-matches-with-everything.json');
        $group = $competition->getStage('L')->getGroup('LG');

        $this->assertEquals('League 1', $group->getName());
        $group->setName('League One');
        $this->assertEquals('League One', $group->getName());

        $this->assertEquals('These are notes on the group', $group->getNotes());
        $group->setNotes('These are notes on the best group');
        $this->assertEquals('These are notes on the best group', $group->getNotes());

        $this->assertEquals('This is a description about the group', $group->getDescription()[0]);
        $group->setDescription(['This is line one of the description', 'This is some more words']);
        $this->assertEquals('This is line one of the description', $group->getDescription()[0]);

        $this->assertFalse($group->getDrawsAllowed());
        $group->setDrawsAllowed(true);
        $this->assertTrue($group->getDrawsAllowed());

        $this->assertEquals(GroupType::LEAGUE, $group->getType());
    }

    public function testGroupGetTeamByIDBlocksBadType() : void
    {
        $competition = new Competition('test');
        $stage = new Stage($competition, 'S');
        $group = new Knockout($stage, 'Finals', MatchType::CONTINUOUS);

        try {
            $group->getTeam('league', '1');
            $this->fail('Getting a team by league position in a non-league group should fail');
        } catch (Exception $e) {
            $this->assertEquals('Invalid type "league" in team reference.  Cannot get league position from a non-league group', $e->getMessage());
        }
    }
}
