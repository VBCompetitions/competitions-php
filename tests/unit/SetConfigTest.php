<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use stdClass;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\SetConfig;

#[CoversClass(SetConfig::class)]
final class SetConfigTest extends TestCase {
    public function testSetConfig() : void
    {
        $config = new SetConfig(json_decode('{"maxSets": 3, "setsToWin": 1, "clearPoints": 2, "minPoints": 1, "pointsToWin": 21, "lastSetPointsToWin": 12, "maxPoints": 50, "lastSetMaxPoints": 30}'));

        $this->assertEquals(3, $config->getMaxSets());
        $this->assertEquals(1, $config->getSetsToWin());
        $this->assertEquals(2, $config->getClearPoints());
        $this->assertEquals(1, $config->getMinPoints());
        $this->assertEquals(21, $config->getPointsToWin());
        $this->assertEquals(12, $config->getLastSetPointsToWin());
        $this->assertEquals(50, $config->getMaxPoints());
        $this->assertEquals(30, $config->getLastSetMaxPoints());
    }

    public function testSetConfigNull() : void
    {
        $config = new SetConfig(null);

        $this->assertEquals(5, $config->getMaxSets());
        $this->assertEquals(3, $config->getSetsToWin());
        $this->assertEquals(2, $config->getClearPoints());
        $this->assertEquals(1, $config->getMinPoints());
        $this->assertEquals(25, $config->getPointsToWin());
        $this->assertEquals(15, $config->getLastSetPointsToWin());
        $this->assertEquals(1000, $config->getMaxPoints());
        $this->assertEquals(1000, $config->getLastSetMaxPoints());
    }

    public function testSetConfigEmpty() : void
    {
        $config = new SetConfig(new stdClass);

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

