<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Club;

#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(Club::class)]
final class ClubTest extends TestCase {
    public function testClubNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');

        $team = $competition->getTeamByID('TM7');
        $this->assertNull($team->getClub(), 'Team 7 should have no club defined');
    }

    public function testClubsDuplicateID() : void
    {
        $this->expectExceptionMessage('Club with ID "NOR" already exists in the competition');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs-duplicate-ids.json');
    }

    public function testCompetitionWithClubsNoSuchID() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $this->expectExceptionMessage('Club with ID "Foo" not found');
        $competition->getClubByID('Foo');
    }

    public function testCompetitionWithClubs() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $this->assertEquals('SOU', $competition->getTeamByID('TM1')->getClub()->getID());
        $this->assertEquals('NOR', $competition->getTeamByID('TM2')->getClub()->getID());
        $this->assertNull($competition->getTeamByID('TM7')->getClub());

        $this->assertEquals('This is a club', $competition->getTeamByID('TM1')->getClub()->getNotes());

        $this->assertEquals('Southampton', $competition->getClubByID('SOU')->getName());
        $this->assertEquals('Northampton', $competition->getClubByID('NOR')->getName());

        $clubs = $competition->getClubs();
        $this->assertCount(2, $clubs);
        $this->assertEquals('Southampton', $clubs[0]->getName());
        $this->assertEquals('Northampton', $clubs[1]->getName());

        $this->assertEquals('competition-with-clubs', $clubs[0]->getCompetition()->getName());
    }

    public function testClubSettersGetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $club = $competition->getClubByID('SOU');

        $this->assertEquals('Southampton', $club->getName());
        $this->assertEquals('This is a club', $club->getNotes());
        $this->assertTrue($club->hasNotes());
        $this->assertCount(3, $club->getTeams());
        $this->assertEquals('Alice VC', $club->getTeams()[0]->getName());
        $this->assertTrue($club->hasTeamWithID('TM1'));
        $this->assertFalse($club->hasTeamWithID('TM2'));

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
        $club = $competition->getClubByID('SOU');
        $team = $club->getTeams()[0];

        $this->assertEquals('Southampton', $club->getName());
        $this->assertEquals('This is a club', $club->getNotes());
        $this->assertCount(3, $club->getTeams());
        $this->assertEquals('Alice VC', $team->getName());
        $this->assertEquals('SOU', $team->getClub()->getID());
        $this->assertTrue($club->hasTeamWithID('TM1'));
        $this->assertFalse($club->hasTeamWithID('TM2'));

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
    }

    public function testClubConstructorBadName() : void
    {
        $competition = new Competition('test competition');
        try {
            new Club($competition, 'id1', 'my "club"');
            $this->fail('Club should not allow " character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id1', 'my : club');
            $this->fail('Club should not allow : character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id1', 'my {club');
            $this->fail('Club should not allow { character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id1', 'my club}');
            $this->fail('Club should not allow } character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id1', 'my club?');
            $this->fail('Club should not allow ? character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Club($competition, 'id1', 'my club = good');
            $this->fail('Club should not allow = character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }
    }
}
