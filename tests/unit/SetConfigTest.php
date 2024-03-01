<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use stdClass;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\Crossover;
use VBCompetitions\Competitions\MatchType;
use VBCompetitions\Competitions\SetConfig;
use VBCompetitions\Competitions\Stage;

#[CoversClass(SetConfig::class)]
final class SetConfigTest extends TestCase {
    public function testSetConfig() : void
    {
        $dummy_competition = new Competition('dummy for score update');
        $dummy_stage = new Stage($dummy_competition, 'S');
        $dummy_group = new Crossover($dummy_stage, 'G', MatchType::SETS);
        $config = new SetConfig($dummy_group);
        $config->loadFromData(json_decode('{"maxSets": 3, "setsToWin": 1, "clearPoints": 2, "minPoints": 1, "pointsToWin": 21, "lastSetPointsToWin": 12, "maxPoints": 50, "lastSetMaxPoints": 30}'));

        $this->assertEquals(3, $config->getMaxSets());
        $this->assertEquals(1, $config->getSetsToWin());
        $this->assertEquals(2, $config->getClearPoints());
        $this->assertEquals(1, $config->getMinPoints());
        $this->assertEquals(21, $config->getPointsToWin());
        $this->assertEquals(12, $config->getLastSetPointsToWin());
        $this->assertEquals(50, $config->getMaxPoints());
        $this->assertEquals(30, $config->getLastSetMaxPoints());
    }

    public function testSetConfigEmpty() : void
    {
        $dummy_competition = new Competition('dummy for score update');
        $dummy_stage = new Stage($dummy_competition, 'S');
        $dummy_group = new Crossover($dummy_stage, 'G', MatchType::SETS);
        $config = new SetConfig($dummy_group);
        $config->loadFromData(new stdClass);

        $this->assertEquals($dummy_group, $config->getGroup());
        $this->assertEquals(5, $config->getMaxSets());
        $this->assertEquals(3, $config->getSetsToWin());
        $this->assertEquals(2, $config->getClearPoints());
        $this->assertEquals(1, $config->getMinPoints());
        $this->assertEquals(25, $config->getPointsToWin());
        $this->assertEquals(15, $config->getLastSetPointsToWin());
        $this->assertEquals(1000, $config->getMaxPoints());
        $this->assertEquals(1000, $config->getLastSetMaxPoints());
    }
}

