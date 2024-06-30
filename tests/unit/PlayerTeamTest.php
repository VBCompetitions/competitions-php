<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Player;
use VBCompetitions\Competitions\PlayerTeam;

#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(Player::class)]
#[CoversClass(PlayerTeam::class)]
final class PlayerTeamTest extends TestCase {
    public function testPlayerTeamSetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');

        $player1 = $competition->getPlayer('P1');
        $player1_team_entries = $player1->getTeamEntries();

        $this->assertEquals($player1, $player1_team_entries[0]->getPlayer());
        $this->assertEquals('TM2', $player1_team_entries[0]->getID());
        $this->assertEquals('2023-09-01', $player1_team_entries[0]->getFrom());
        $this->assertEquals('2024-01-14', $player1_team_entries[0]->getUntil());
        $this->assertEquals(null, $player1_team_entries[0]->getNotes());
        $this->assertEquals($player1, $player1_team_entries[1]->getPlayer());
        $this->assertEquals('TM3', $player1_team_entries[1]->getID());
        $this->assertEquals('2024-01-14', $player1_team_entries[1]->getFrom());
        $this->assertEquals(null, $player1_team_entries[1]->getUntil());
        $this->assertEquals('some notes', $player1_team_entries[1]->getNotes());

        $competition = new Competition('test');
        $player = new Player($competition, 'P1', 'Alice Alison');
        $player_team = new PlayerTeam($player, 'T1');

        try {
            $player_team->setFrom('Today');
            $this->fail('PlayerTeam should not allow a bad from date');
        } catch (Exception $e) {
            $this->assertEquals('Invalid date "Today": must contain a value of the form "YYYY-MM-DD"', $e->getMessage());
        }

        try {
            $player_team->setFrom('2024-02-30');
            $this->fail('PlayerTeam should not allow a non-existent from date');
        } catch (Exception $e) {
            $this->assertEquals('Invalid date "2024-02-30": date does not exist', $e->getMessage());
        }

        try {
            $player_team->setUntil('Today');
            $this->fail('PlayerTeam should not allow a bad until date');
        } catch (Exception $e) {
            $this->assertEquals('Invalid date "Today": must contain a value of the form "YYYY-MM-DD"', $e->getMessage());
        }

        try {
            $player_team->setUntil('2024-02-30');
            $this->fail('PlayerTeam should not allow a non-existent until date');
        } catch (Exception $e) {
            $this->assertEquals('Invalid date "2024-02-30": date does not exist', $e->getMessage());
        }

        $player_team->setNotes('TODO');
    }

    public function testPlayerTeamConstructor() : void
    {
        $competition = new Competition('test');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        $competition->addTeam($team);
        $player = new Player($competition, 'P1', 'Alice Alison');
        $competition->addPlayer($player);

        try {
          new PlayerTeam($player, '');
          $this->fail('PlayerTeam constructor should not allow a short team id');
        } catch (Exception $e) {
          $this->assertEquals('Invalid team ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
          new PlayerTeam($player, '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891');
          $this->fail('PlayerTeam constructor should not allow a long team ID');
        } catch (Exception $e) {
          $this->assertEquals('Invalid team ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
          new PlayerTeam($player, '"id1"');
          $this->fail('PlayerTeam constructor should not allow a player ID with invalid characters');
        } catch (Exception $e) {
          $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
          new PlayerTeam($player, 'id:1');
          $this->fail('PlayerTeam constructor should not allow a player ID with invalid characters');
        } catch (Exception $e) {
          $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
          new PlayerTeam($player, 'id{1');
          $this->fail('PlayerTeam constructor should not allow a player ID with invalid characters');
        } catch (Exception $e) {
          $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
          new PlayerTeam($player, 'id1}');
          $this->fail('PlayerTeam constructor should not allow a player ID with invalid characters');
        } catch (Exception $e) {
          $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
          new PlayerTeam($player, 'id1?');
          $this->fail('PlayerTeam constructor should not allow a player ID with invalid characters');
        } catch (Exception $e) {
          $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
          new PlayerTeam($player, 'id=1');
          $this->fail('PlayerTeam constructor should not allow a player ID with invalid characters');
        } catch (Exception $e) {
          $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        $player_team = new PlayerTeam($player, 'T1');
        $player->appendTeamEntry($player_team);
        $player_team->setFrom('2023-10-03');
        $player_team->setFrom('2023-10-04');
        $player_team->setUntil('2024-06-28');
        $player_team->setUntil('2024-06-29');
        $player_team->setNotes('non-professional contract');
        $this->assertTrue($competition->hasPlayerInTeam('P1', 'T1'));
        $this->assertEquals('T1', $player_team->getID());
        $this->assertEquals('2023-10-04', $player_team->getFrom());
        $this->assertEquals('2024-06-29', $player_team->getUntil());
        $this->assertEquals('non-professional contract', $player_team->getNotes());
    }
}
