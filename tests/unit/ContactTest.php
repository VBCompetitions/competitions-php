<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Contact;
use VBCompetitions\Competitions\ContactRole;

#[CoversClass(Contact::class)]
#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(ContactRole::class)]
final class ContactTest extends TestCase {
    public function testContactsNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts.json');

        $team = $competition->getTeamByID('TM1');
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team);
        $this->assertCount(0, $team->getContacts(), 'Team 1 should have no contacts defined');
    }

    public function testContactsDefaultSecretary() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts.json');

        $team = $competition->getTeamByID('TM2');
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team);

        $this->assertEquals(1, count($team->getContacts()), 'Team 2 should have only one contact defined');
        $this->assertEquals('C1', $team->getContactByID('C1')->getID());
        $this->assertEquals('Alice Alison', $team->getContactByID('C1')->getName());
        $this->assertEquals(['alice@example.com'], $team->getContactByID('C1')->getEmails());
        $this->assertEquals([ContactRole::SECRETARY], $team->getContactByID('C1')->getRoles());
    }

    public function testContactsDuplicateID() : void
    {
        $this->expectExceptionMessage('Contact with ID "C1" already exists in the team');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts-duplicate-ids.json');
    }

    public function testContactsEach() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts.json');
        $team = $competition->getTeamByID('TM3');

        $this->assertEquals(7, count($team->getContacts()), 'Team 3 should have 7 contacts defined');

        $contactC1 = $team->getContactByID('C1');
        $this->assertEquals('C1', $contactC1->getID());
        $this->assertEquals('Alice Alison', $contactC1->getName());
        $this->assertEquals(['alice@example.com'], $contactC1->getEmails());
        $this->assertEquals(['01234 567890'], $contactC1->getPhones());

        $this->assertEquals([ContactRole::SECRETARY, ContactRole::ASSISTANT_COACH], $contactC1->getRoles());
        $this->assertTrue($contactC1->hasRole(ContactRole::SECRETARY));
        $this->assertTrue($contactC1->hasRole(ContactRole::ASSISTANT_COACH));
        $this->assertFalse($contactC1->hasRole(ContactRole::TREASURER));
        $this->assertFalse($contactC1->hasRole(ContactRole::MANAGER));
        $this->assertFalse($contactC1->hasRole(ContactRole::CAPTAIN));
        $this->assertFalse($contactC1->hasRole(ContactRole::COACH));
        $this->assertFalse($contactC1->hasRole(ContactRole::MEDIC));

        $this->assertEquals([ContactRole::TREASURER], $team->getContactByID('C2')->getRoles());
        $this->assertEquals([ContactRole::MANAGER], $team->getContactByID('C3')->getRoles());
        $this->assertEquals([ContactRole::CAPTAIN], $team->getContactByID('C4')->getRoles());
        $this->assertEquals([ContactRole::COACH], $team->getContactByID('C5')->getRoles());
        $this->assertEquals([ContactRole::ASSISTANT_COACH], $team->getContactByID('C6')->getRoles());
        $this->assertEquals([ContactRole::MEDIC], $team->getContactByID('C7')->getRoles());
    }

    public function testContactsGetByIDOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts.json');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Contact with ID NO-SUCH-TEAM not found');
        $competition->getTeamByID('TM1')->getContactByID('NO-SUCH-TEAM');
    }
}
