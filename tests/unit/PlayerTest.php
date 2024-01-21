<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Player;

#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(Player::class)]
final class PlayerTest extends TestCase {
    public function testPlayersNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');

        $team = $competition->getTeamByID('TM1');
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team);
        $this->assertEquals(0, count($team->getPlayers()), 'Team 1 should have no players defined');
    }

    public function testPlayersDuplicateID() : void
    {
        $this->expectExceptionMessage('Competition data failed validation: team players with duplicate IDs within a team not allowed');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players-duplicate-ids.json');
    }

    public function testPlayersGetByID() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');
        $team = $competition->getTeamByID('TM3');

        $this->assertEquals('Alice Alison', $team->getPlayerByID('P1')->name);
        $this->assertEquals('junior', $team->getPlayerByID('P1')->notes);
        $this->assertEquals('Bobby Bobs', $team->getPlayerByID('P2')->name);
        $this->assertEquals('Charlie Charleston', $team->getPlayerByID('P3')->name);
        $this->assertEquals(7, $team->getPlayerByID('P3')->number);
        $this->assertEquals('Dave Davidson', $team->getPlayerByID('P4')->name);
        $this->assertEquals('Emma Emerson', $team->getPlayerByID('P5')->name);
        $this->assertEquals('Frankie Frank', $team->getPlayerByID('P6')->name);

        $team = $competition->getTeamByID('TM2');
        $this->assertNull($team->getPlayerByID('P1')->number);
        $this->assertNull($team->getPlayerByID('P1')->notes);
    }

    public function testPlayersGetByIDOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Player with ID NO-SUCH-TEAM not found');
        $competition->getTeamByID('TM1')->getPlayerByID('NO-SUCH-TEAM');
    }
}
