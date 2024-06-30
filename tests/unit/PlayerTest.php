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

        $team = $competition->getTeam('TM1');
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team);
        $this->assertFalse($team->hasPlayers(), 'Team 1 should have no players defined');
    }

    public function testPlayerGetByID() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');
        $team = $competition->getTeam('TM3');

        $this->assertEquals($competition->getPlayer('P1')->getCompetition(), $competition);
        $this->assertEquals('Alice Alison', $competition->getPlayer('P1')->getName());
        $this->assertEquals('junior', $competition->getPlayer('P1')->getNotes());
        $this->assertEquals('Bobby Bobs', $competition->getPlayer('P2')->getName());
        $this->assertEquals('Charlie Charleston', $competition->getPlayer('P3')->getName());
        $this->assertEquals(7, $competition->getPlayer('P3')->getNumber());
        $this->assertEquals('Dave Davidson', $competition->getPlayer('P4')->getName());
        $this->assertEquals('Emma Emerson', $competition->getPlayer('P5')->getName());
        $this->assertEquals('Frankie Frank', $competition->getPlayer('P6')->getName());
        $this->assertEquals($team->getID(), $competition->getPlayer('P6')->getCurrentTeam()->getID());

        $this->assertEquals($competition->getPlayer('P7')->getNumber(), null);
        $this->assertEquals($competition->getPlayer('P7')->getNotes(), null);
        $this->assertEquals($competition->getPlayer('P8')->getCurrentTeam()->getID(), CompetitionTeam::UNKNOWN_TEAM_ID);
        }

    public function testPlayerGetByIDOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Player with ID "NO-SUCH-PLAYER" not found');
        $competition->getPlayer('NO-SUCH-PLAYER');
    }

    public function testPlayerSetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');

        $player1 = $competition->getPlayer('P1');

        $this->assertEquals('Alice Alison', $player1->getName());
        $this->assertEquals(1, $player1->getNumber());
        $this->assertEquals('junior', $player1->getNotes());
        $this->assertFalse($player1->hasTeamEntry('TM1'));
        $this->assertTrue($player1->hasTeamEntry('TM2'));
        $this->assertTrue($player1->hasTeamEntry('TM3'));

        $this->assertEquals($competition->getPlayer('P8')->getLatestTeamEntry(), null);

        $player1->setName('Alison Alison');
        $this->assertEquals('Alison Alison', $player1->getName());

        $player1->setNumber(10);
        $this->assertEquals(10, $player1->getNumber());

        $player1->setNotes('no longer junior');
        $this->assertEquals('no longer junior', $player1->getNotes());

        $player1->spliceTeamEntries(0, 1);
        $this->assertFalse($player1->hasTeamEntry('TM1'));
        $this->assertFalse($player1->hasTeamEntry('TM2'));
        $this->assertTrue($player1->hasTeamEntry('TM3'));

        $competition = new Competition('test');
        $player = new Player($competition, 'P1', 'Alice Alison');

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

        try {
            new Player($competition, '', 'Alice Alison');
            $this->fail('Player should catch empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new Player($competition, '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891', 'Alice Alison');
            $this->fail('Player should not allow a long ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new Player($competition, '"id1"', 'Alice Alison');
            $this->fail('Player should not allow " character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($competition, 'id:1', 'Alice Alison');
            $this->fail('Player should not allow : character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($competition, 'id{1', 'Alice Alison');
            $this->fail('Player should not allow { character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($competition, 'id1}', 'Alice Alison');
            $this->fail('Player should not allow } character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($competition, 'id1?', 'Alice Alison');
            $this->fail('Player should not allow ? character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Player($competition, 'id=1', 'Alice Alison');
            $this->fail('Player should not allow = character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        $player1 = new Player($competition, 'P1', 'Alice Alison');
        $competition->addPlayer($player1);

        try {
            new Player($competition, 'P1', 'Bobby Bobs');
            $this->fail('Teams should not allow duplicate players');
        } catch (Exception $e) {
            $this->assertEquals('Player with ID "P1" already exists in the competition', $e->getMessage());
        }
    }
}
