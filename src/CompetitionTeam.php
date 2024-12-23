<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;

/**
 * A team definition
 */
final class CompetitionTeam implements JsonSerializable
{
    /** @var string A unique ID for the team, e.g. 'TM1'. This is used in the rest of the instance document to specify the team */
    private string $id;

    /** @var string The name for the team */
    private string $name;

    /** @var array The contacts for the Team */
    private array $contacts = [];

    /** @var ?Club The club this team is in */
    private ?Club $club = null;

    /** @var ?string Free form string to add notes about a team.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** @var Competition The Competition this team is in */
    private Competition $competition;

    public const UNKNOWN_TEAM_ID = 'UNKNOWN';
    public const UNKNOWN_TEAM_NAME = 'UNKNOWN';

    /** @var object A Lookup table from contact IDs to the contact */
    private object $contact_lookup;

    /**
     * Contains the team data of a competition, creating any metadata needed
     *
     * @param Competition $competition A link back to the Competition this Team is in
     * @param string $id The unique ID for the team
     * @param string $name The name for the team
     */
    function __construct(Competition $competition, string $id, string $name)
    {
        if (strlen($id) > 100 || strlen($id) < 1) {
            throw new Exception('Invalid team ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $id)) {
            throw new Exception('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        if ($competition->hasTeam($id)) {
            throw new Exception('Team with ID "'.$id.'" already exists in the competition');
        }

        $this->competition = $competition;
        $this->id = $id;
        $this->setName($name);
        $this->contact_lookup = new stdClass();
    }

    /**
     * Load team data from an object
     *
     * @param object $team_data The data defining this Team
     *
     * @return CompetitionTeam
     */
    public function loadFromData(object $team_data) : CompetitionTeam
    {
        if (property_exists($team_data, 'contacts')) {
            foreach ($team_data->contacts as $contact_data) {
                $this->addContact((new TeamContact($this, $contact_data->id, $contact_data->roles))->loadFromData($contact_data));
            }
        }

        if (property_exists($team_data, 'club')) {
            $this->setClubID($team_data->club);
            $this->getClub()->addTeam($this);
        }

        if (property_exists($team_data, 'notes')) {
            $this->notes = $team_data->notes;
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
        if (count($this->contacts) > 0) {
            $team->contacts = $this->contacts;
        }
        if ($this->club !== null) {
            $team->club = $this->club->getID();
        }
        if ($this->notes !== null) {
            $team->notes = $this->notes;
        }
        return $team;
    }

    /**
     * Get the competition this team is in
     *
     * @return Competition
     */
    public function getCompetition() : Competition
    {
        return $this->competition;
    }

    /**
     * Get the ID for this team
     *
     * @return string The ID for this team
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Set the name for this team
     *
     * @param string $name The name for this team
     *
     * @return CompetitionTeam This CompetitionTeam
     */
    public function setName($name) : CompetitionTeam
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid team name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name for this team
     *
     * @return string The name for this team
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the club ID for this team
     *
     * @param string|null $id The ID of the club this team is in
     *
     * @return CompetitionTeam This competition team
     */
    public function setClubID(?string $id) : CompetitionTeam
    {
        if ($id === null) {
            if ($this->club->hasTeam($this->id)) {
                $this->club->deleteTeam($this->id);
            }
            $this->club = null;
            return $this;
        }

        if ($this->club !== null && $id === $this->club->getID()) {
            return $this;
        }

        if (!$this->competition->hasClub($id)) {
            throw new Exception('No club with ID "'.$id.'" exists');
        }

        $this->club = $this->competition->getClub($id);
        $this->club->addTeam($this);

        return $this;
    }

    /**
     * Get the club for this team
     *
     * @return ?Club The club this team is in
     */
    public function getClub() : ?Club
    {
        return $this->club;
    }

    /**
     * Does this team have a club that it belongs to
     *
     * @return bool True if the team belongs to a club, otherwise false
     */
    public function hasClub() : bool
    {
        return !is_null($this->club);
    }

    /**
     * Get the notes for this team
     *
     * @return ?string the notes for this team
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Set the notes for this team
     *
     * @param string|null $notes the notes for this team
     *
     * @return CompetitionTeam This competition team
     */
    public function setNotes(?string $notes) : CompetitionTeam
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Does this team have any notes attached
     *
     * @return bool True if the team has notes, otherwise false
     */
    public function hasNotes() : bool
    {
        return !is_null($this->notes);
    }

    /**
     * Add a contact to this team
     *
     * @param TeamContact $contact The contact to add to this team
     *
     * @return CompetitionTeam This CompetitionTeam instance
     *
     * @throws Exception If a contact with a duplicate ID within the team is added
     */
    public function addContact(TeamContact $contact) : CompetitionTeam
    {
        if ($this->hasContact($contact->getID())) {
            throw new Exception('team contacts with duplicate IDs within a team not allowed');
        }
        array_push($this->contacts, $contact);
        $this->contact_lookup->{$contact->getID()} = $contact;
        return $this;
    }

    /**
     * Returns an array of Contacts for this team
     *
     * @return array<TeamContact>|null The contacts for this team
     */
    public function getContacts() : ?array
    {
        return $this->contacts;
    }

    /**
     * Returns the Contact with the requested ID, or throws if the ID is not found
     *
     * @param string $id The ID of the contact in this team to return
     *
     * @throws OutOfBoundsException If a Contact with the requested ID was not found
     *
     * @return TeamContact The requested contact for this team
     */
    public function getContact(string $id) : TeamContact
    {
        if (!property_exists($this->contact_lookup, $id)) {
            throw new OutOfBoundsException('Contact with ID "'.$id.'" not found');
        }
        return $this->contact_lookup->$id;
    }

    /**
     * Check if a contact with the given ID exists in this team
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
     * Check if this team has any contacts
     *
     * @return bool True if the team has contacts, otherwise false
     */
    public function hasContacts() : bool
    {
        return count($this->contacts) > 0;
    }

    /**
     * Delete a contact from the team
     *
     * @param string $id The ID of the contact to delete
     *
     * @return CompetitionTeam This CompetitionTeam instance
     */
    public function deleteContact(string $id) : CompetitionTeam
    {
        if (!$this->hasContact($id)) {
            return $this;
        }

        unset($this->contact_lookup->$id);
        $this->contacts = array_values(array_filter($this->contacts, fn(TeamContact $el): bool => $el->getID() !== $id));
        return $this;
    }

    /**
     * Get the players for this team
     *
     * @return array<Player> The players for this team
     */
    public function getPlayers () : array
    {
        return $this->competition->getPlayersInTeam($this->id);
    }

    /**
     * Check if a player with the given ID exists in this team
     *
     * @param string $id The ID of the player to check
     *
     * @return bool True if the player exists, otherwise false
     */
    public function hasPlayer ($id) : bool
    {
        return $this->competition->hasPlayerInTeam($id, $this->id);
    }

    /**
     * Check if this team has any players
     *
     * @return bool True if the team has players, otherwise false
     */
    public function hasPlayers () : bool
    {
        return $this->competition->hasPlayersInTeam($this->id);
    }
}
