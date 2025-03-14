<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;

/**
 * A club definition
 */
final class Club implements JsonSerializable
{
    /** @var string A unique ID for the club, e.g. 'CLUB1'.  This must be unique within the competition.  It must only contain letters (upper or lowercase), and numbers */
    private string $id;

    /** @var string The name for the club */
    private string $name;

    /** @var array The contacts for the Club */
    private array $contacts = [];

    /** @var ?string Free form string to add notes about a club.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** @var Competition The Competition this club is in */
    private Competition $competition;

    /** @var object A Lookup table from team IDs (including references) to the team */
    private object $team_lookup;

    /** @var object A Lookup table from contact IDs to the contact */
    private object $contact_lookup;

    public const UNKNOWN_CLUB_ID = 'UNKNOWN';
    public const UNKNOWN_CLUB_NAME = 'UNKNOWN';

    /**
     * Contains the club data of a competition, creating any metadata needed
     *
     * @param Competition $competition A link back to the Competition this Stage is in
     * @param string $id The ID of this Team
     * @param string $club_name The name of this Team
     * @throws Exception When the provided club ID is invalid or already exists in the competition
     */
    function __construct(Competition $competition, string $id, string $club_name)
    {
        if (strlen($id) > 100 || strlen($id) < 1) {
            throw new Exception('Invalid club ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $id)) {
            throw new Exception('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        if ($competition->hasClub($id)) {
            throw new Exception('Club with ID "'.$id.'" already exists in the competition');
        }

        $this->competition = $competition;
        $this->id = $id;
        $this->setName($club_name);
        $this->team_lookup = new stdClass();
        $this->contact_lookup = new stdClass();
    }

    /**
     * Assumes this is a freshly made Club object and loads it with the data extracted
     * from the Competitions JSON file for this club
     *
     * @param object $club_data Data from a Competitions JSON file for a single club
     * @return Club the updated club object
     */
    public function loadFromData(object $club_data) : Club
    {
        if (property_exists($club_data, 'contacts')) {
            foreach ($club_data->contacts as $contact_data) {
                $this->addContact((new ClubContact($this, $contact_data->id, $contact_data->roles))->loadFromData($contact_data));
            }
        }

        if (property_exists($club_data, 'notes')) {
            $this->setNotes($club_data->notes);
        }

        return $this;
    }

    /**
     * Return the list of team definition suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $team = new stdClass();
        $team->id = $this->id;
        $team->name = $this->name;

        if ($this->notes !== null) {
            $team->notes = $this->notes;
        }

        return $team;
    }

    /**
     * Get the competition this club is in
     *
     * @return Competition
     */
    public function getCompetition() : Competition
    {
        return $this->competition;
    }

    /**
     * Get the ID for this club
     *
     * @return string the id for this club
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Set the name for this club
     *
     * @param string $name the name for this club
     * @return Club this Club
     * @throws Exception When the provided club name is invalid
     */
    public function setName($name) : Club
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid club name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name for this club
     *
     * @return string the name for this club
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Add a contact to this club
     *
     * @param ClubContact $contact The contact to add to this club
     *
     * @return Club This Club instance
     *
     * @throws Exception If a contact with a duplicate ID within the club is added
     */
    public function addContact(ClubContact $contact) : Club
    {
        if ($this->hasContact($contact->getID())) {
            throw new Exception('club contacts with duplicate IDs within a club not allowed');
        }
        array_push($this->contacts, $contact);
        $this->contact_lookup->{$contact->getID()} = $contact;
        return $this;
    }

    /**
     * Returns an array of Contacts for this teclubam
     *
     * @return array<ClubContact>|null The contacts for this club
     */
    public function getContacts() : ?array
    {
        return $this->contacts;
    }

    /**
     * Returns the Contact with the requested ID, or throws if the ID is not found
     *
     * @param string $id The ID of the contact in this club to return
     *
     * @throws OutOfBoundsException If a Contact with the requested ID was not found
     *
     * @return ClubContact The requested contact for this club
     */
    public function getContact(string $id) : ClubContact
    {
        if (!property_exists($this->contact_lookup, $id)) {
            throw new OutOfBoundsException('Contact with ID "'.$id.'" not found');
        }
        return $this->contact_lookup->$id;
    }

    /**
     * Check if a contact with the given ID exists in this club
     *
     * @param string $id The ID of the contact to check
     *
     * @return bool True if the contact exists, otherwise false
     */
    public function hasContact(string $id) : bool
    {
        return property_exists($this->contact_lookup, $id);
    }

    /**
     * Check if this club has any contacts
     *
     * @return bool True if the club has contacts, otherwise false
     */
    public function hasContacts() : bool
    {
        return count($this->contacts) > 0;
    }

    /**
     * Delete a contact from the club
     *
     * @param string $id The ID of the contact to delete
     *
     * @return Club This Club instance
     */
    public function deleteContact(string $id) : Club
    {
        if (!$this->hasContact($id)) {
            return $this;
        }

        unset($this->contact_lookup->$id);
        $this->contacts = array_values(array_filter($this->contacts, fn(ClubContact $el): bool => $el->getID() !== $id));
        return $this;
    }

    /**
     * Set the notes for this club
     *
     * @param string|null $notes the notes for this club
     * @return Club this Club
     */
    public function setNotes(?string $notes) : Club
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Get the notes for this club
     *
     * @return ?string the notes for this club
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Does this club have any notes attached
     *
     * @return bool True if the club has notes, otherwise false
     */
    public function hasNotes() : bool
    {
        return $this->notes !== null;
    }

    /**
     * Add a team to this club
     *
     * @param CompetitionTeam $team the team to add
     * @return Club this Club
     */
    public function addTeam(CompetitionTeam $team) : Club
    {
        if ($this->hasTeam($team->getID())) {
            return $this;
        }
        $this->team_lookup->{$team->getID()} = $team;
        $team->setClubID($this->getID());
        return $this;
    }

    /**
     * Get the teams in this club
     *
     * @return array<CompetitionTeam>
     */
    public function getTeams() : array
    {
        $teams = [];
        foreach ($this->team_lookup as $team) {
            array_push($teams, $team);
        }
        return $teams;
    }

    /**
     * Check if the club has a team with the specified ID
     *
     * @param string $id The ID of the team
     * @return bool
     */
    public function hasTeam(string $id) : bool
    {
        return property_exists($this->team_lookup, $id);
    }

    /**
     * Delete a team from this club
     *
     * @param string $id The ID of the team to delete
     * @return Club this Club
     */
    public function deleteTeam(string $id) : Club
    {
        if ($this->hasTeam($id)) {
            $team = $this->team_lookup->$id;
            unset($this->team_lookup->$id);
            $team->setClubID(null);
        }

        return $this;
    }
}
