<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Club;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\TeamContact;
use VBCompetitions\Competitions\TeamContactRole;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Player;
use VBCompetitions\Competitions\PlayerTeam;

#[CoversClass(Club::class)]
#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(TeamContact::class)]
#[CoversClass(Player::class)]
#[CoversClass(PlayerTeam::class)]
final class CompetitionTeamTest extends TestCase {
    public function testCompetitionTeamDuplicateID() : void
    {
        $competition = new Competition('test competition');
        $team_1 = new CompetitionTeam($competition, 'T1', 'team 1');
        $competition->addTeam($team_1);
        try {
            new CompetitionTeam($competition, 'T1', 'team 2');
            $this->fail('CompetitionTeam should not allow duplicate IDs');
        } catch (Exception $e) {
            $this->assertEquals('Team with ID "T1" already exists in the competition', $e->getMessage());
        }
    }

    public function testCompetitionTeamName() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');

        try {
            $team->setName('');
            $this->fail('CompetitionTeam should not allow a zero length name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        try {
            $name = 'a';
            for ($i=0; $i < 100; $i++) {
                $name .= '0123456789';
            }
            $team->setName($name);
            $this->fail('CompetitionTeam should not allow a very long name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        $this->assertEquals('Team 1', $team->getName());
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team->setName('Team A'));
        $this->assertEquals('Team A', $team->getName());
    }

    public function testCompetitionTeamSetClub() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        $club = new Club($competition, 'C1', 'Club 1');
        $competition->addTeam($team)->addClub($club);

        try {
            $team->setClubID('C2');
        } catch (Exception $e) {
            $this->assertEquals('No club with ID "C2" exists', $e->getMessage());
        }

        $this->assertFalse($team->hasClub());
        $team->setClubID('C1');
        $this->assertEquals('C1', $team->getClub()->getID());
        $this->assertTrue($team->hasClub());
        $team->setClubID(null);
        $this->assertFalse($team->hasClub());
    }

    public function testCompetitionTeamNotes() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');

        $this->assertNull($team->getNotes());
        $this->assertFalse($team->hasNotes());
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team->setNotes('Some notes on the team'));
        $this->assertEquals('Some notes on the team', $team->getNotes());
        $this->assertTrue($team->hasNotes());
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team->setNotes(null));
        $this->assertNull($team->getNotes());
        $this->assertFalse($team->hasNotes());
    }

    public function testCompetitionTeamContacts() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        $contact = new TeamContact($team, 'C1', [TeamContactRole::SECRETARY]);

        $this->assertCount(0, $team->getContacts());
        $this->assertFalse($team->hasContacts());
        $this->assertFalse($team->hasContact('C1'));

        $team->addContact($contact);
        $this->assertCount(1, $team->getContacts());
        $this->assertTrue($team->hasContacts());
        $this->assertTrue($team->hasContact('C1'));
        $this->assertEquals(TeamContactRole::SECRETARY, $team->getContact('C1')->getRoles()[0]);

        try {
            $team->addContact($contact);
            $this->fail('CompetitionTeam should not allow a contact with a duplicate ID');
        } catch (Exception $e) {
            $this->assertEquals('team contacts with duplicate IDs within a team not allowed', $e->getMessage());
        }
        $this->assertCount(1, $team->getContacts());
        $this->assertTrue($team->hasContacts());
        $this->assertTrue($team->hasContact('C1'));

        $team->deleteContact('C1');
        try {
            $team->getContact('C1');
            $this->fail('CompetitionTeam should throw when getting a non-existant contact');
        } catch (Exception $e) {
            $this->assertEquals('Contact with ID "C1" not found', $e->getMessage());
        }
        $this->assertCount(0, $team->getContacts());
        $this->assertFalse($team->hasContacts());
        $this->assertFalse($team->hasContact('C1'));

        $team->deleteContact('C1');
        $this->assertCount(0, $team->getContacts());
        $this->assertFalse($team->hasContacts());
        $this->assertFalse($team->hasContact('C1'));
    }

    public function testCompetitionTeamPlayers() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        $competition->addTeam($team);
        $player = new Player($competition, 'P1', 'Player 1');
        $competition->addPlayer($player);
        $player_team = new PlayerTeam($player, 'T1');

        $this->assertCount(0, $team->getPlayers());
        $this->assertFalse($team->hasPlayers());
        $this->assertFalse($team->hasPlayer('P1'));

        $player->appendTeamEntry($player_team);
        $this->assertCount(1, $team->getPlayers());
        $this->assertTrue($team->hasPlayers());
        $this->assertTrue($team->hasPlayer('P1'));
        $this->assertCount(1, $competition->getPlayers());
        $this->assertTrue($competition->hasPlayers());
        $this->assertTrue($competition->hasPlayer('P1'));
        $this->assertEquals('Player 1', $competition->getPlayer('P1')->getName());

        try {
            $competition->addPlayer($player);
            $this->fail('Competition should not allow a player with a duplicate ID');
        } catch (Exception $e) {
            $this->assertEquals('players with duplicate IDs within a competition not allowed', $e->getMessage());
        }
        $this->assertCount(1, $team->getPlayers());
        $this->assertTrue($team->hasPlayers());
        $this->assertTrue($team->hasPlayer('P1'));
        $this->assertCount(1, $competition->getPlayers());
        $this->assertTrue($competition->hasPlayers());
        $this->assertTrue($competition->hasPlayer('P1'));

        $competition->deletePlayer('P1');
        try {
            $competition->getPlayer('P1');
            $this->fail('Competition should throw when getting a non-existant player');
        } catch (Exception $e) {
            $this->assertEquals('Player with ID "P1" not found', $e->getMessage());
        }
        $this->assertCount(0, $team->getPlayers());
        $this->assertFalse($team->hasPlayers());
        $this->assertFalse($team->hasPlayer('P1'));
        $this->assertCount(0, $competition->getPlayers());
        $this->assertFalse($competition->hasPlayers());
        $this->assertFalse($competition->hasPlayer('P1'));

        $competition->deletePlayer('P1');
        $this->assertCount(0, $team->getPlayers());
        $this->assertFalse($team->hasPlayers());
        $this->assertFalse($team->hasPlayer('P1'));
        $this->assertCount(0, $competition->getPlayers());
        $this->assertFalse($competition->hasPlayers());
        $this->assertFalse($competition->hasPlayer('P1'));
    }

    public function testCompetitionTeamConstructorBadID() : void
    {
        $competition = new Competition('test competition');
        try {
            new CompetitionTeam($competition, '', 'my team');
            $this->fail('CompetitionTeam should not allow an empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new CompetitionTeam($competition, '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891', 'my team');
            $this->fail('CompetitionTeam should not allow a long ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new CompetitionTeam($competition, '"id1"', 'my team');
            $this->fail('CompetitionTeam should not allow " character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionTeam($competition, 'id:1', 'my team');
            $this->fail('CompetitionTeam should not allow : character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionTeam($competition, 'id{1', 'my team');
            $this->fail('CompetitionTeam should not allow { character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionTeam($competition, 'id1}', 'my team');
            $this->fail('CompetitionTeam should not allow } character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionTeam($competition, 'id1?', 'my team');
            $this->fail('CompetitionTeam should not allow ? character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionTeam($competition, 'id=1', 'my team');
            $this->fail('CompetitionTeam should not allow = character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }
    }
}
