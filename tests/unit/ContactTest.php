<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
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

        $team = $competition->getTeam('TM1');
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team);
        $this->assertCount(0, $team->getContacts(), 'Team 1 should have no contacts defined');
    }

    public function testContactsDefaultSecretary() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts.json');

        $team = $competition->getTeam('TM2');
        $this->assertInstanceOf('VBCompetitions\Competitions\CompetitionTeam', $team);

        $this->assertEquals(1, count($team->getContacts()), 'Team 2 should have only one contact defined');
        $this->assertEquals('C1', $team->getContact('C1')->getID());
        $this->assertEquals('Alice Alison', $team->getContact('C1')->getName());
        $this->assertEquals(['alice@example.com'], $team->getContact('C1')->getEmails());
        $this->assertEquals([ContactRole::SECRETARY], $team->getContact('C1')->getRoles());
    }

    public function testContactsDuplicateID() : void
    {
        $this->expectExceptionMessage('Contact with ID "C1" already exists in the team');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts-duplicate-ids.json');
    }

    public function testContactsEach() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts.json');
        $team = $competition->getTeam('TM3');

        $this->assertEquals(7, count($team->getContacts()), 'Team 3 should have 7 contacts defined');

        $contactC1 = $team->getContact('C1');
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

        $this->assertEquals([ContactRole::TREASURER], $team->getContact('C2')->getRoles());
        $this->assertEquals([ContactRole::MANAGER], $team->getContact('C3')->getRoles());
        $this->assertEquals([ContactRole::CAPTAIN], $team->getContact('C4')->getRoles());
        $this->assertEquals([ContactRole::COACH], $team->getContact('C5')->getRoles());
        $this->assertEquals([ContactRole::ASSISTANT_COACH], $team->getContact('C6')->getRoles());
        $this->assertEquals([ContactRole::MEDIC], $team->getContact('C7')->getRoles());
    }

    public function testContactsGetByIDOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'contacts'))), 'contacts.json');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Contact with ID "NO-SUCH-TEAM" not found');
        $competition->getTeam('TM1')->getContact('NO-SUCH-TEAM');
    }

    public function testContactSetName() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        $contact = new Contact($team, 'C1', [ContactRole::SECRETARY]);
        $this->assertEquals('T1', $contact->getTeam()->getID());

        try {
            $contact->setName('');
            $this->fail('Contact should not allow an empty Name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact name: must be between 1 and 1000 characters long', $e->getMessage());
        }
        $this->assertNull($contact->getName());

        try {
            $name = 'a';
            for ($i=0; $i < 100; $i++) {
                $name .= '0123456789';
            }
            $contact->setName($name);
            $this->fail('Contact should not allow a long Name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact name: must be between 1 and 1000 characters long', $e->getMessage());
        }
        $this->assertNull($contact->getName());

        $contact->setName('Alice Alison');
        $this->assertEquals('Alice Alison', $contact->getName());
    }

    public function testContactSetSpotsDuplicates() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        $contact = new Contact($team, 'C1', [ContactRole::SECRETARY]);

        $contact->addEmail('alice@example.com')->addEmail('alice@example.com')->addEmail('alice@example.com');
        $this->assertCount(1, $contact->getEmails());

        $contact->addPhone('01234 567890')->addPhone('01234 567890')->addPhone('01234 567890');
        $this->assertCount(1, $contact->getPhones());

        $contact->addRole(ContactRole::SECRETARY)->addRole(ContactRole::SECRETARY);
        $this->assertCount(1, $contact->getRoles());
    }

    public function testContactSettersAndAdders() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        $contact = new Contact($team, 'C1', [ContactRole::SECRETARY]);

        $this->assertCount(1, $contact->getRoles());
        $contact->setRoles([ContactRole::CAPTAIN, ContactRole::COACH, ContactRole::TREASURER, ContactRole::SECRETARY]);
        $this->assertCount(4, $contact->getRoles());

        try {
            $contact->setRoles([]);
            $this->fail('Contact should not allow zero roles');
        } catch (Exception $e) {
            $this->assertEquals('Error setting the roles to an empty list as the Contact must have at least one role', $e->getMessage());
        }

        try {
            $contact->setRoles(['foo']);
            $this->fail('Contact should not allow an unknown role');
        } catch (Exception $e) {
            $this->assertEquals('Error setting the roles due to invalid role: foo', $e->getMessage());
        }

        $this->assertCount(0, $contact->getEmails());
        // Include a duplicate
        $contact->setEmails(['alice1@example.com', 'alice2@example.com', 'alice2@example.com']);
        $this->assertCount(2, $contact->getEmails());
        $contact->addEmail('alice3@example.com');
        $this->assertCount(3, $contact->getEmails());
        try {
            $contact->addEmail('fo');
            $this->fail('Contact should not allow invalid email');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact email address: must be at least 3 characters long', $e->getMessage());
        }

        $contact->setEmails(null);
        $this->assertCount(0, $contact->getEmails());

        try {
            $contact->setEmails(['fo', 'alice1@example.com']);
            $this->fail('Contact should not allow invalid email');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact email address: must be at least 3 characters long', $e->getMessage());
        }

        try {
            $contact->setEmails(['alice1@example.com', 'fo']);
            $this->fail('Contact should not allow invalid email');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact email address: must be at least 3 characters long', $e->getMessage());
        }

        $this->assertCount(0, $contact->getPhones());
        // Include a duplicate
        $contact->setPhones(['01234 567890', '01234 567891', '01234 567891']);
        $this->assertCount(2, $contact->getPhones());
        $contact->addPhone('01234 567892');
        $this->assertCount(3, $contact->getPhones());

        try {
            $contact->addPhone('');
            $this->fail('Contact should not allow an empty phone number');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact phone number: must be between 1 and 50 characters long', $e->getMessage());
        }

        try {
            $contact->addPhone('012345678901234567890123456789012345678901234567890123456789');
            $this->fail('Contact should not allow an empty phone number');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact phone number: must be between 1 and 50 characters long', $e->getMessage());
        }

        $contact->setPhones(null);
        $this->assertCount(0, $contact->getPhones());

        try {
            $contact->setPhones(['', '01234 567890']);
            $this->fail('Contact should not allow an empty phone number');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact phone number: must be between 1 and 50 characters long', $e->getMessage());
        }

        try {
            $contact->setPhones(['01234 567890', '']);
            $this->fail('Contact should not allow a string as a phone number');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact phone number: must be between 1 and 50 characters long', $e->getMessage());
        }

        try {
            $contact->setPhones(['012345678901234567890123456789012345678901234567890123456789', '01234 567890']);
            $this->fail('Contact should not allow an empty phone number');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact phone number: must be between 1 and 50 characters long', $e->getMessage());
        }

        try {
            $contact->setPhones(['01234 567890', '012345678901234567890123456789012345678901234567890123456789']);
            $this->fail('Contact should not allow a string as a phone number');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact phone number: must be between 1 and 50 characters long', $e->getMessage());
        }
    }

    public function testContactConstructorBadID() : void
    {
        $competition = new Competition('test competition');
        $team = new CompetitionTeam($competition, 'T1', 'Team 1');
        try {
            new Contact($team, '', [ContactRole::SECRETARY]);
            $this->fail('Contact should not allow an empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new Contact($team, '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891', [ContactRole::SECRETARY]);
            $this->fail('Contact should not allow a long empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new Contact($team, '"id1"', [ContactRole::SECRETARY]);
            $this->fail('Contact should not allow " character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Contact($team, 'id:1', [ContactRole::SECRETARY]);
            $this->fail('Contact should not allow : character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Contact($team, 'id{1', [ContactRole::SECRETARY]);
            $this->fail('Contact should not allow { character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Contact($team, 'id1}', [ContactRole::SECRETARY]);
            $this->fail('Contact should not allow } character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Contact($team, 'id1?', [ContactRole::SECRETARY]);
            $this->fail('Contact should not allow ? character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new Contact($team, 'id=1', [ContactRole::SECRETARY]);
            $this->fail('Contact should not allow = character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }
    }
}
