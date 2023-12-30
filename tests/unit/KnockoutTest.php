<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\GroupType;
use VBCompetitions\Competitions\Knockout;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(Knockout::class)]
#[CoversClass(Stage::class)]
#[CoversClass(GroupMatch::class)]
final class KnockoutTest extends TestCase {
    public function testKnockout() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'knockout'))), 'complete-knockout.json');
        $knockout = $competition->getStageById('KO')->getGroupById('CUP');

        if ($knockout instanceof Knockout) {
            $this->assertEquals('1st', $knockout->getKnockoutConfig()->standing[0]->position);
        } else {
            $this->fail('Group should be a knockout');
        }

        $this->assertTrue($knockout->isComplete(), 'Group should be found as completed');
        $this->assertEquals($knockout->getType(), GroupType::KNOCKOUT);
        $this->assertEquals('TM7', $competition->getTeamByID('{KO:CUP:FIN:winner}')->getID());
        $this->assertEquals('TM6', $competition->getTeamByID('{KO:CUP:FIN:loser}')->getID());
        $this->assertEquals('TM3', $competition->getTeamByID('{KO:CUP:PO:winner}')->getID());
        $this->assertEquals('TM2', $competition->getTeamByID('{KO:CUP:PO:loser}')->getID());

    }

    public function testKnockoutWithSets() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'knockout'))), 'complete-knockout-sets.json');
        $knockout = $competition->getStageById('KO')->getGroupById('CUP');

        $this->assertInstanceOf('VBCompetitions\Competitions\Knockout', $knockout, 'Group should be a knockout');

        $this->assertTrue($knockout->isComplete(), 'Group should be found as completed');
        $this->assertEquals($knockout->getType(), GroupType::KNOCKOUT);
        $this->assertEquals('TM7', $competition->getTeamByID('{KO:CUP:FIN:winner}')->getID());
        $this->assertEquals('TM6', $competition->getTeamByID('{KO:CUP:FIN:loser}')->getID());
        $this->assertEquals('TM3', $competition->getTeamByID('{KO:CUP:PO:winner}')->getID());
        $this->assertEquals('TM2', $competition->getTeamByID('{KO:CUP:PO:loser}')->getID());
    }

    public function testKnockoutIncomplete() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'knockout'))), 'incomplete-knockout.json');
        $knockout = $competition->getStageById('KO')->getGroupById('CUP');

        $this->assertInstanceOf('VBCompetitions\Competitions\Knockout', $knockout, 'Group should be a knockout');

        $this->assertFalse($knockout->isComplete(), 'Group should be found as incomplete');
    }

    public function testKnockoutDrawsNotAllowed() : void
    {
        $this->expectExceptionMessage('Invalid match information (in match {KO:CUP:QF1}): scores show a draw but draws are not allowed');
        new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'knockout'))), 'knockout-with-drawn-match.json');
    }
}
