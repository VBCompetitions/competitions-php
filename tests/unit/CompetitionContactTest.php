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
use VBCompetitions\Competitions\CompetitionContact;
use VBCompetitions\Competitions\CompetitionContactRole;

#[CoversClass(Contact::class)]
#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(CompetitionContact::class)]
#[CoversClass(CompetitionContactRole::class)]
final class CompetitionContactTest extends TestCase {
    public function testContactsNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitioncontacts'))), 'no-contacts.json');

        $this->assertCount(0, $competition->getContacts(), 'Competition should have no contacts defined');
        $this->assertFalse($competition->hasContacts(), 'Competition should have no contacts defined');
    }

    public function testContactsDuplicateID() : void
    {
        $this->expectExceptionMessage('Contact with ID "C1" already exists in the competition');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitioncontacts'))), 'contacts-duplicate-ids.json');
    }

    public function testContactsEach() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitioncontacts'))), 'contacts.json');

        $this->assertEquals(7, count($competition->getContacts()), 'Competition should have 7 contacts defined');

        $contactC1 = $competition->getContact('C1');
        $this->assertEquals('C1', $contactC1->getID());
        $this->assertEquals('Alice Alison', $contactC1->getName());
        $this->assertEquals(['alice@example.com'], $contactC1->getEmails());
        $this->assertEquals(['01234 567890'], $contactC1->getPhones());
        $this->assertEquals($contactC1->getNotes(), 'We should find a separate secretary so Alice doesn\'t get overloaded');

        $this->assertEquals([CompetitionContactRole::DIRECTOR, CompetitionContactRole::FIXTURES, CompetitionContactRole::SECRETARY], $contactC1->getRoles());
        $this->assertTrue($contactC1->hasRole(CompetitionContactRole::DIRECTOR));
        $this->assertTrue($contactC1->hasRole(CompetitionContactRole::FIXTURES));
        $this->assertTrue($contactC1->hasRole(CompetitionContactRole::SECRETARY));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::LOGISTICS));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::COMMUNICATIONS));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::OFFICIALS));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::RESULTS));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::MARKETING));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::SAFETY));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::VOLUNTEER));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::WELFARE));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::HOSPITALITY));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::CEREMONIES));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::TREASURER));
        $this->assertFalse($contactC1->hasRole(CompetitionContactRole::MEDIC));

        $this->assertEquals([CompetitionContactRole::LOGISTICS, CompetitionContactRole::TREASURER], $competition->getContact('C2')->getRoles());
        $this->assertEquals([CompetitionContactRole::COMMUNICATIONS], $competition->getContact('C3')->getRoles());
        $this->assertEquals([CompetitionContactRole::OFFICIALS, CompetitionContactRole::RESULTS], $competition->getContact('C4')->getRoles());
        $this->assertEquals([CompetitionContactRole::MARKETING, CompetitionContactRole::HOSPITALITY, CompetitionContactRole::CEREMONIES], $competition->getContact('C5')->getRoles());
        $this->assertEquals([CompetitionContactRole::SAFETY, CompetitionContactRole::VOLUNTEER, CompetitionContactRole::WELFARE], $competition->getContact('C6')->getRoles());
        $this->assertEquals([CompetitionContactRole::MEDIC], $competition->getContact('C7')->getRoles());
    }

    public function testContactsGetByIDOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'teamcontacts'))), 'contacts.json');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Contact with ID "NO-SUCH-CONTACT" not found');
        $competition->getContact('NO-SUCH-CONTACT');
    }

    public function testContactSetName() : void
    {
        $competition = new Competition('test competition');
        $contact = new CompetitionContact($competition, 'C1', [CompetitionContactRole::SECRETARY]);
        $this->assertEquals($competition->getName(), $contact->getCompetition()->getName());

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
        $contact = new CompetitionContact($competition, 'C1', [CompetitionContactRole::SECRETARY]);

        $contact->addEmail('alice@example.com')->addEmail('alice@example.com')->addEmail('alice@example.com');
        $this->assertCount(1, $contact->getEmails());

        $contact->addPhone('01234 567890')->addPhone('01234 567890')->addPhone('01234 567890');
        $this->assertCount(1, $contact->getPhones());

        $contact->addRole(CompetitionContactRole::SECRETARY)->addRole(CompetitionContactRole::SECRETARY);
        $this->assertCount(1, $contact->getRoles());
    }

    public function testContactSettersAndAdders() : void
    {
        $competition = new Competition('test competition');
        $contact = new CompetitionContact($competition, 'C1', [CompetitionContactRole::SECRETARY]);

        try {
            $contact->addRole('bad role');
            $this->fail('Contact should not allow a non-existent role');
        } catch (Exception $e) {
            $this->assertEquals('Error adding the role due to invalid role: bad role', $e->getMessage());
        }

        $this->assertCount(1, $contact->getRoles());
        $contact->setRoles([CompetitionContactRole::DIRECTOR, CompetitionContactRole::FIXTURES, CompetitionContactRole::TREASURER, CompetitionContactRole::SECRETARY]);
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

        $this->assertNull($contact->getNotes());
        $contact->setNotes('some contact notes');
        $this->assertEquals($contact->getNotes(), 'some contact notes');
        $contact->setNotes(null);
        $this->assertNull($contact->getNotes());
    }

    public function testContactConstructorBadID() : void
    {
        $competition = new Competition('test competition');
        try {
            new CompetitionContact($competition, '', [CompetitionContactRole::SECRETARY]);
            $this->fail('Contact should not allow an empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new CompetitionContact($competition, '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891', [CompetitionContactRole::SECRETARY]);
            $this->fail('Contact should not allow a long empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new CompetitionContact($competition, '"id1"', [CompetitionContactRole::SECRETARY]);
            $this->fail('Contact should not allow " character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionContact($competition, 'id:1', [CompetitionContactRole::SECRETARY]);
            $this->fail('Contact should not allow : character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionContact($competition, 'id{1', [CompetitionContactRole::SECRETARY]);
            $this->fail('Contact should not allow { character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionContact($competition, 'id1}', [CompetitionContactRole::SECRETARY]);
            $this->fail('Contact should not allow } character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionContact($competition, 'id1?', [CompetitionContactRole::SECRETARY]);
            $this->fail('Contact should not allow ? character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new CompetitionContact($competition, 'id=1', [CompetitionContactRole::SECRETARY]);
            $this->fail('Contact should not allow = character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }
    }
}
