<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Timer\Duration;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\GroupBreak;
use VBCompetitions\Competitions\IfUnknown;
use VBCompetitions\Competitions\IfUnknownBreak;
use VBCompetitions\Competitions\IfUnknownMatch;
use VBCompetitions\Competitions\MatchType;

#[CoversClass(IfUnknown::class)]
#[CoversClass(IfUnknownBreak::class)]
#[CoversClass(IfUnknownMatch::class)]
final class IfUnknownTest extends TestCase {
    public function testIfUnknownLoad() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ifunknown'))), 'incomplete-group-multi-stage.json');
        $if_unknown = $competition->getStageById('F')->getIfUnknown();

        $this->assertEquals(MatchType::CONTINUOUS, $if_unknown->getMatchType());
        $this->assertEquals('unknown', $if_unknown->getID());
        $this->assertEquals('Test Knockout', $if_unknown->getCompetition()->getName());
        $this->assertEqualsCanonicalizing([], $if_unknown->getTeamIDs());
        $this->assertTrue($if_unknown->matchesHaveCourts());
        $this->assertTrue($if_unknown->matchesHaveDates());
        $this->assertTrue($if_unknown->matchesHaveDurations());
        $this->assertTrue($if_unknown->matchesHaveManagers());
        $this->assertTrue($if_unknown->matchesHaveMVPs());
        $this->assertTrue($if_unknown->matchesHaveNotes());
        $this->assertTrue($if_unknown->matchesHaveOfficials());
        $this->assertTrue($if_unknown->matchesHaveStarts());
        $this->assertTrue($if_unknown->matchesHaveVenues());
        $this->assertTrue($if_unknown->matchesHaveWarmups());

        $second_match = $if_unknown->getMatches()[1];
        $this->assertEquals('SF2', $second_match->getID());
        $this->assertInstanceOf('VBCompetitions\Competitions\IfUnknown', $second_match->getGroup());
        $this->assertFalse($second_match->isComplete());
        $this->assertFalse($second_match->isDraw());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $second_match->getWinnerTeamId());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $second_match->getLoserTeamId());
        $this->assertEquals(0, $second_match->getHomeTeamSets());
        $this->assertEquals(0, $second_match->getAwayTeamSets());
        $this->assertEquals('1st Group B', $second_match->getHomeTeam()->getID());
        $this->assertEquals('2nd Group A', $second_match->getAwayTeam()->getID());
        $this->assertEquals('1', $second_match->getCourt());
        $this->assertEquals('City Stadium', $second_match->getVenue());
        $this->assertEquals('2020-06-06', $second_match->getDate());
        $this->assertEquals('10:00', $second_match->getWarmup());
        $this->assertEquals('0:50', $second_match->getDuration());
        $this->assertEquals('10:10', $second_match->getStart());
        $this->assertNull($second_match->getNotes());
        $this->assertEquals('SF1 loser', $second_match->getOfficials()->getTeamID());

        $this->assertEquals('A Bobs', $if_unknown->getMatchById('FIN')->getManager()->getManagerName());
        $this->assertEquals('J Doe', $if_unknown->getMatchById('FIN')->getMVP());

        $break = $if_unknown->getMatches()[2];
        if ($break instanceof IfUnknownBreak) {
            $this->assertEquals('11:30', $break->getStart());
            $this->assertEquals('2020-06-06', $break->getDate());
            $this->assertEquals('1:20', $break->getDuration());
            $this->assertEquals('Lunch break', $break->getName());
            $this->assertEquals($if_unknown, $break->getIfUnknown());
        } else {
            $this->fail('Match 3 in the IfUnknown block should have been represented as a IfUnknownBreak');
        }
    }

    public function testIfUnknownLoadSparse() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ifunknown'))), 'incomplete-group-multi-stage-sparse.json');
        $if_unknown = $competition->getStageById('F')->getIfUnknown();

        $this->assertEquals(MatchType::CONTINUOUS, $if_unknown->getMatchType());
        $this->assertEquals('unknown', $if_unknown->getID());
        $this->assertEquals(MatchType::CONTINUOUS, $if_unknown->getMatchType());
        $this->assertEquals('Test Knockout', $if_unknown->getCompetition()->getName());
        $this->assertEqualsCanonicalizing([], $if_unknown->getTeamIDs());
        $this->assertFalse($if_unknown->matchesHaveCourts());
        $this->assertFalse($if_unknown->matchesHaveDates());
        $this->assertFalse($if_unknown->matchesHaveDurations());
        $this->assertFalse($if_unknown->matchesHaveManagers());
        $this->assertFalse($if_unknown->matchesHaveMVPs());
        $this->assertFalse($if_unknown->matchesHaveNotes());
        $this->assertFalse($if_unknown->matchesHaveOfficials());
        $this->assertFalse($if_unknown->matchesHaveStarts());
        $this->assertFalse($if_unknown->matchesHaveVenues());
        $this->assertFalse($if_unknown->matchesHaveWarmups());

        $second_match = $if_unknown->getMatches()[1];
        $this->assertEquals('SF2', $second_match->getID());
        $this->assertInstanceOf('VBCompetitions\Competitions\IfUnknown', $second_match->getGroup());
        $this->assertFalse($second_match->isComplete());
        $this->assertFalse($second_match->isDraw());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $second_match->getWinnerTeamId());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $second_match->getLoserTeamId());
        $this->assertEquals(0, $second_match->getHomeTeamSets());
        $this->assertEquals(0, $second_match->getAwayTeamSets());
        $this->assertEquals('1st Group B', $second_match->getHomeTeam()->getID());
        $this->assertEquals('2nd Group A', $second_match->getAwayTeam()->getID());
        $this->assertNull($second_match->getCourt());
        $this->assertNull($second_match->getVenue());
        $this->assertNull($second_match->getDate());
        $this->assertNull($second_match->getWarmup());
        $this->assertNull($second_match->getDuration());
        $this->assertNull($second_match->getStart());
        $this->assertNull($second_match->getNotes());
        $this->assertNull($second_match->getOfficials());

        $break = $if_unknown->getMatches()[2];
        $break = $if_unknown->getMatches()[2];
        if ($break instanceof IfUnknownBreak) {
            $this->assertNull($break->getStart());
            $this->assertNull($break->getDate());
            $this->assertNull($break->getDuration());
            $this->assertEquals('Lunch break', $break->getName());
            $this->assertEquals($if_unknown, $break->getIfUnknown());
        } else {
            $this->fail('Match 3 in the IfUnknown block should have been represented as a IfUnknownBreak');
        }
    }

    public function testIfUnknownMatchSaveScoresIsIgnored() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $if_unknown = $competition->getStageById('L')->getIfUnknown();
        $match = $if_unknown->getMatches()[0];

        $match->setScores([23], [25]);
        $this->assertEquals(0, count($match->getHomeTeamScores()));
        $this->assertEquals(0, count($match->getAwayTeamScores()));
    }

    public function testIfUnknownNoDuplicateMatchIDs() : void
    {
        $this->expectExceptionMessage('stage ID {F}, ifUnknown: matches with duplicate IDs {FIN} not allowed');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ifunknown'))), 'duplicate-match-ids.json');
    }

    public function testIfUnknownNoSuchMatch() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ifunknown'))), 'incomplete-group-multi-stage-sparse.json');
        $this->expectExceptionMessage('Match with ID NO_SUCH_MATCH not found');
        $this->expectException(OutOfBoundsException::class);
        $competition->getStageById('F')->getIfUnknown()->getMatchById('NO_SUCH_MATCH');
    }
}
