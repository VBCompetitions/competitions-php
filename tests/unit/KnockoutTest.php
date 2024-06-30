<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\GroupType;
use VBCompetitions\Competitions\Knockout;
use VBCompetitions\Competitions\KnockoutConfig;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(Knockout::class)]
#[CoversClass(KnockoutConfig::class)]
#[CoversClass(Stage::class)]
#[CoversClass(GroupMatch::class)]
final class KnockoutTest extends TestCase {
    public function testKnockout() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'knockout'))), 'complete-knockout.json');
        $knockout = $competition->getStage('KO')->getGroup('CUP');

        if ($knockout instanceof Knockout) {
            $this->assertEquals('1st', $knockout->getKnockoutConfig()->getStanding()[0]->position);
        } else {
            $this->fail('Group should be a knockout');
        }

        $this->assertTrue($knockout->isComplete(), 'Group should be found as completed');
        $this->assertEquals($knockout->getType(), GroupType::KNOCKOUT);
        $this->assertEquals('TM7', $competition->getTeam('{KO:CUP:FIN:winner}')->getID());
        $this->assertEquals('TM6', $competition->getTeam('{KO:CUP:FIN:loser}')->getID());
        $this->assertEquals('TM3', $competition->getTeam('{KO:CUP:PO:winner}')->getID());
        $this->assertEquals('TM2', $competition->getTeam('{KO:CUP:PO:loser}')->getID());

        if ($knockout instanceof Knockout) {
            $knockout_config = $knockout->getKnockoutConfig();
        }
        $this->assertEquals($knockout, $knockout_config->getGroup());
        $this->assertEquals('TM7', $competition->getTeam($knockout_config->getStanding()[0]->id)->getID());
    }

    public function testKnockoutWithSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'knockout'))), 'complete-knockout-sets.json');
        $knockout = $competition->getStage('KO')->getGroup('CUP');

        $this->assertInstanceOf('VBCompetitions\Competitions\Knockout', $knockout, 'Group should be a knockout');

        $this->assertTrue($knockout->isComplete(), 'Group should be found as completed');
        $this->assertEquals($knockout->getType(), GroupType::KNOCKOUT);
        $this->assertEquals('TM7', $competition->getTeam('{KO:CUP:FIN:winner}')->getID());
        $this->assertEquals('TM6', $competition->getTeam('{KO:CUP:FIN:loser}')->getID());
        $this->assertEquals('TM3', $competition->getTeam('{KO:CUP:PO:winner}')->getID());
        $this->assertEquals('TM2', $competition->getTeam('{KO:CUP:PO:loser}')->getID());
    }

    public function testKnockoutIncomplete() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'knockout'))), 'incomplete-knockout.json');
        $knockout = $competition->getStage('KO')->getGroup('CUP');

        $this->assertInstanceOf('VBCompetitions\Competitions\Knockout', $knockout, 'Group should be a knockout');

        $this->assertFalse($knockout->isComplete(), 'Group should be found as incomplete');
    }

    public function testKnockoutDrawsNotAllowed() : void
    {
        $this->expectExceptionMessage('Invalid match information (in match {KO:CUP:QF1}): scores show a draw but draws are not allowed');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'knockout'))), 'knockout-with-drawn-match.json');
    }
}
