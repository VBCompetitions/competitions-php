<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\GroupMatch;

#[CoversClass(Competition::class)]
#[CoversClass(GroupMatch::class)]
final class CompetitionUpdateMatchTest extends TestCase {
    protected function tearDown(): void
    {
        $files = glob(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))).DIRECTORY_SEPARATOR.'*.json');
        foreach($files as $file){
            if(is_file($file)){
                unlink($file);
            }
        }
    }

    public function testCompetitionUpdateMatchScores() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $score_updated = Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'RLM15', [23], [19], true);
        $this->assertTrue($score_updated);

        $updated_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json');
        $match_14 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM14');
        $match_15 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM15');
        $match_16 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM16');
        $this->assertTrue($match_14->isComplete());
        $this->assertTrue($match_15->isComplete());
        $this->assertFalse($match_16->isComplete());
        $this->assertEquals($match_14->getWinnerTeamId(), 'TM6');
        $this->assertEquals($match_15->getWinnerTeamId(), 'TM3');
    }

    public function testCompetitionUpdateMatchScoresTooManyValues() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Invalid results: match type is continuous, but score length is greater than one');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'RLM15', [23, 23], [19, 19], true);
    }

    public function testCompetitionUpdateMatchScoresDrawsNotAllowed() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Invalid score: draws not allowed in this group');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'RLM15', [19], [19], true);
    }

    public function testCompetitionUpdateMatchScoresWithSets() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition-sets.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition-sets.json'))
        );

        $score_updated = Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition-sets.json', 'L', 'RL', 'RLM15', [25, 25], [19, 19], null);
        $this->assertTrue($score_updated);

        $updated_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition-sets.json');
        $match_14 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM14');
        $match_15 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM15');
        $match_16 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM16');
        $this->assertTrue($match_14->isComplete());
        $this->assertTrue($match_15->isComplete());
        $this->assertFalse($match_16->isComplete());
        $this->assertEquals($match_14->getWinnerTeamId(), 'TM6');
        $this->assertEquals($match_15->getWinnerTeamId(), 'TM3');
    }

    public function testCompetitionUpdateMatchScoresWithSetsExplicitComplete() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition-sets-duration.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition-sets-duration.json'))
        );

        $score_updated = Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition-sets-duration.json', 'L', 'RL', 'RLM15', [25, 25], [19, 19], true);
        $this->assertTrue($score_updated);

        $updated_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition-sets-duration.json');
        $match_14 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM14');
        $match_15 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM15');
        $match_16 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM16');
        $this->assertTrue($match_14->isComplete());
        $this->assertTrue($match_15->isComplete());
        $this->assertFalse($match_16->isComplete());
        $this->assertEquals($match_14->getWinnerTeamId(), 'TM6');
        $this->assertEquals($match_15->getWinnerTeamId(), 'TM3');
    }

    public function testCompetitionUpdateMatchIncomplete() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $score_updated = Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'RLM15', [23], [19], false);
        $this->assertTrue($score_updated);

        $updated_competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json');
        $match_14 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM14');
        $match_15 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM15');
        $match_16 = $updated_competition->getStageById('L')->getGroupById('RL')->getMatchById('RLM16');
        $this->assertTrue($match_14->isComplete());
        $this->assertFalse($match_15->isComplete());
        $this->assertFalse($match_16->isComplete());
        $this->assertEquals($match_14->getWinnerTeamId(), 'TM6');

        $this->expectExceptionMessage('Match incomplete, there is no winner');
        $match_15->getWinnerTeamId();
    }

    public function testCompetitionUpdateContinuousMatchNoCompleteness() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Invalid results: match type is continuous, but the match completeness is not set');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'RLM15', [23], [19], null);
    }

    public function testCompetitionUpdateSetsMatchWithDurationNoCompleteness() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition-sets-duration.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition-sets-duration.json'))
        );

        $this->expectExceptionMessage('Invalid results: match type is sets and match has a duration, but the match completeness is not set');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition-sets-duration.json', 'L', 'RL', 'RLM15', [25, 25], [19, 19], null);
    }

    public function testCompetitionUpdateFileNotJSON() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition-not-json.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition-not-json.json'))
        );

        $this->expectExceptionMessage('Document does not contain valid JSON');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition-not-json.json', 'L', 'RL', 'BadID', [], [], null);
    }

    public function testCompetitionUpdateFileNotValid() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'invalid-competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'invalid-competition.json'))
        );

        $this->expectExceptionMessageMatches('/Competition data failed schema validation.*\[#\/required\] \[#\] The required properties \(name, teams, stages\) are missing/s');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'invalid-competition.json', 'L', 'RL', 'BadID', [], [], null);
    }

    public function testCompetitionUpdateScoreLengthMismatch() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Invalid results: score lengths are different');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'BadID', [10], [12, 12], null);
    }

    public function testCompetitionUpdateHomeScoresNotValid() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Invalid results: found a non-integer home team score value');
        // @phpstan-ignore argument.type (deliberately sending a string to check it is caught ar runtime)
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'BadID', [10, 'not-a-number'], [12, 12], null);
    }

    public function testCompetitionUpdateAwayScoresNotValid() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Invalid results: found a non-integer away team score value');
        // @phpstan-ignore argument.type (deliberately sending a string to check it is caught ar runtime)
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'BadID', [10, 10], [12, 'not-a-number'], null);
    }

    public function testCompetitionUpdateSetsBlockedForContinuousMatches() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Invalid results: match type is continuous, but score length is greater than one');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'RLM15', [10, 10], [12, 12], null);
    }

    public function testCompetitionUpdateSetsBlockSetScoresAfterIncompleteSet() : void
    {
        // Block us from saying the scores are 20-18, X>1-Y>1
        // i.e. I can't enter subsequent set scores when an earlier set is incomplete
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition-sets.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition-sets.json'))
        );

        $this->expectExceptionMessage('Invalid set scores: data contains non-zero scores for a set after an incomplete set');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition-sets.json', 'L', 'RL', 'RLM15', [18, 2], [16, 1], null);
    }

    public function testCompetitionUpdateDrawsBlockedWhenNotAllowed() : void
    {
        // A drawn score is only blocked when
        //   this is a completed result (explicitly complete for a continuous match or a match with a duration, or when the set score indicated completion (is this possible? do the set scores not ban this being possible?))
        // AND
        //   the drawsAllowed is not explicitly true
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Invalid score: draws not allowed in this group');
        Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'RLM1', [10], [10], true);
    }

    public function testCompetitionUpdateNoSuchStage() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Stage with ID NoSuchID not found');
        $this->expectException(OutOfBoundsException::class);
        $score_updated = Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'NoSuchID', 'RL', 'RLM1', [23], [19], true);
        $this->assertFalse($score_updated);
    }

    public function testCompetitionUpdateNoSuchGroup() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Group with ID NoSuchID not found');
        $this->expectException(OutOfBoundsException::class);
        $score_updated = Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'NoSuchID', 'RLM1', [23], [19], true);
        $this->assertFalse($score_updated);
    }

    public function testCompetitionUpdateNoSuchMatch() : void
    {
        copy(
            realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'competition.json'))),
            join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update', 'competition.json'))
        );

        $this->expectExceptionMessage('Match with ID NoSuchID not found');
        $this->expectException(OutOfBoundsException::class);
        $score_updated = Competition::updateMatchResults(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'update'))), 'competition.json', 'L', 'RL', 'NoSuchID', [23], [19], true);
        $this->assertFalse($score_updated);
    }
}

