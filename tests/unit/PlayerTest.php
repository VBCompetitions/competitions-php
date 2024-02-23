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
        $this->assertFalse($team->hasPlayers(), 'Team 1 should have no players defined');
    }

    public function testPlayersDuplicateID() : void
    {
        $this->expectExceptionMessage('team players with duplicate IDs within a team not allowed');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players-duplicate-ids.json');
    }

    public function testPlayersGetByID() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');
        $team = $competition->getTeamByID('TM3');

        $this->assertEquals('Alice Alison', $team->getPlayerByID('P1')->getName());
        $this->assertEquals('junior', $team->getPlayerByID('P1')->getNotes());
        $this->assertEquals('Bobby Bobs', $team->getPlayerByID('P2')->getName());
        $this->assertEquals('Charlie Charleston', $team->getPlayerByID('P3')->getName());
        $this->assertEquals(7, $team->getPlayerByID('P3')->getNumber());
        $this->assertEquals('Dave Davidson', $team->getPlayerByID('P4')->getName());
        $this->assertEquals('Emma Emerson', $team->getPlayerByID('P5')->getName());
        $this->assertEquals('Frankie Frank', $team->getPlayerByID('P6')->getName());

        $team = $competition->getTeamByID('TM2');
        $this->assertNull($team->getPlayerByID('P1')->getNumber());
        $this->assertNull($team->getPlayerByID('P1')->getNotes());
    }

    public function testPlayersGetByIDOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Player with ID NO-SUCH-TEAM not found');
        $competition->getTeamByID('TM1')->getPlayerByID('NO-SUCH-TEAM');
    }

    public function testPlayersSetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');
        $team = $competition->getTeamByID('TM3');

        $player1 = $team->getPlayerByID('P1');

        $this->assertEquals('Alice Alison', $player1->getName());
        $this->assertEquals(1, $player1->getNumber());
        $this->assertEquals('junior', $player1->getNotes());

        $player1->setName('Alison Alison');
        $player1->setNumber(10);
        $player1->setNotes('no longer junior');

        $this->assertEquals('Alison Alison', $player1->getName());
        $this->assertEquals(10, $player1->getNumber());
        $this->assertEquals('no longer junior', $player1->getNotes());
    }

    // public function testPlayersSameNumberThrowsOnLoad() : void
    // {
    //     $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');
    //     $team = $competition->getTeamByID('TM3');

    //     // TODO loading file with dupe numbers fails validation
    // }

    // public function testPlayersSameNumberThrowsOnSet() : void
    // {
    //     $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');
    //     $team = $competition->getTeamByID('TM3');

    //     // TODO setting players to have dupe numbers fails on set
    //     // PHPDoc should include @throws
    // }
}
