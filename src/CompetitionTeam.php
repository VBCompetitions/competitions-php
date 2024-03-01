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

    /** @var array A list of players for a team */
    private array $players = [];

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

    /** @var object A Lookup table from player IDs to the contact */
    private object $player_lookup;

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

        if ($competition->hasTeamWithID($id)) {
            throw new Exception('Team with ID "'.$id.'" already exists in the competition');
        }

        $this->competition = $competition;
        $this->id = $id;
        $this->setName($name);
        $this->contact_lookup = new stdClass();
        $this->player_lookup = new stdClass();
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
                $roles = [];
                foreach ($contact_data->roles as $contact_role) {
                    $role = match ($contact_role) {
                        'secretary' => ContactRole::SECRETARY,
                        'treasurer' => ContactRole::TREASURER,
                        'manager' => ContactRole::MANAGER,
                        'captain' => ContactRole::CAPTAIN,
                        'coach' => ContactRole::COACH,
                        'assistantCoach' => ContactRole::ASSISTANT_COACH,
                        'medic' => ContactRole::MEDIC,
                    };
                    array_push($roles, $role);
                }
                $this->addContact((new Contact($this, $contact_data->id, $roles))->loadFromData($contact_data));
            }
        }

        if (property_exists($team_data, 'players')) {
            foreach ($team_data->players as $player_data) {
                $this->addPlayer((new Player($this, $player_data->id, $player_data->name))->loadFromData($player_data));
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
        if (count($this->players) > 0) {
            $team->players = $this->players;
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
     * @param ?string $club_id The ID of the club this team is in
     *
     * @return CompetitionTeam This competition team
     */
    public function setClubID(?string $club_id) : CompetitionTeam
    {
        if ($club_id === null) {
            if ($this->club->hasTeamWithID($this->id)) {
                $this->club->deleteTeam($this->id);
            }
            $this->club = null;
            return $this;
        }

        if ($this->club !== null && $club_id === $this->club->getID()) {
            return $this;
        }

        if (!$this->competition->hasClubWithID($club_id)) {
            throw new Exception('No club with ID "'.$club_id.'" exists');
        }

        $this->club = $this->competition->getClubById($club_id);
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
     * @param ?string $notes the notes for this team
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
     * @param Contact $contact The contact to add to this team
     *
     * @return CompetitionTeam This CompetitionTeam instance
     *
     * @throws Exception If a contact with a duplicate ID within the team is added
     */
    public function addContact(Contact $contact) : CompetitionTeam
    {
        if ($this->hasContactWithID($contact->getID())) {
            throw new Exception('team contacts with duplicate IDs within a team not allowed');
        }
        array_push($this->contacts, $contact);
        $this->contact_lookup->{$contact->getID()} = $contact;
        return $this;
    }

    /**
     * Returns an array of Contacts for this team
     *
     * @return ?array<Contact> The contacts for this team
     */
    public function getContacts() : ?array
    {
        return $this->contacts;
    }

    /**
     * Returns the Contact with the requested ID, or throws if the ID is not found
     *
     * @param string $contact_id The ID of the contact in this team to return
     *
     * @throws OutOfBoundsException If a Contact with the requested ID was not found
     *
     * @return Contact The requested contact for this team
     */
    public function getContactByID(string $contact_id) : Contact
    {
        if (!property_exists($this->contact_lookup, $contact_id)) {
            throw new OutOfBoundsException('Contact with ID "'.$contact_id.'" not found');
        }
        return $this->contact_lookup->$contact_id;
    }

    /**
     * Check if a contact with the given ID exists in this team
     *
     * @param string $contact_id The ID of the contact to check
     *
     * @return bool True if the contact exists, otherwise false
     */
    public function hasContactWithID(string $contact_id) : bool
    {
        return property_exists($this->contact_lookup, $contact_id);
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
     * @param string $contact_id The ID of the contact to delete
     *
     * @return CompetitionTeam This CompetitionTeam instance
     */
    public function deleteContact(string $contact_id) : CompetitionTeam
    {
        if (!$this->hasContactWithID($contact_id)) {
            return $this;
        }

        unset($this->contact_lookup->$contact_id);
        $this->contacts = array_filter($this->contacts, fn(Contact $el): bool => $el->getID() !== $contact_id);
        return $this;
    }

    /**
     * Add a player to this team
     *
     * @param Player $player The player to add to this team
     *
     * @return CompetitionTeam This CompetitionTeam instance
     *
     * @throws Exception If a player with a duplicate ID within the team is added
     */
    public function addPlayer(Player $player) : CompetitionTeam
    {
        if ($this->hasPlayerWithID($player->getID())) {
            throw new Exception('team players with duplicate IDs within a team not allowed');
        }

        array_push($this->players, $player);
        $this->player_lookup->{$player->getID()} = $player;
        return $this;
    }

    /**
     * Get the players for this team
     *
     * @return ?array<Player> The players for this team
     */
    public function getPlayers() : ?array
    {
        return $this->players;
    }

    /**
     * Returns the Player with the requested ID, or throws if the ID is not found
     *
     * @param string $player_id The ID of the player in this team to return
     *
     * @throws OutOfBoundsException If a Player with the requested ID was not found
     *
     * @return Player The requested player for this team
     */
    public function getPlayerByID(string $player_id) : Player
    {
        if (!property_exists($this->player_lookup, $player_id)) {
            throw new OutOfBoundsException('Player with ID "'.$player_id.'" not found');
        }
        return $this->player_lookup->$player_id;
    }

    /**
     * Check if a player with the given ID exists in this team
     *
     * @param string $player_id The ID of the player to check
     *
     * @return bool True if the player exists, otherwise false
     */
    public function hasPlayerWithID(string $player_id) : bool
    {
        return property_exists($this->player_lookup, $player_id);
    }

    /**
     * Check if this team has any players
     *
     * @return bool True if the team has players, otherwise false
     */
    public function hasPlayers() : bool
    {
        return count($this->players) > 0;
    }

    /**
     * Delete a player from the team
     *
     * @param string $player_id The ID of the player to delete
     *
     * @return CompetitionTeam This CompetitionTeam instance
     */
    public function deletePlayer(string $player_id) : CompetitionTeam
    {
        if (!$this->hasPlayerWithID($player_id)) {
            return $this;
        }

        unset($this->player_lookup->$player_id);
        $this->players = array_filter($this->players, fn(Player $el): bool => $el->getID() !== $player_id);
        return $this;
    }
}
