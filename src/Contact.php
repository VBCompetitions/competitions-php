<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * The role of the contact within a team.  There may me multiple contacts with the same role
 */
enum ContactRole
{
    /** A team treasurer */
    case TREASURER;
    /** A team secretary */
    case SECRETARY;
    /** A team manager */
    case MANAGER;
    /** A team captain */
    case CAPTAIN;
    /** A team coach */
    case COACH;
    /** A team assistant coach */
    case ASSISTANT_COACH;
    /** A team medic */
    case MEDIC;
}

/**
 * A single contact for a team
 */
final class Contact implements JsonSerializable
{
    /** A unique ID for this contact, e.g. 'TM1Contact1'.  This must be unique within the team*/
    private string $id;

    /** The name of this contact */
    private ?string $name = null;

    /** The roles of this contact within the team */
    private array $roles = [];

    /** The email addresses for this contact */
    private ?array $emails = [];

    /** A telephone number for this contact.  If a contact has multiple phone numbers then add them as another contact */
    private ?array $phones = [];

    /** The team this contact belongs to */
    private CompetitionTeam $team;

    /**
     * Defines a Team Contact
     *
     * @param object $contact_data The data defining this Contact
     */
    function __construct(CompetitionTeam $team, string $id, array $roles)
    {
        if (strlen($id) > 100 || strlen($id) < 1) {
            throw new Exception('Invalid contact ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $id)) {
            throw new Exception('Invalid contact ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        if ($team->hasContactWithID($id)) {
            throw new Exception('Contact with ID "'.$id.'" already exists in the team');
        }

        $this->id = $id;

        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    public static function loadFromData(CompetitionTeam $competition_team, object $contact_data) : Contact
    {
        $contact = new Contact($competition_team, $contact_data->id, $contact_data->roles);

        if (property_exists($contact_data, 'name')) {
            $contact->setName($contact_data->name);
        }

        if (property_exists($contact_data, 'emails')) {
            foreach ($contact_data->emails as $email) {
                $contact->addEmail($email);
            }
        }
        if (property_exists($contact_data, 'phones')) {
            foreach ($contact_data->phones as $phone) {
                $contact->addPhone($phone);
            }
        }

        return $contact;
    }

    /**
     * Return the contact data suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $contact = new stdClass();
        $contact->id = $this->id;
        if ($this->name !== null) {
            $contact->name = $this->name;
        }

        $contact->roles = [];
        foreach ($this->roles as $role) {
            array_push($contact->roles, match($role) {
                ContactRole::SECRETARY => 'secretary',
                ContactRole::TREASURER => 'treasurer',
                ContactRole::MANAGER => 'manager',
                ContactRole::CAPTAIN => 'captain',
                ContactRole::COACH => 'coach',
                ContactRole::ASSISTANT_COACH => 'assistantCoach',
                ContactRole::MEDIC => 'medic'
            });
        }

        if ($this->emails !== null) {
            $contact->emails = $this->emails;
        }

        if ($this->phones !== null) {
            $contact->phones = $this->phones;
        }

        return $contact;
    }

    public function getTeam() : CompetitionTeam
    {
        return $this->team;
    }

    /**
     * Get the ID for this contact
     *
     * @return string the id for this contact
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Set the name for this contact
     *
     * @param string the name for this contact
     */
    public function setName($name) : void
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid contact name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
    }

    /**
     * Get the name for this contact
     *
     * @return string the name for this contact
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the roles for this contact
     *
     * @return array<ContactRole> the roles for this contact
     */
    public function getRoles() : array
    {
        return $this->roles;
    }

    /**
     * Add the role to this contact
     *
     * @param string the role to add to this contact
     *
     *
     */
    public function addRole(string $role) : int
    {
        // TODO this could be bitwise maths so any duplicates are ignored?
        // Todo whatever - still need to scan for dupes
        switch ($role) {
            case 'secretary':
                array_push($this->roles, ContactRole::SECRETARY);
                break;
            case 'treasurer':
                array_push($this->roles, ContactRole::TREASURER);
                break;
            case 'manager':
                array_push($this->roles, ContactRole::MANAGER);
                break;
            case 'captain':
                array_push($this->roles, ContactRole::CAPTAIN);
                break;
            case 'coach':
                array_push($this->roles, ContactRole::COACH);
                break;
            case 'assistantCoach':
                array_push($this->roles, ContactRole::ASSISTANT_COACH);
                break;
            case 'medic':
                array_push($this->roles, ContactRole::MEDIC);
                break;
            default:
                throw new Exception('Role "'.$role.'" is not a valid role for a contact');
        }
        return count($this->roles);
    }

    /**
     * Return whether this contact has the specified role
     *
     * @return bool whether the contact has the specified role
     */
    public function hasRole(ContactRole $role) : bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Get the email addresses for this contact
     *
     * @return array<string> the email addresses for this contact
     */
    public function getEmails() : array
    {
        return $this->emails;
    }

    /**
     * Add the email addresses to this contact
     *
     * @param string $email the email address to add to this contact
     */
    public function addEmail($email) : int
    {
        // TODO - screen for duplicates
        array_push($this->emails, $email);
        return count($this->emails);
    }

    /**
     * Get the phone numbers for this contact
     *
     * @return array<string> the phone numbers for this contact
     */
    public function getPhones() : array
    {
        return $this->phones;
    }

    public function addPhone($phone) : int
    {
        // TODO - screen for duplicates
        array_push($this->phones, $phone);
        return count($this->phones);
    }
}
