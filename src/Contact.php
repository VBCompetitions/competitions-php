<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * A single contact for a team
 */
final class Contact implements JsonSerializable
{
    /** @var string A unique ID for this contact, e.g. 'TM1Contact1'. This must be unique within the team */
    private string $id;

    /** @var ?string The name of this contact */
    private ?string $name = null;

    /** @var array The roles of this contact within the team */
    private array $roles = [];

    /** @var ?array The email addresses for this contact */
    private ?array $emails = [];

    /** @var ?array A telephone number for this contact. If a contact has multiple phone numbers then add them as another contact */
    private ?array $phones = [];

    /** @var CompetitionTeam The team this contact belongs to */
    private CompetitionTeam $team;

    /**
     * Defines a Team Contact
     *
     * @param CompetitionTeam $team The team this contact belongs to
     * @param string $id The unique ID for this contact
     * @param array $roles The roles of this contact within the team
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

        $this->team = $team;
        $this->id = $id;

        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    /**
     * Loads contact data from an object
     *
     * @param object $contact_data The data defining this Contact
     *
     * @return Contact The updated Contact instance
     */
    public function loadFromData(object $contact_data) : Contact
    {
        if (property_exists($contact_data, 'name')) {
            $this->setName($contact_data->name);
        }

        if (property_exists($contact_data, 'emails')) {
            foreach ($contact_data->emails as $email) {
                $this->addEmail($email);
            }
        }
        if (property_exists($contact_data, 'phones')) {
            foreach ($contact_data->phones as $phone) {
                $this->addPhone($phone);
            }
        }

        return $this;
    }

    /**
     * Serializes the contact data into a format suitable for saving into a competition file
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
                ContactRole::MEDIC => 'medic',
                default => 'secretary'
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

    /**
     * Get the team this contact belongs to
     *
     * @return CompetitionTeam The team this contact belongs to
     */
    public function getTeam() : CompetitionTeam
    {
        return $this->team;
    }

    /**
     * Get the ID for this contact
     *
     * @return string The ID for this contact
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Set the name for this contact
     *
     * @param string $name The name for this contact
     *
     * @throws Exception If the name is invalid
     *
     * @return Contact This contact
     */
    public function setName($name) : Contact
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid contact name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name for this contact
     *
     * @return ?string The name for this contact
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Get the roles for this contact
     *
     * @return array<ContactRole> The roles for this contact
     */
    public function getRoles() : array
    {
        return $this->roles;
    }

    /**
     * Add a role to this contact
     *
     * @param ContactRole $role The role to add to this contact
     *
     * @return Contact Returns this contact for method chaining
     */
    public function addRole(ContactRole $role) : Contact
    {
        if (!$this->hasRole($role)) {
            array_push($this->roles, $role);
        }
        return $this;
    }

    /**
     * Check if this contact has the specified role
     *
     * @param ContactRole $role The role to check for
     *
     * @return bool Whether the contact has the specified role
     */
    public function hasRole(ContactRole $role) : bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Get the email addresses for this contact
     *
     * @return array<string> The email addresses for this contact
     */
    public function getEmails() : array
    {
        return $this->emails;
    }

    /**
     * Add an email address to this contact
     *
     * @param string $email The email address to add
     *
     * @return Contact Returns this contact for method chaining
     */
    public function addEmail($email) : Contact
    {
        if (!in_array($email, $this->emails)) {
            array_push($this->emails, $email);
        }
        return $this;
    }

    /**
     * Get the phone numbers for this contact
     *
     * @return array<string> The phone numbers for this contact
     */
    public function getPhones() : array
    {
        return $this->phones;
    }

    /**
     * Add a phone number to this contact
     *
     * @param string $phone The phone number to add
     * @return Contact Returns this contact for method chaining
     */
    public function addPhone($phone) : Contact
    {
        if (!in_array($phone, $this->phones)) {
            array_push($this->phones, $phone);
        }
        return $this;
    }
}
