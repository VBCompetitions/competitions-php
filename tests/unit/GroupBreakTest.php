<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\GroupBreak;

#[CoversClass(GroupBreak::class)]
final class GroupBreakTest extends TestCase {
    public function testGroupBreakBasicData() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'complete-group.json');
        $group = $competition->getStageById('L')->getGroupById('LG');
        $break = null;
        foreach ($group->getMatches() as $match) {
            if ($match instanceof GroupBreak) {
                $break = $match;
            }
        }

        $this->assertNotNull($break);
        $this->assertEquals('13:20', $break->getStart());
        $this->assertEquals('2020-02-20', $break->getDate());
        $this->assertEquals('1:00', $break->getDuration());
        $this->assertEquals('Lunch break', $break->getName());
        $this->assertEquals('LG', $break->getGroup()->getID());
    }
}
