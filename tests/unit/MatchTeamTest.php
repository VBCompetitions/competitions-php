<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\MatchTeam;

#[CoversClass(Competition::class)]
#[CoversClass(GroupMatch::class)]
#[CoversClass(MatchTeam::class)]
final class MatchTeamTest extends TestCase {
    public function testMatchTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matchteam'))), 'competition.json');
        $match = $competition->getStage('L')->getGroup('RL')->getMatches()[0];
        $this->assertInstanceOf('VBCompetitions\Competitions\MatchInterface', $match);

        $home_team = $match->getHomeTeam();
        $away_team = $match->getAwayTeam();

        $this->assertEquals('A Alice', $home_team->getMVP()->getName());
        $this->assertEquals('B Bobs', $away_team->getMVP()->getName());
        $this->assertEquals('Some team note', $home_team->getNotes());
        $this->assertEquals(1, count($home_team->getPlayers()));
        $this->assertEquals('P1', $home_team->getPlayers()[0]->getID());
        $this->assertEquals('RLM1', $home_team->getMatch()->getID());
    }

    public function testMatchTeamMixedPlayerTypes() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'matchteam'))), 'competition-player-types.json');
        $competition_reloaded = Competition::loadFromCompetitionJSON(json_encode($competition));

        $this->assertEquals(
            $competition->getStage('S')->getGroup('C')->getMatch('M1')->getMVP()->getID(),
            $competition_reloaded->getStage('S')->getGroup('C')->getMatch('M1')->getMVP()->getID()
        );
        $this->assertEquals(
            $competition->getStage('S')->getGroup('C')->getMatch('M1')->getHomeTeam()->getMVP()->getID(),
            $competition_reloaded->getStage('S')->getGroup('C')->getMatch('M1')->getHomeTeam()->getMVP()->getID()
        );
        $this->assertEquals(
            $competition->getStage('S')->getGroup('C')->getMatch('M1')->getAwayTeam()->getMVP()->getID(),
            $competition_reloaded->getStage('S')->getGroup('C')->getMatch('M1')->getAwayTeam()->getMVP()->getID()
        );
        $this->assertEquals(
            $competition->getStage('S')->getGroup('C')->getMatch('M1')->getHomeTeam()->getPlayers()[0],
            $competition_reloaded->getStage('S')->getGroup('C')->getMatch('M1')->getHomeTeam()->getPlayers()[0]
        );

        $this->assertEquals(
            $competition->getStage('S')->getGroup('S')->getMatch('M1')->getMVP()->getID(),
            $competition_reloaded->getStage('S')->getGroup('S')->getMatch('M1')->getMVP()->getID()
        );
        $this->assertEquals(
            $competition->getStage('S')->getGroup('S')->getMatch('M1')->getHomeTeam()->getMVP()->getID(),
            $competition_reloaded->getStage('S')->getGroup('S')->getMatch('M1')->getHomeTeam()->getMVP()->getID()
        );
        $this->assertEquals(
            $competition->getStage('S')->getGroup('S')->getMatch('M1')->getAwayTeam()->getMVP()->getID(),
            $competition_reloaded->getStage('S')->getGroup('S')->getMatch('M1')->getAwayTeam()->getMVP()->getID()
        );
        $this->assertEquals(
            $competition->getStage('S')->getGroup('S')->getMatch('M1')->getHomeTeam()->getPlayers()[0],
            $competition_reloaded->getStage('S')->getGroup('S')->getMatch('M1')->getHomeTeam()->getPlayers()[0]
        );
    }
}

