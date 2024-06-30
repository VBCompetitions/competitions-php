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
use VBCompetitions\Competitions\MatchManager;
use VBCompetitions\Competitions\MatchType;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(MatchManager::class)]
final class MatchManagerTest extends TestCase {
    public function testManagerNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'manager'))), 'manager-team.json');
        $this->assertNull($competition->getStage('L')->getGroup('LG')->getMatch('LG2')->getManager());
    }

    public function testManagerTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'manager'))), 'manager-team.json');
        $match_manager = $competition->getStage('L')->getGroup('LG')->getMatch('LG1')->getManager();

        $this->assertTrue($match_manager->isTeam());
        $this->assertEquals('TM1', $match_manager->getTeamID());
        $this->assertNull($match_manager->getManagerName());
        $this->assertEquals('LG1', $match_manager->getMatch()->getID());
    }

    public function testManagerPerson() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'manager'))), 'manager-person.json');
        $match_manager = $competition->getStage('L')->getGroup('LG')->getMatch('LG1')->getManager();

        $this->assertFalse($match_manager->isTeam());
        $this->assertEquals('Some Manager', $match_manager->getManagerName());
        $this->assertNull($match_manager->getTeamID());
        $this->assertEquals('LG1', $match_manager->getMatch()->getID());
    }

    public function testManagerSetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'manager'))), 'manager-team.json');
        $match_manager = $competition->getStage('L')->getGroup('LG')->getMatch('LG1')->getManager();

        $this->assertTrue($match_manager->isTeam());
        $match_manager->setManagerName('Alan Measles');
        $this->assertEquals('Alan Measles', $match_manager->getManagerName());
        $this->assertFalse($match_manager->isTeam());
        $this->assertNull($match_manager->getTeamID());

        // Note that we _do_ allow a playing team to be a court manager
        $match_manager->setTeamID('TM2');
        $this->assertEquals('TM2', $match_manager->getTeamID());
        $this->assertTrue($match_manager->isTeam());
        $this->assertNull($match_manager->getManagerName());
    }

    public function testManagerConstructor() : void
    {
        $competition = new Competition('test');
        $stage = new Stage($competition, 'S');
        $group = new Crossover($stage, 'G', MatchType::CONTINUOUS);
        $match = new GroupMatch($group, 'M1');

        try {
            new MatchManager($match, null, null);
            $this->fail('MatchManager should require a team or a person');
        } catch (Exception $e) {
            $this->assertEquals('Match Managers must be either a team or a person', $e->getMessage());
        }
    }

    public function testManagerSetName() : void
    {
        $competition = new Competition('test');
        $stage = new Stage($competition, 'S');
        $group = new Crossover($stage, 'G', MatchType::CONTINUOUS);
        $match = new GroupMatch($group, 'M1');
        $manager = new MatchManager($match, null, 'Alice Alison');

        try {
            $manager->setManagerName('');
            $this->fail('MatchManager should catch an empty name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid manager name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        try {
            $name = 'a';
            for ($i=0; $i < 100; $i++) {
                $name .= '0123456789';
            }
            $manager->setManagerName($name);
            $this->fail('MatchManager should catch a long name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid manager name: must be between 1 and 1000 characters long', $e->getMessage());
        }
    }
}
