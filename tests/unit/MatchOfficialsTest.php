<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Crossover;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\MatchOfficials;
use VBCompetitions\Competitions\MatchType;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(MatchOfficials::class)]
final class MatchOfficialsTest extends TestCase {
    public function testOfficialsNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'officials'))), 'officials-team.json');
        $this->assertNull($competition->getStage('L')->getGroup('LG')->getMatch('LG2')->getOfficials());
    }

    public function testOfficialsTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'officials'))), 'officials-team.json');
        $match_officials = $competition->getStage('L')->getGroup('LG')->getMatch('LG1')->getOfficials();

        $this->assertTrue($match_officials->isTeam());
        $this->assertEquals('TM1', $match_officials->getTeamID());
        $this->assertFalse($match_officials->hasSecondRef());
        $this->assertFalse($match_officials->hasChallengeRef());
        $this->assertFalse($match_officials->hasAssistantChallengeRef());
        $this->assertFalse($match_officials->hasReserveRef());
        $this->assertFalse($match_officials->hasScorer());
        $this->assertFalse($match_officials->hasAssistantScorer());
        $this->assertFalse($match_officials->hasLinespersons());
        $this->assertFalse($match_officials->hasBallCrew());
        $this->assertNull($match_officials->getFirstRef());
        $this->assertNull($match_officials->getSecondRef());
        $this->assertNull($match_officials->getChallengeRef());
        $this->assertNull($match_officials->getAssistantChallengeRef());
        $this->assertNull($match_officials->getReserveRef());
        $this->assertNull($match_officials->getScorer());
        $this->assertNull($match_officials->getAssistantScorer());
        $this->assertEquals('LG1', $match_officials->getMatch()->getID());
    }

    public function testOfficialsPerson() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'officials'))), 'officials-persons.json');
        $match_officials = $competition->getStage('L')->getGroup('LG')->getMatch('LG1')->getOfficials();

        $this->assertFalse($match_officials->isTeam());
        $this->assertNull($match_officials->getTeamID());
        $this->assertEquals('A First', $match_officials->getFirstRef());
        $this->assertEquals('B Second', $match_officials->getSecondRef());
        $this->assertEquals('C Challenge', $match_officials->getChallengeRef());
        $this->assertEquals('D Assistant', $match_officials->getAssistantChallengeRef());
        $this->assertEquals('E Reserve', $match_officials->getReserveRef());
        $this->assertEquals('F Scorer', $match_officials->getScorer());
        $this->assertEquals('G Assistant', $match_officials->getAssistantScorer());
        $this->assertCount(2, $match_officials->getLinespersons());
        $this->assertEquals("H Line", $match_officials->getLinespersons()[0]);
        $this->assertCount(2, $match_officials->getBallCrew());
        $this->assertEquals("J Ball", $match_officials->getBallCrew()[0]);
    }

    public function testOfficialsSetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'officials'))), 'officials-team.json');
        $match_officials = $competition->getStage('L')->getGroup('LG')->getMatch('LG1')->getOfficials();

        $this->assertTrue($match_officials->isTeam());

        $match_officials->setFirstRef('A First');
        $match_officials->setSecondRef('B Second');
        $match_officials->setChallengeRef('C Challenge');
        $match_officials->setAssistantChallengeRef('D Assistant');
        $match_officials->setReserveRef('E Reserve');
        $match_officials->setScorer('F Scorer');
        $match_officials->setAssistantScorer('G Assistant');
        $match_officials->setLinespersons(['H Line', 'I Line']);
        $match_officials->setBallCrew(['J Ball', 'K Ball']);

        $this->assertEquals('A First', $match_officials->getFirstRef());
        $this->assertEquals('B Second', $match_officials->getSecondRef());
        $this->assertEquals('C Challenge', $match_officials->getChallengeRef());
        $this->assertEquals('D Assistant', $match_officials->getAssistantChallengeRef());
        $this->assertEquals('E Reserve', $match_officials->getReserveRef());
        $this->assertEquals('F Scorer', $match_officials->getScorer());
        $this->assertEquals('G Assistant', $match_officials->getAssistantScorer());
        $this->assertCount(2, $match_officials->getLinespersons());
        $this->assertEquals("H Line", $match_officials->getLinespersons()[0]);
        $this->assertCount(2, $match_officials->getBallCrew());
        $this->assertEquals("J Ball", $match_officials->getBallCrew()[0]);
        $this->assertNull($match_officials->getTeamID());
        $this->assertFalse($match_officials->isTeam());

        $match_officials->setTeamID('{L:LG:LG2:winner}');

        $this->assertFalse($match_officials->hasSecondRef());
        $this->assertFalse($match_officials->hasChallengeRef());
        $this->assertFalse($match_officials->hasAssistantChallengeRef());
        $this->assertFalse($match_officials->hasReserveRef());
        $this->assertFalse($match_officials->hasScorer());
        $this->assertFalse($match_officials->hasAssistantScorer());
        $this->assertFalse($match_officials->hasLinespersons());
        $this->assertFalse($match_officials->hasBallCrew());
        $this->assertNull($match_officials->getFirstRef());
        $this->assertNull($match_officials->getSecondRef());
        $this->assertNull($match_officials->getChallengeRef());
        $this->assertNull($match_officials->getAssistantChallengeRef());
        $this->assertNull($match_officials->getReserveRef());
        $this->assertNull($match_officials->getScorer());
        $this->assertNull($match_officials->getAssistantScorer());
        $this->assertTrue($match_officials->isTeam());
        $this->assertEquals('{L:LG:LG2:winner}', $match_officials->getTeamID());
    }

    public function testOfficialsConstructor() : void
    {
        $competition = new Competition('test');
        $stage = new Stage($competition, 'S');
        $group = new Crossover($stage, 'G', MatchType::CONTINUOUS);
        $match = new GroupMatch($group, 'M1');

        try {
            new MatchOfficials($match, null, null);
            $this->fail('MatchOfficial should require a team or a person');
        } catch (Exception $e) {
            $this->assertEquals('Match Officials must be either a team or a person', $e->getMessage());
        }
    }

    public function testOfficialsExceptionSettingInvalidTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'officials'))), 'officials-team.json');
        $match_officials = $competition->getStage('L')->getGroup('LG')->getMatch('LG1')->getOfficials();

        $this->expectExceptionMessageMatches('/Invalid team reference for officials in match with ID "LG1": "{L:LG:LG2"/');
        $match_officials->setTeamID('{L:LG:LG2');
    }
}
