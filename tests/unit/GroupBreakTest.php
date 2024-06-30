<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\Crossover;
use VBCompetitions\Competitions\GroupBreak;
use VBCompetitions\Competitions\MatchType;
use VBCompetitions\Competitions\Stage;

#[CoversClass(GroupBreak::class)]
final class GroupBreakTest extends TestCase {
    public function testGroupBreakBasicData() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'groups'))), 'complete-group.json');
        $group = $competition->getStage('L')->getGroup('LG');
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

    public function testGroupBreakSetters() : void
    {
        $competition = new Competition('test competition');
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $group = new Crossover($stage, 'C', MatchType::SETS);
        $stage->addGroup($group);

        $lunch_break = new GroupBreak($group);

        try {
            $lunch_break->setDate('Today');
            $this->fail('GroupBreak should not allow a bad date');
        } catch (Exception $e) {
            $this->assertEquals('Invalid date "Today": must contain a value of the form "YYYY-MM-DD"', $e->getMessage());
        }

        try {
            $lunch_break->setDate('2024-02-30');
            $this->fail('GroupBreak should not allow a non-existent date');
        } catch (Exception $e) {
            $this->assertEquals('Invalid date "2024-02-30": date does not exist', $e->getMessage());
        }

        try {
            $lunch_break->setStart('This afternoon');
            $this->fail('GroupBreak should not allow a bad start time');
        } catch (Exception $e) {
            $this->assertEquals('Invalid start time "This afternoon": must contain a value of the form "HH:mm" using a 24 hour clock', $e->getMessage());
        }

        try {
            $lunch_break->setDuration('20 minutes');
            $this->fail('GroupBreak should not allow a bad duration');
        } catch (Exception $e) {
            $this->assertEquals('Invalid duration "20 minutes": must contain a value of the form "HH:mm"', $e->getMessage());
        }

        try {
            $lunch_break->setName('');
            $this->fail('GroupBreak should not allow a bad name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid break name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        try {
            $name = 'a';
            for ($i=0; $i < 100; $i++) {
                $name .= '0123456789';
            }
            $lunch_break->setName($name);
            $this->fail('GroupBreak should not allow a long Name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid break name: must be between 1 and 1000 characters long', $e->getMessage());
        }
        $this->assertNull($lunch_break->getName());
    }
}
