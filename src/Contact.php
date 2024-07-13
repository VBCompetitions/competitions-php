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

        if ($team->hasContact($id)) {
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

        if (count($this->emails) > 0) {
            $contact->emails = $this->emails;
        }

        if (count($this->phones) > 0) {
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
     * Set the list of roles, overriding the previous list
     *
     * @param array<ContactRole> $roles The list of roles for the contact
     *
     * @return Contact Returns this contact for method chaining
     * @throws Exception When the list of roles contains an invalid value
     */
    public function setRoles(array $roles) : Contact
    {
        if (count($roles) === 0) {
            throw new Exception('Error setting the roles to an empty list as the Contact must have at least one role');
        }

        $new_roles = [];
        foreach ($roles as $role) {
            if (!$role instanceof ContactRole) {
                throw new Exception('Error setting the roles due to invalid role: '.$role);
            }
            array_push($new_roles, $role);
        }

        $this->roles = $new_roles;
        return $this;
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
     * @throws Exception When the email address is invalid
     */
    public function addEmail($email) : Contact
    {
        if (strlen($email) < 3) {
            throw new Exception('Invalid contact email address: must be at least 3 characters long');
        }
        if (!in_array($email, $this->emails)) {
            array_push($this->emails, $email);
        }
        return $this;
    }

    /**
     * Set the list of email addresses, overriding the previous list.  To delete all email addresses, pass in null
     *
     * @param array|null $emails The list of email addresses for the contact
     *
     * @return Contact Returns this contact for method chaining
     * @throws Exception When one of the email addresses is invalid
     */
    public function setEmails(?array $emails) : Contact
    {
        if ($emails === null) {
            $this->emails = [];
            return $this;
        }

        $new_emails = [];
        foreach ($emails as $email) {
            if (strlen($email) < 3) {
                throw new Exception('Invalid contact email address: must be at least 3 characters long');
            }
            if (!in_array($email, $new_emails)) {
                array_push($new_emails, $email);
            }
        }
        $this->emails = $new_emails;
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
     * @throws Exception When the phone number is invalid
     */
    public function addPhone($phone) : Contact
    {
        if (strlen($phone) > 50 || strlen($phone) < 1) {
            throw new Exception('Invalid contact phone number: must be between 1 and 50 characters long');
        }
        if (!in_array($phone, $this->phones)) {
            array_push($this->phones, $phone);
        }
        return $this;
    }

    /**
     * Set the list of phone numbers, overriding the previous list.  To delete all phone numbers, pass in null
     *
     * @param array|null $phones The list of phone numbers for the contact
     *
     * @return Contact Returns this contact for method chaining
     * @throws Exception When one of the phone numbers is invalid
     */
    public function setPhones(?array $phones) : Contact
    {
        if ($phones === null) {
            $this->phones = [];
            return $this;
        }

        $new_phones = [];
        foreach ($phones as $phone) {
            if (strlen($phone) > 50 || strlen($phone) < 1) {
                throw new Exception('Invalid contact phone number: must be between 1 and 50 characters long');
            }
            if (!in_array($phone, $new_phones)) {
                array_push($new_phones, $phone);
            }
        }
        $this->phones = $new_phones;
        return $this;
    }
}
