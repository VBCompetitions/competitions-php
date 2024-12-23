<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Club;
use VBCompetitions\Competitions\ClubContact;
use VBCompetitions\Competitions\ClubContactRole;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\Contact;

#[CoversClass(Contact::class)]
#[CoversClass(Competition::class)]
#[CoversClass(Club::class)]
#[CoversClass(ClubContact::class)]
#[CoversClass(ClubContactRole::class)]
final class ClubContactTest extends TestCase {
    public function testContactsNone() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'clubcontacts'))), 'no-contacts.json');
        $club = $competition->getClub('CL1');

        $this->assertCount(0, $club->getContacts(), 'Club should have no contacts defined');
        $this->assertFalse($club->hasContacts(), 'Club should have no contacts defined');
    }

    public function testContactsDuplicateID() : void
    {
        $this->expectExceptionMessage('Contact with ID "C1" already exists in the club');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'clubcontacts'))), 'contacts-duplicate-ids.json');
    }

    public function testContactsEach() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'clubcontacts'))), 'contacts.json');
        $club = $competition->getClub('CL1');

        $this->assertEquals(7, count($club->getContacts()), 'Competition should have 7 contacts defined');

        $contactC1 = $club->getContact('C1');
        $this->assertEquals('C1', $contactC1->getID());
        $this->assertEquals('Alice Alison', $contactC1->getName());
        $this->assertEquals(['alice@example.com'], $contactC1->getEmails());
        $this->assertEquals(['01234 567890'], $contactC1->getPhones());
        $this->assertEquals($contactC1->getNotes(), 'Alice is both the club chair and secretary');

        $this->assertEquals([ClubContactRole::CHAIR, ClubContactRole::SECRETARY], $contactC1->getRoles());
        $this->assertTrue($contactC1->hasRole(ClubContactRole::CHAIR));
        $this->assertTrue($contactC1->hasRole(ClubContactRole::SECRETARY));
        $this->assertFalse($contactC1->hasRole(ClubContactRole::VICE));
        $this->assertFalse($contactC1->hasRole(ClubContactRole::TREASURER));
        $this->assertFalse($contactC1->hasRole(ClubContactRole::WELFARE));
        $this->assertFalse($contactC1->hasRole(ClubContactRole::COMMUNICATIONS));
        $this->assertFalse($contactC1->hasRole(ClubContactRole::MARKETING));
        $this->assertFalse($contactC1->hasRole(ClubContactRole::VOLUNTEER));
        $this->assertFalse($contactC1->hasRole(ClubContactRole::LOGISTICS));
        $this->assertFalse($contactC1->hasRole(ClubContactRole::COACHING));

        $this->assertEquals([ClubContactRole::TREASURER], $club->getContact('C2')->getRoles());
        $this->assertEquals([ClubContactRole::VICE, ClubContactRole::LOGISTICS], $club->getContact('C3')->getRoles());
        $this->assertEquals([ClubContactRole::WELFARE], $club->getContact('C4')->getRoles());
        $this->assertEquals([ClubContactRole::COMMUNICATIONS, ClubContactRole::MARKETING], $club->getContact('C5')->getRoles());
        $this->assertEquals([ClubContactRole::VOLUNTEER], $club->getContact('C6')->getRoles());
        $this->assertEquals([ClubContactRole::COACHING], $club->getContact('C7')->getRoles());
    }

    public function testContactsGetByIDOutOfBounds() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'clubcontacts'))), 'contacts.json');
        $club = $competition->getClub('CL1');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Contact with ID "NO-SUCH-CONTACT" not found');
        $club->getContact('NO-SUCH-CONTACT');
    }

    public function testContactSetName() : void
    {
        $competition = new Competition('test competition');
        $club = new Club($competition, 'CL1', 'Some Club');
        $contact = new ClubContact($club, 'C1', [ClubContactRole::SECRETARY]);
        $this->assertEquals($contact->getClub()->getID(), 'CL1');

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
        $club = new Club($competition, 'CL1', 'Some Club');
        $contact = new ClubContact($club, 'C1', [ClubContactRole::SECRETARY]);

        $contact->addEmail('alice@example.com')->addEmail('alice@example.com')->addEmail('alice@example.com');
        $this->assertCount(1, $contact->getEmails());

        $contact->addPhone('01234 567890')->addPhone('01234 567890')->addPhone('01234 567890');
        $this->assertCount(1, $contact->getPhones());

        $contact->addRole(ClubContactRole::SECRETARY)->addRole(ClubContactRole::SECRETARY);
        $this->assertCount(1, $contact->getRoles());
    }

    public function testContactSettersAndAdders() : void
    {
        $competition = new Competition('test competition');
        $club = new Club($competition, 'CL1', 'Some Club');
        $contact = new ClubContact($club, 'C1', [ClubContactRole::SECRETARY]);

        try {
            $contact->addRole('bad role');
            $this->fail('Contact should not allow a non-existent role');
        } catch (Exception $e) {
            $this->assertEquals('Error adding the role due to invalid role: bad role', $e->getMessage());
        }

        $this->assertCount(1, $contact->getRoles());
        $contact->setRoles([ClubContactRole::CHAIR, ClubContactRole::COACHING, ClubContactRole::TREASURER, ClubContactRole::SECRETARY]);
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
        $club = new Club($competition, 'CL1', 'Some Club');
        try {
            new ClubContact($club, '', [ClubContactRole::SECRETARY]);
            $this->fail('Contact should not allow an empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new ClubContact($club, '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891', [ClubContactRole::SECRETARY]);
            $this->fail('Contact should not allow a long empty ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            new ClubContact($club, '"id1"', [ClubContactRole::SECRETARY]);
            $this->fail('Contact should not allow " character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new ClubContact($club, 'id:1', [ClubContactRole::SECRETARY]);
            $this->fail('Contact should not allow : character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new ClubContact($club, 'id{1', [ClubContactRole::SECRETARY]);
            $this->fail('Contact should not allow { character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new ClubContact($club, 'id1}', [ClubContactRole::SECRETARY]);
            $this->fail('Contact should not allow } character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new ClubContact($club, 'id1?', [ClubContactRole::SECRETARY]);
            $this->fail('Contact should not allow ? character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }

        try {
            new ClubContact($club, 'id=1', [ClubContactRole::SECRETARY]);
            $this->fail('Contact should not allow = character');
        } catch (Exception $e) {
            $this->assertEquals('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =', $e->getMessage());
        }
    }
}
