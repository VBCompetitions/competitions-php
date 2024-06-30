<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\Crossover;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\GroupType;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(Crossover::class)]
#[CoversClass(Stage::class)]
#[CoversClass(GroupMatch::class)]
final class CrossoverTest extends TestCase {
    public function testCrossover() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'crossovers'))), 'complete-crossover.json');
        $crossover = $competition->getStage('C')->getGroup('CO');

        $this->assertInstanceOf('VBCompetitions\Competitions\Crossover', $crossover, 'Group should be a crossover');

        $this->assertTrue($crossover->isComplete(), 'Group should be found as completed');
        $this->assertEquals($crossover->getType(), GroupType::CROSSOVER);
        $this->assertEquals('TM3', $competition->getTeam('{C:CO:CO1:winner}')->getID());
        $this->assertEquals('TM1', $competition->getTeam('{C:CO:CO1:loser}')->getID());
        $this->assertEquals('TM4', $competition->getTeam('{C:CO:CO2:winner}')->getID());
        $this->assertEquals('TM2', $competition->getTeam('{C:CO:CO2:loser}')->getID());
    }

    public function testCrossoverWithSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'crossovers'))), 'complete-crossover-sets.json');
        $crossover = $competition->getStage('C')->getGroup('CO');

        $this->assertInstanceOf('VBCompetitions\Competitions\Crossover', $crossover, 'Group should be a crossover');

        $this->assertTrue($crossover->isComplete(), 'Group should be found as completed');
        $this->assertEquals('TM3', $competition->getTeam('{C:CO:CO1:winner}')->getID());
        $this->assertEquals('TM1', $competition->getTeam('{C:CO:CO1:loser}')->getID());
        $this->assertEquals('TM4', $competition->getTeam('{C:CO:CO2:winner}')->getID());
        $this->assertEquals('TM2', $competition->getTeam('{C:CO:CO2:loser}')->getID());
    }

    public function testCrossoverIncomplete() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'crossovers'))), 'incomplete-crossover.json');
        $crossover = $competition->getStage('C')->getGroup('CO');

        $this->assertInstanceOf('VBCompetitions\Competitions\Crossover', $crossover, 'Group should be a crossover');

        $this->assertFalse($crossover->isComplete(), 'Group should be found as incomplete');
    }

    public function testCrossoverDrawsNotAllowed() : void
    {
        $this->expectExceptionMessage('Invalid match information (in match {C:CO:CO1}): scores show a draw but draws are not allowed');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'crossovers'))), 'crossover-with-drawn-match.json');
    }

    public function testCrossoverGroupsWithoutNames() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'crossovers'))), 'crossover-no-names.json');
        $crossover = $competition->getStage('C');

        $this->assertNull($crossover->getGroup('CO0')->getName());
        $this->assertEquals('Crossover round', $crossover->getGroup('CO1')->getName());
        $this->assertNull($crossover->getGroup('CO2')->getName());
    }
}
