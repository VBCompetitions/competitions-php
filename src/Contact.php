<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use stdClass;

/**
 * The role of the contact within a team.  There may me multiple contacts with the same role
 */
enum ContactType
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

    /**
     * Defines a Team Contact
     *
     * @param object $contact_data The data defining this Contact
     */
    function __construct(object $contact_data)
    {
        $this->id = $contact_data->id;
        if (property_exists($contact_data, 'name')) {
            $this->name = $contact_data->name;
        }
        foreach ($contact_data->roles as $role) {
            switch ($role) {
                case 'secretary':
                    array_push($this->roles, ContactType::SECRETARY);
                    break;
                case 'treasurer':
                    array_push($this->roles, ContactType::TREASURER);
                    break;
                case 'manager':
                    array_push($this->roles, ContactType::MANAGER);
                    break;
                case 'captain':
                    array_push($this->roles, ContactType::CAPTAIN);
                    break;
                case 'coach':
                    array_push($this->roles, ContactType::COACH);
                    break;
                case 'assistantCoach':
                    array_push($this->roles, ContactType::ASSISTANT_COACH);
                    break;
                case 'medic':
                    array_push($this->roles, ContactType::MEDIC);
                    break;
            }
        }
        if (property_exists($contact_data, 'emails')) {
            $this->emails = $contact_data->emails;
        }
        if (property_exists($contact_data, 'phones')) {
            $this->phones = $contact_data->phones;
        }
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
     * @return array<ContactType> the roles for this contact
     */
    public function getRoles() : array
    {
        return $this->roles;
    }

    /**
     * Return whether this contact has the specified role
     *
     * @return bool whether the contact has the specified role
     */
    public function hasRole(ContactType $role) : bool
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
     * Get the phone numbers for this contact
     *
     * @return array<string> the phone numbers for this contact
     */
    public function getPhones() : array
    {
        return $this->phones;
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
                ContactType::SECRETARY => 'secretary',
                ContactType::TREASURER => 'treasurer',
                ContactType::MANAGER => 'manager',
                ContactType::CAPTAIN => 'captain',
                ContactType::COACH => 'coach',
                ContactType::ASSISTANT_COACH => 'assistantCoach',
                ContactType::MEDIC => 'medic'
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
}
