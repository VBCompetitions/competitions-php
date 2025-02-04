<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Club;
use VBCompetitions\Competitions\ClubContact;
use VBCompetitions\Competitions\ClubContactRole;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;

#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(Club::class)]
final class ClubTest extends TestCase {
    public function testClubNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');

        $team = $competition->getTeam('TM7');
        $this->assertNull($team->getClub(), 'Team 7 should have no club defined');
    }

    public function testCompetitionWithClubsNoSuchID() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $this->expectExceptionMessage('Club with ID "Foo" not found');
        $competition->getClub('Foo');
    }

    public function testCompetitionWithClubs() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $this->assertTrue($competition->hasClubs());
        $this->assertEquals('SOU', $competition->getTeam('TM1')->getClub()->getID());
        $this->assertEquals('NOR', $competition->getTeam('TM2')->getClub()->getID());
        $this->assertNull($competition->getTeam('TM7')->getClub());

        $this->assertEquals('This is a club', $competition->getTeam('TM1')->getClub()->getNotes());

        $this->assertEquals('Southampton', $competition->getClub('SOU')->getName());
        $this->assertEquals('Northampton', $competition->getClub('NOR')->getName());

        $clubs = $competition->getClubs();
        $this->assertCount(2, $clubs);
        $this->assertEquals('Southampton', $clubs[0]->getName());
        $this->assertEquals('Northampton', $clubs[1]->getName());

        $this->assertEquals('competition-with-clubs', $clubs[0]->getCompetition()->getName());
    }

    public function testClubSettersGetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $club = $competition->getClub('SOU');

        $this->assertEquals('Southampton', $club->getName());
        $this->assertEquals('This is a club', $club->getNotes());
        $this->assertTrue($club->hasNotes());
        $this->assertCount(3, $club->getTeams());
        $this->assertEquals('Alice VC', $club->getTeams()[0]->getName());
        $this->assertTrue($club->hasTeam('TM1'));
        $this->assertFalse($club->hasTeam('TM2'));

        $club->setName('New Southampton');
        $club->setNotes('This is the club to be');

        $this->assertEquals('New Southampton', $club->getName());
        $this->assertEquals('This is the club to be', $club->getNotes());

        try {
            $club->setName('');
            $this->fail('Club setName should not allow an empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        try {
            $name = 'a';
            for ($i=0; $i < 100; $i++) {
                $name .= '0123456789';
            }
            $club->setName($name);
            $this->fail('Club setName should not allow a long ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club name: must be between 1 and 1000 characters long', $e->getMessage());
        }
    }

    public function testClubDelete() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $club = $competition->getClub('SOU');
        $team = $club->getTeams()[0];

        $this->assertEquals('Southampton', $club->getName());
        $this->assertEquals('This is a club', $club->getNotes());
        $this->assertCount(3, $club->getTeams());
        $this->assertEquals('Alice VC', $team->getName());
        $this->assertEquals('SOU', $team->getClub()->getID());
        $this->assertTrue($club->hasTeam('TM1'));
        $this->assertFalse($club->hasTeam('TM2'));

        $club_returned = $club->deleteTeam('TM1');
        $this->assertInstanceOf('VBCompetitions\Competitions\Club', $club_returned);
        $this->assertEquals('SOU', $club_returned->getID());
        $this->assertCount(2, $club->getTeams());
        $this->assertNull($team->getClub());
        $this->assertEquals('Charlie VC', $club->getTeams()[0]->getName());

        $club_returned = $club->deleteTeam('TM1');
        $this->assertCount(2, $club->getTeams());
        $this->assertEquals('Charlie VC', $club->getTeams()[0]->getName());
    }

    public function testClubConstructorBadID() : void
    {
        $competition = new Competition('test competition');
        try {
            new Club($competition, '', 'my club');
            $this->fail('Club should not allow an empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new Club($competition, '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891', 'my club');
            $this->fail('Club should not allow a long empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new Club($competition, '"id1"', 'my club');
            $this->fail('Club should not allow " character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id:1', 'my club');
            $this->fail('Club should not allow : character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id{1', 'my club');
            $this->fail('Club should not allow { character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id1}', 'my club');
            $this->fail('Club should not allow } character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id1?', 'my club');
            $this->fail('Club should not allow ? character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id=1', 'my club');
            $this->fail('Club should not allow = character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }
    }

    public function testClubContacts() : void
    {
        $competition = new Competition('test');
        $club1 = new Club($competition, 'CL1', 'Some club');
        $club2 = new Club($competition, 'CL2', 'Some other club');

        $contact1 = new ClubContact($club1, 'C1', [ClubContactRole::SECRETARY]);
        $club1->addContact($contact1);

        $contact2 = new ClubContact($club2, 'C1', [ClubContactRole::CHAIR]);
        try {
            $club1->addContact($contact2);
            $this->fail('adding contact should fail with a macthing ID');
        } catch (Exception $e) {
            $this->assertEquals('club contacts with duplicate IDs within a club not allowed', $e->getMessage());
        }

        $this->assertCount(1, $club1->getContacts());
        $club1->deleteContact('C1');
        $this->assertFalse($club1->hasContacts());
        $club1->deleteContact('C1');
        $this->assertFalse($club1->hasContacts());
    }
}
