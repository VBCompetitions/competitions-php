<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
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
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

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
        $this->assertEquals('A Bobs', $match->getMVP()->getName());
        $this->assertEquals('Dave', $match->getManager()->getManagerName());
        $this->assertEquals('Local derby', $match->getNotes());
    }

    public function testGroupMatchContinuousAwayWin() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-away-win.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertFalse($match->isDraw(), 'Match should not be a draw');
        $this->assertEquals('TM2', $match->getWinnerTeamId(), 'TM2 should be found as the winner');
        $this->assertEquals('TM1', $match->getLoserTeamId(), 'TM1 should be found as the loser');
    }

    public function testGroupMatchContinuousDrawThrowsWinner() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-draw.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertTrue($match->isDraw(), 'Match result should be found as draw');

        $this->expectExceptionMessage('Match drawn, there is no winner');
        $match->getWinnerTeamId();
    }

    public function testGroupMatchContinuousDrawThrowsLoser() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-draw.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertTrue($match->isDraw(), 'Match result should be found as draw');

        $this->expectExceptionMessage('Match drawn, there is no loser');
        $match->getLoserTeamId();
    }

    public function testGroupMatchContinuousDrawDisallowed() : void
    {
        $this->expectExceptionMessage('Invalid match information (in match {S:SG:SG1}): scores show a draw but draws are not allowed');
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-draw-disallowed.json');
        $competition->getStage('S')->getGroup('SG')->getMatch('SG1');
    }

    public function testGroupMatchContinuousThrowsGettingHomeSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-away-win.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->expectExceptionMessage('Match has no sets because the match type is continuous');
        $match->getHomeTeamSets();
    }

    public function testGroupMatchContinuousThrowsGettingAwaySets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-away-win.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->expectExceptionMessage('Match has no sets because the match type is continuous');
        $match->getAwayTeamSets();
    }

    public function testGroupMatchSetsLengthMismatch() : void
    {
        $this->expectExceptionMessage('Invalid match information for match SG1: team scores have different length');
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-scores-length-mismatch.json');
        $competition->getStage('S')->getGroup('SG')->getMatch('SG1');
    }

    public function testGroupMatchSetsHomeWin() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-home-win.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertEquals('TM1', $match->getWinnerTeamId(), 'TM1 should be found as the winner');
        $this->assertEquals('TM2', $match->getLoserTeamId(), 'TM2 should be found as the loser');
    }

    public function testGroupMatchSetsAwayWin() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-away-win.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertEquals('TM2', $match->getWinnerTeamId(), 'TM2 should be found as the winner');
        $this->assertEquals('TM1', $match->getLoserTeamId(), 'TM1 should be found as the loser');
    }

    public function testGroupMatchSetsGetSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-home-win.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as completed');
        $this->assertEquals(2, $match->getHomeTeamSets());
        $this->assertEquals(0, $match->getAwayTeamSets());
    }

    public function testGroupMatchSetsIncompleteBestOfGetWinnerThrows() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-maxsets.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertFalse($match->isComplete(), 'Match should be found as incomplete');

        $this->expectExceptionMessage('Match incomplete, there is no winner');
        $match->getWinnerTeamId();
    }

    public function testGroupMatchSetsIncompleteBestOfGetLoserThrows() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-maxsets.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertFalse($match->isComplete(), 'Match should be found as incomplete');

        $this->expectExceptionMessage('Match incomplete, there is no loser');
        $match->getLoserTeamId();
    }

    public function testGroupMatchSetsIncompleteMinPointsGetWinnerThrows() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-maxsets.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertFalse($match->isComplete(), 'Match should be found as incomplete');

        $this->expectExceptionMessage('Match incomplete, there is no winner');
        $match->getWinnerTeamId();
    }

    public function testGroupMatchSetsIncompleteMinPointsGetLoserThrows() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-maxsets.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertFalse($match->isComplete(), 'Match should be found as incomplete');

        $this->expectExceptionMessage('Match incomplete, there is no loser');
        $match->getLoserTeamId();
    }

    public function testGroupMatchSetsIncompleteFirstSetMatchDeclaredCompleteHasResult() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-incomplete-first-set.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');

        $this->assertTrue($match->isComplete(), 'Match should be found as incomplete');
    }

    public function testGroupMatchSetsInsufficientPoints() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-insufficient-points.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');
        $this->assertFalse($match->isComplete());
    }

    public function testGroupMatchSetsDawnGame() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'sets-draw.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');
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

    // public function testGroupMatchSetsMatchDifferentScoreLengths() : void
    // {
    //     copy(
    //         realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition-sets-duration.json'))),
    //         join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition-sets-duration.json'))
    //     );

    //     $this->expectExceptionMessage('Invalid set scores: score arrays are different lengths');
    //     $dummy_competition = new Competition('dummy for score update');
    //     $dummy_stage = new Stage($dummy_competition, 'S');
    //     $dummy_group = new Crossover($dummy_stage, 'G', MatchType::SETS);
    //     $config = new SetConfig($dummy_group);
    //     $config->loadFromData(json_decode('{"maxSets": 3, "setsToWin": 1, "clearPoints": 2, "minPoints": 1, "pointsToWin": 25, "lastSetPointsToWin": 15, "maxPoints": 50, "lastSetMaxPoints": 30}'));
    //     GroupMatch::assertSetScoresValid(
    //         [25],
    //         [19, 19],
    //         $config
    //     );
    // }

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
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');
        $home_score = $match->getHomeTeam()->getScores();
        $home_score[0] = 19;
        $this->assertEquals(21, $match->getHomeTeam()->getScores()[0]);
        $this->assertEquals(19, $home_score[0]);
    }

    public function testGroupMatchGetCompleteReadOnly() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'continuous-home-win.json');
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');
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
        $match = $competition->getStage('S')->getGroup('SG')->getMatch('SG1');
        $this->assertEquals(0, count($match->getHomeTeam()->getScores()));
        $this->expectExceptionMessage('Match incomplete, there is no winner');
        $match->getWinnerTeamId();
    }

    public function testGroupMatchSaveScoresContinuous() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $group = $competition->getStage('L')->getGroup('RL');
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
        $match = $competition->getStage('L')->getGroup('RL')->getMatches()[0];

        $this->expectExceptionMessage('Invalid score: draws not allowed in this group');
        $match->setScores([22], [22], true);
    }

    public function testGroupMatchSaveScoresContinuousWantsCompleteness() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $group = $competition->getStage('L')->getGroup('RL');
        $match = $group->getMatches()[0];

        $this->expectExceptionMessage('Invalid score: match type is continuous, but the match completeness is not set');
        $match->setScores([23], [22]);
    }

    public function testGroupMatchSaveScoresSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $match = $competition->getStage('L')->getGroup('RS')->getMatches()[0];

        $match->setScores([25, 25, 25], [17, 19, 12], false);
        $this->assertFalse($match->isComplete());
        $match->setScores($match->getHomeTeamScores(), $match->getAwayTeamScores(), true);
        $this->assertTrue($match->isComplete());
        $this->assertEquals(25, $match->getHomeTeamScores()[0]);
        $this->assertEquals(17, $match->getAwayTeamScores()[0]);
    }

    public function testGroupMatchSaveScoresSetsCatchBannedDraws() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $match = $competition->getStage('L')->getGroup('RS')->getMatches()[0];

        $this->expectExceptionMessage('Invalid set scores: data contains non-zero scores for a set after an incomplete set');
        $match->setScores([25, 25, 25], [25, 25, 25], false);
    }

    public function testGroupMatchMatchSaveScoresSetsWantsCompleteness() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matches'))), 'save-scores.json');
        $group = $competition->getStage('L')->getGroup('RS');
        $match = $group->getMatches()[0];

        $this->expectExceptionMessage('Invalid results: match type is sets and match has a duration, but the match completeness is not set');
        $match->setScores([25, 25, 25], [20, 22, 19]);
    }

    public function testGroupMatchSavesFriendlyInfo() : void
    {
        $competition = new Competition('test competition');
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $group = new Crossover($stage, 'C', MatchType::SETS);
        $stage->addGroup($group);
        $config = new SetConfig($group);
        $group->setSetConfig($config);

        $team1 = new CompetitionTeam($competition, 'TM1', 'Team 1');
        $competition->addTeam($team1);
        $team2 = new CompetitionTeam($competition, 'TM2', 'Team 2');
        $competition->addTeam($team2);

        $match = new GroupMatch($group, 'M1');
        $home_team = new MatchTeam($match, 'TM1');
        $away_team = new MatchTeam($match, 'TM2');
        $match->setHomeTeam($home_team);
        $match->setAwayTeam($away_team);
        $match->setFriendly(true);
        $group->addMatch($match);

        $competition = Competition::loadFromCompetitionJSON(json_encode($competition));
        $this->assertTrue($competition->getStage('S')->getGroup('C')->getMatch('M1')->isFriendly());
    }

    public function testGroupMatchContinuousScoresLengthMismatch() : void
    {
        $competition = new Competition('test competition');
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $group = new Crossover($stage, 'C', MatchType::CONTINUOUS);
        $config = new SetConfig($group);

        $this->expectExceptionMessage('Invalid results: match type is continuous, but score length is greater than one');
        GroupMatch::assertContinuousScoresValid([10, 10], [20], $config);
    }

    public function testGroupMatchContinuousScoresDrawAndConfigObject() : void
    {
        $groupConfig = new stdClass();
        $groupConfig->drawsAllowed = true;
        GroupMatch::assertContinuousScoresValid([10], [20], $groupConfig);

        $groupConfig->drawsAllowed = false;
        $this->expectExceptionMessage('Invalid score: draws not allowed in this group');
        GroupMatch::assertContinuousScoresValid([10], [10], $groupConfig);
    }

    public function testGroupMatchSetsScoresLengthMismatch() : void
    {
        $competition = new Competition('test competition');
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $group = new Crossover($stage, 'C', MatchType::SETS);
        $config = new SetConfig($group);

        $this->expectExceptionMessage('Invalid set scores: score arrays are different lengths');
        GroupMatch::assertSetScoresValid([10, 10], [20], $config);
    }

    public function testGroupMatchSetsScoresAwayHasExtraInfo() : void
    {
        $competition = new Competition('test competition');
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $group = new Crossover($stage, 'C', MatchType::SETS);
        $config = new SetConfig($group);

        $this->expectExceptionMessage('Invalid set scores: data contains non-zero scores for a set after an incomplete set');
        GroupMatch::assertSetScoresValid([10, 10, 0], [25, 15, 1], $config);
    }

    public function testGroupMatchSetsScoresDeciderMaxedOut() : void
    {
        $competition = new Competition('test competition');
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $group = new Crossover($stage, 'C', MatchType::SETS);
        $stage->addGroup($group);
        $config = new SetConfig($group);
        $group->setSetConfig($config);

        $team1 = new CompetitionTeam($competition, 'TM1', 'Team 1');
        $competition->addTeam($team1);
        $team2 = new CompetitionTeam($competition, 'TM2', 'Team 2');
        $competition->addTeam($team2);

        $match = new GroupMatch($group, 'M1');
        $home_team = new MatchTeam($match, 'TM1');
        $away_team = new MatchTeam($match, 'TM2');
        $match->setHomeTeam($home_team);
        $match->setAwayTeam($away_team);
        $group->addMatch($match);

        $config->setLastSetMaxPoints(20);
        $this->expectNotToPerformAssertions();
        $match->setScores([10, 10, 25, 25, 19], [25, 25, 10, 10, 20]);
    }

    public function testGroupMatchSetters() : void
    {
        $competition = new Competition('test competition');
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $group = new Crossover($stage, 'C', MatchType::SETS);
        $stage->addGroup($group);
        $config = new SetConfig($group);
        $group->setSetConfig($config);

        $team1 = new CompetitionTeam($competition, 'TM1', 'Team 1');
        $competition->addTeam($team1);
        $team2 = new CompetitionTeam($competition, 'TM2', 'Team 2');
        $competition->addTeam($team2);

        $match = new GroupMatch($group, 'M1');

        try {
            $match->setDate('Today');
            $this->fail('GroupMatch should not allow a bad date');
        } catch (Exception $e) {
            $this->assertEquals('Invalid date "Today": must contain a value of the form "YYYY-MM-DD"', $e->getMessage());
        }

        try {
            $match->setDate('2024-02-30');
            $this->fail('GroupMatch should not allow a non-existent date');
        } catch (Exception $e) {
            $this->assertEquals('Invalid date "2024-02-30": date does not exist', $e->getMessage());
        }

        try {
            $match->setWarmup('This morning');
            $this->fail('GroupMatch should not allow a bad warmup time');
        } catch (Exception $e) {
            $this->assertEquals('Invalid warmup time "This morning": must contain a value of the form "HH:mm" using a 24 hour clock', $e->getMessage());
        }

        try {
            $match->setStart('This afternoon');
            $this->fail('GroupMatch should not allow a bad start time');
        } catch (Exception $e) {
            $this->assertEquals('Invalid start time "This afternoon": must contain a value of the form "HH:mm" using a 24 hour clock', $e->getMessage());
        }

        try {
            $match->setDuration('20 minutes');
            $this->fail('GroupMatch should not allow a bad duration');
        } catch (Exception $e) {
            $this->assertEquals('Invalid duration "20 minutes": must contain a value of the form "HH:mm"', $e->getMessage());
        }
    }
}
