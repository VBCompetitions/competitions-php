<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\MatchTeam;

#[CoversClass(Competition::class)]
#[CoversClass(MatchTeam::class)]
final class MatchTeamTest extends TestCase {
    public function testMatchTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matchteam'))), 'competition.json');
        $match = $competition->getStageById('L')->getGroupById('RL')->getMatches()[0];
        $this->assertInstanceOf('VBCompetitions\Competitions\MatchInterface', $match);

        $home_team = $match->getHomeTeam();
        $away_team = $match->getAwayTeam();

        $this->assertEquals('A Alice', $home_team->getMVP());
        $this->assertEquals('B Bobs', $away_team->getMVP());
        $this->assertEquals('Some team note', $home_team->getNotes());
        $this->assertEquals(1, count($home_team->getPlayers()));
        $this->assertEquals('P1', $home_team->getPlayers()[0]);
        $this->assertEquals('RLM1', $home_team->getMatch()->getID());
    }
}

