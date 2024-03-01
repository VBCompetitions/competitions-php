<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
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
    public function testPlayerNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');

        $team = $competition->getTeamByID('TM1');
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team);
        $this->assertFalse($team->hasPlayers(), 'Team 1 should have no players defined');
    }

    public function testPlayerDuplicateID() : void
    {
        $this->expectExceptionMessage('Player with ID "P1" already exists in the team');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players-duplicate-ids.json');
    }

    public function testPlayerGetByID() : void
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

    public function testPlayerGetByIDOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Player with ID "NO-SUCH-TEAM" not found');
        $competition->getTeamByID('TM1')->getPlayerByID('NO-SUCH-TEAM');
    }

    public function testPlayerSetters() : void
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

        $competition = new Competition('test');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        $player = new Player($team, 'P1', 'Alice Alison');

        try {
            $player->setName('');
            $this->fail('Player should catch empty Name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        $name = 'a';
        for ($i=0; $i < 100; $i++) {
            $name .= '0123456789';
        }
        try {
            $player->setName($name);
            $this->fail('Player should not allow a long Name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        try {
            $player->setNumber(-1);
            $this->fail('Player should not allow a negative shirt number');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player number "-1": must be greater than 1', $e->getMessage());
        }
    }

    public function testPlayerConstructor() : void
    {
        $competition = new Competition('test');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');

        try {
            new Player($team, '', 'Alice Alison');
            $this->fail('Player should catch empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new Player($team, '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891', 'Alice Alison');
            $this->fail('Player should not allow a long ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new Player($team, '"id1"', 'Alice Alison');
            $this->fail('Player should not allow " character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($team, 'id:1', 'Alice Alison');
            $this->fail('Player should not allow : character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($team, 'id{1', 'Alice Alison');
            $this->fail('Player should not allow { character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($team, 'id1}', 'Alice Alison');
            $this->fail('Player should not allow } character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($team, 'id1?', 'Alice Alison');
            $this->fail('Player should not allow ? character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($team, 'id=1', 'Alice Alison');
            $this->fail('Player should not allow = character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        $player1 = new Player($team, 'P1', 'Alice Alison');
        $team->addPlayer($player1);

        try {
            new Player($team, 'P1', 'Bobby Bobs');
            $this->fail('Teams should not allow duplicate players');
        } catch (Exception $e) {
            $this->assertEquals('Player with ID "P1" already exists in the team', $e->getMessage());
        }
    }
}
