<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use OutOfBoundsException;
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

    public function testClubSetters() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $club = $competition->getClubByID('SOU');

        $this->assertEquals('Southampton', $club->getName());
        $this->assertEquals('This is a club', $club->getNotes());

        $club->setName('New Southampton');
        $club->setNotes('This is the club to be');

        $this->assertEquals('New Southampton', $club->getName());
        $this->assertEquals('This is the club to be', $club->getNotes());
    }
}
