<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\Crossover;
use VBCompetitions\Competitions\Group;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\MatchTeam;
use VBCompetitions\Competitions\MatchType;
use VBCompetitions\Competitions\SetConfig;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(Group::class)]
#[CoversClass(GroupMatch::class)]
#[CoversClass(MatchTeam::class)]
#[CoversClass(SetConfig::class)]
final class GroupMatchTest extends TestCase {
    public function testGroupMatchContinuousHomeWin() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-home-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertFalse($match->isDraw(), 'Match should not be a draw');
        $this->assertEquals('TM1', $match->getWinnerTeamId(), 'TM1 should be found as the winner');
        $this->assertEquals('TM2', $match->getLoserTeamId(), 'TM2 should be found as the loser');
        $this->assertEquals('SG', $match->getGroup()->getID());
        $this->assertEquals('TM1', $match->getHomeTeam()->getID());
        $this->assertEquals('TM2', $match->getAwayTeam()->getID());

        $this->assertEquals('SG1', $match->getID());
        $this->assertEquals('1', $match->getCourt());
        $this->assertEquals('Home Stadium', $match->getVenue());
        $this->assertEquals('2020-06-06', $match->getDate());
        $this->assertEquals('09:10', $match->getWarmup());
        $this->assertEquals('09:20', $match->getStart());
        $this->assertEquals('0:20', $match->getDuration());
        $this->assertTrue($match->getComplete());
        $this->assertEquals('Dave', $match->getOfficials()->getFirstRef());
        $this->assertEquals('A Bobs', $match->getMVP());
        $this->assertEquals('Dave', $match->getManager()->getManagerName());
        $this->assertEquals('Local derby', $match->getNotes());
    }

    public function testGroupMatchContinuousAwayWin() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-away-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertFalse($match->isDraw(), 'Match should not be a draw');
        $this->assertEquals('TM2', $match->getWinnerTeamId(), 'TM2 should be found as the winner');
        $this->assertEquals('TM1', $match->getLoserTeamId(), 'TM1 should be found as the loser');
    }

    public function testGroupMatchContinuousDrawThrowsWinner() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-draw.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertTrue($match->isDraw(), 'Match result should be found as draw');

        $this->expectExceptionMessage('Match drawn, there is no winner');
        $match->getWinnerTeamId();
    }

    public function testGroupMatchContinuousDrawThrowsLoser() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-draw.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertTrue($match->isDraw(), 'Match result should be found as draw');

        $this->expectExceptionMessage('Match drawn, there is no loser');
        $match->getLoserTeamId();
    }

    public function testGroupMatchContinuousDrawDisallowed() : void
    {
        $this->expectExceptionMessage('Invalid match information (in match {S:SG:SG1}): scores show a draw but draws are not allowed');
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-draw-disallowed.json');
        $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');
    }

    public function testGroupMatchContinuousThrowsGettingHomeSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-away-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->expectExceptionMessage('Match has no sets because the match type is continuous');
        $match->getHomeTeamSets();
    }

    public function testGroupMatchContinuousThrowsGettingAwaySets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-away-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->expectExceptionMessage('Match has no sets because the match type is continuous');
        $match->getAwayTeamSets();
    }

    public function testGroupMatchSetsLengthMismatch() : void
    {
        $this->expectExceptionMessage('Invalid match information for match SG1: team scores have different length');
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-scores-length-mismatch.json');
        $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');
    }

    public function testGroupMatchSetsHomeWin() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-home-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertEquals('TM1', $match->getWinnerTeamId(), 'TM1 should be found as the winner');
        $this->assertEquals('TM2', $match->getLoserTeamId(), 'TM2 should be found as the loser');
    }

    public function testGroupMatchSetsAwayWin() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-away-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertEquals('TM2', $match->getWinnerTeamId(), 'TM2 should be found as the winner');
        $this->assertEquals('TM1', $match->getLoserTeamId(), 'TM1 should be found as the loser');
    }

    public function testGroupMatchSetsGetSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-home-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertEquals(2, $match->getHomeTeamSets());
        $this->assertEquals(0, $match->getAwayTeamSets());
    }

    public function testGroupMatchSetsIncompleteBestOfGetWinnerThrows() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-maxsets.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertFalse($match->isComplete(), 'Match should be found as incomplete');

        $this->expectExceptionMessage('Match incomplete, there is no winner');
        $match->getWinnerTeamId();
    }

    public function testGroupMatchSetsIncompleteBestOfGetLoserThrows() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-maxsets.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertFalse($match->isComplete(), 'Match should be found as incomplete');

        $this->expectExceptionMessage('Match incomplete, there is no loser');
        $match->getLoserTeamId();
    }

    public function testGroupMatchSetsIncompleteMinPointsGetWinnerThrows() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-maxsets.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertFalse($match->isComplete(), 'Match should be found as incomplete');

        $this->expectExceptionMessage('Match incomplete, there is no winner');
        $match->getWinnerTeamId();
    }

    public function testGroupMatchSetsIncompleteMinPointsGetLoserThrows() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-maxsets.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertFalse($match->isComplete(), 'Match should be found as incomplete');

        $this->expectExceptionMessage('Match incomplete, there is no loser');
        $match->getLoserTeamId();
    }

    public function testGroupMatchSetsIncompleteFirstSetMatchDeclaredCompleteHasResult() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-first-set.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as incomplete');
    }

    public function testGroupMatchSetsInsufficientPoints() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-insufficient-points.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');
        $this->assertFalse($match->isComplete());
    }

    public function testGroupMatchSetsDawnGame() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-draw.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');
        $this->assertTrue($match->isDraw());
    }

    public function testGroupMatchSetsDrawsDisallowed() : void
    {
        $this->expectExceptionMessage('Invalid match information (in match {S:SG:SG1}): scores show a draw but draws are not allowed');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-draw-disallowed.json');
    }

    public function testGroupMatchSetsTooManySets() : void
    {
        $this->expectExceptionMessage('Invalid match information (in match {S:SG:SG1}): team scores have more sets than the maximum allowed length');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-too-many-sets.json');
    }

    public function testGroupMatchSetsMatchDifferentScoreLengths() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition-sets-duration.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition-sets-duration.json'))
        );

        $this->expectExceptionMessage('Invalid set scores: score arrays are different lengths');
        $dummy_competition = new Competition('dummy for score update');
        $dummy_stage = new Stage($dummy_competition, 'S');
        $dummy_group = new Crossover($dummy_stage, 'G', MatchType::SETS);
        $config = new SetConfig($dummy_group);
        $config->loadFromData(json_decode('{"maxSets": 3, "setsToWin": 1, "clearPoints": 2, "minPoints": 1, "pointsToWin": 25, "lastSetPointsToWin": 15, "maxPoints": 50, "lastSetMaxPoints": 30}'));
        GroupMatch::assertSetScoresValid(
            [25],
            [19, 19],
            $config
        );
    }

    public function testGroupMatchSetsMatchTooManyScores() : void
    {
        $this->expectExceptionMessage('Invalid set scores: score arrays are longer than the maximum number of sets allowed');
        $dummy_competition = new Competition('dummy for score update');
        $dummy_stage = new Stage($dummy_competition, 'S');
        $dummy_group = new Crossover($dummy_stage, 'G', MatchType::SETS);
        $config = new SetConfig($dummy_group);
        $config->loadFromData(json_decode('{"maxSets": 3, "setsToWin": 1, "clearPoints": 2, "minPoints": 1, "pointsToWin": 25, "lastSetPointsToWin": 15, "maxPoints": 50, "lastSetMaxPoints": 30}'));
        GroupMatch::assertSetScoresValid(
            [25, 25, 25, 25, 25, 25],
            [19, 19, 19, 19, 19, 19],
            $config
        );
    }

    public function testGroupMatchSetsMatchHomeTeamTooManyInDecider() : void
    {
        $dummy_competition = new Competition('dummy for score update');
        $dummy_stage = new Stage($dummy_competition, 'S');
        $dummy_group = new Crossover($dummy_stage, 'G', MatchType::SETS);
        $config = new SetConfig($dummy_group);
        $config->loadFromData(json_decode('{"maxSets": 3, "setsToWin": 1, "clearPoints": 2, "minPoints": 1, "pointsToWin": 25, "lastSetPointsToWin": 15, "maxPoints": 50, "lastSetMaxPoints": 30}'));
        $this->expectExceptionMessage('Invalid set scores: value for set score at index 2 shows home team scoring more points than necessary to win the set');
        GroupMatch::assertSetScoresValid(
            [25, 19, 25],
            [19, 25, 19],
            $config
        );
    }

    public function testGroupMatchSetsMatchAwayTeamTooManyInDecider() : void
    {
        $dummy_competition = new Competition('dummy for score update');
        $dummy_stage = new Stage($dummy_competition, 'S');
        $dummy_group = new Crossover($dummy_stage, 'G', MatchType::SETS);
        $config = new SetConfig($dummy_group);
        $config->loadFromData(json_decode('{"maxSets": 3, "setsToWin": 1, "clearPoints": 2, "minPoints": 1, "pointsToWin": 25, "lastSetPointsToWin": 15, "maxPoints": 50, "lastSetMaxPoints": 30}'));
        $this->expectExceptionMessage('Invalid set scores: value for set score at index 2 shows away team scoring more points than necessary to win the set');
        GroupMatch::assertSetScoresValid(
            [25, 19, 19],
            [19, 25, 25],
            $config
        );
    }

    public function testGroupMatchGetScoreReadOnly() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-home-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');
        $home_score = $match->getHomeTeam()->getScores();
        $home_score[0] = 19;
        $this->assertEquals(21, $match->getHomeTeam()->getScores()[0]);
        $this->assertEquals(19, $home_score[0]);
    }

    public function testGroupMatchGetCompleteReadOnly() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-home-win.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');
        $this->assertTrue($match->getComplete());
        $this->assertTrue($match->isComplete());
        $match->setComplete(false);
        $this->assertFalse($match->getComplete());
        $this->assertFalse($match->isComplete());
    }

    public function testGroupMatchHomeTeamCannotOfficiateSelf() : void
    {
        $this->expectExceptionMessage('Refereeing team (in match {S:SG:SG1}) cannot be the same as one of the playing teams');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'home-team-officiates-self.json');
    }

    public function testGroupMatchAwayTeamCannotOfficiateSelf() : void
    {
        $this->expectExceptionMessage('Refereeing team (in match {S:SG:SG1}) cannot be the same as one of the playing teams');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'away-team-officiates-self.json');
    }

    public function testGroupMatchNoScores() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-no-result.json');
        $match = $competition->getStageById('S')->getGroupById('SG')->getMatchById('SG1');
        $this->assertEquals(0, count($match->getHomeTeam()->getScores()));
        $this->expectExceptionMessage('Match incomplete, there is no winner');
        $match->getWinnerTeamId();
    }

    public function testGroupMatchSaveScoresContinuous() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $group = $competition->getStageById('L')->getGroupById('RL');
        $match = $group->getMatches()[0];
        $home_team = $match->getHomeTeam();
        $away_team = $match->getAwayTeam();

        $this->assertInstanceOf('VBCompetitions\Competitions\GroupMatch', $match);
        $match->setScores([23], $away_team->getScores(), false);
        $this->assertFalse($match->isComplete());
        $match->setScores($home_team->getScores(), [19], true);
        $this->assertTrue($match->isComplete());
        $this->assertEquals(23, $home_team->getScores()[0]);
        $this->assertEquals(19, $away_team->getScores()[0]);
    }

    public function testGroupMatchSaveScoresContinuousCatchBannedDraws() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $match = $competition->getStageById('L')->getGroupById('RL')->getMatches()[0];

        $this->expectExceptionMessage('Invalid score: draws not allowed in this group');
        $match->setScores([22], [22], true);
    }

    public function testGroupMatchSaveScoresContinuousWantsCompleteness() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $group = $competition->getStageById('L')->getGroupById('RL');
        $match = $group->getMatches()[0];

        $this->expectExceptionMessage('Invalid score: match type is continuous, but the match completeness is not set');
        $match->setScores([23], [22]);
    }

    public function testGroupMatchSaveScoresSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $match = $competition->getStageById('L')->getGroupById('RS')->getMatches()[0];

        $match->setScores([25, 25, 25], [17, 19, 12], false);
        $this->assertFalse($match->isComplete());
        $match->setScores($match->getHomeTeamScores(), $match->getAwayTeamScores(), true);
        $this->assertTrue($match->isComplete());
        $this->assertEquals(25, $match->getHomeTeamScores()[0]);
        $this->assertEquals(17, $match->getAwayTeamScores()[0]);
        // $this->assertEquals($match->getHomeTeam()->getID(), $match->getWinnerTeamId());
    }

    public function testGroupMatchSaveScoresSetsCatchBannedDraws() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $match = $competition->getStageById('L')->getGroupById('RS')->getMatches()[0];

        $this->expectExceptionMessage('Invalid set scores: data contains non-zero scores for a set after an incomplete set');
        $match->setScores([25, 25, 25], [25, 25, 25], false);
    }

    public function testGroupMatchMatchSaveScoresSetsWantsCompleteness() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $group = $competition->getStageById('L')->getGroupById('RS');
        $match = $group->getMatches()[0];

        $this->expectExceptionMessage('Invalid results: match type is sets and match has a duration, but the match completeness is not set');
        $match->setScores([25, 25, 25], [20, 22, 19]);
    }

    // TODO - team cannot play itself

}
