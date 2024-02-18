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
    /** A unique ID for the team, e.g. 'TM1'. This is used in the rest of the instance document to specify the team */
    private string $id;

    /** The name for the team */
    private string $name;

    /** The contacts for the Team */
    private array $contacts = [];

    /** A list of players for a team */
    private array $players = [];

    /** The club this team is in */
    private ?Club $club = null;

    /** Free form string to add notes about a team.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** The Competition this team is in */
    private Competition $competition;

    public const UNKNOWN_TEAM_ID = 'UNKNOWN';
    public const UNKNOWN_TEAM_NAME = 'UNKNOWN';

    /** A Lookup table from contact IDs to the contact */
    private object $contact_lookup;

    /** A Lookup table from player IDs to the contact */
    private object $player_lookup;

    /**
     * Contains the team data of a competition, creating any metadata needed
     *
     * @param Competition $competition A link back to the Competition this Stage is in
     * @param object $team_data The data defining this Team
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

    public static function loadFromData($competition, object $team_data) : CompetitionTeam
    {
        $team = new CompetitionTeam($competition, $team_data->id, $team_data->name);

        if (property_exists($team_data, 'contacts')) {
            foreach ($team_data->contacts as $contact_data) {
                $team->addContact(Contact::loadFromData($team, $contact_data));
            }
        }

        if (property_exists($team_data, 'players')) {
            foreach ($team_data->players as $player_data) {
                $team->addPlayer(Player::loadFromData($team, $player_data));
            }
        //     $this->players = [];
        //     foreach ($team_data->players as $player_data) {
        //         $new_player = new Player($player_data);
        //         array_push($this->players, $new_player);
        //         if (property_exists($this->player_lookup, $new_player->getID())) {
        //             throw new Exception('Competition data failed validation: team players with duplicate IDs within a team not allowed');
        //         }
        //         $this->player_lookup->{$new_player->getID()} = $new_player;
        //     }
        }

        if (property_exists($team_data, 'club')) {
            $team->setClub($team_data->club);
            $team->getClub()->addTeam($team);
        }

        if (property_exists($team_data, 'notes')) {
            $team->notes = $team_data->notes;
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
     * @return string the id for this team
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Get the name for this team
     *
     * @return string the name for this team
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the name for this team
     *
     * @param string $name the name for this team
     */
    public function setName($name) : void
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid team name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
    }

    /**
     * Get the club for this team
     *
     * @return Club|null the club this team is in
     */
    public function getClub() : Club|null
    {
        return $this->club;
    }

    /**
     * Set the club ID for this team
     *
     * @param string|null $club_id the ID of the club this team is in
     */
    public function setClub(?string $club_id) : void
    {
        if ($club_id === null) {
            $this->club = $club_id;
        } else {
            try {
                $this->club = $this->competition->getClubById($club_id);
            } catch (\Throwable $_) {
                throw new Exception('No club with ID "'.$club_id.'" exists');
            }
        }
    }

    /**
     * Get the notes for this team
     *
     * @return string|null the notes for this team
     */
    public function getNotes() : string|null
    {
        return $this->notes;
    }

    /**
     * Set the notes for this team
     *
     * @param string|null $notes the notes for this team
     */
    public function setNotes(?string $notes) : void
    {
        $this->notes = $notes;
    }

    /**
     * Returns an array of Contacts
     *
     * @return array<Contact>|null the contacts for this team
     */
    public function getContacts() : array|null
    {
        return $this->contacts;
    }

    /**
     * Returns the Contact with the requested ID, or throws if the ID is not found
     *
     * @param string $contact_id The ID of the contact in this team to return
     *
     * @throws OutOfBoundsException A Contact with the requested ID was not found
     *
     * @return Contact the requested contact for this team
     */
    public function getContactByID(string $contact_id) : Contact
    {
        if (!property_exists($this->contact_lookup, $contact_id)) {
            throw new OutOfBoundsException('Contact with ID '.$contact_id.' not found');
        }
        return $this->contact_lookup->$contact_id;
    }

    /**
     * Add the contact to this team
     *
     * @param Contact $contact the contact to add to this team
     *
     * @return int the number of contacts stored in this team
     */
    public function addContact(Contact $contact) : int
    {
        // TODO - screen for duplicates
        array_push($this->contacts, $contact);
        $this->contact_lookup->{$contact->getID()} = $contact;
        return count($this->contacts);
    }

    public function hasContactWithID(string $contact_id) : bool
    {
        return property_exists($this->contact_lookup, $contact_id);
    }

    /**
     * Get the players for this team
     *
     * @return array<Player>|null the players for this team
     */
    public function getPlayers() : array|null
    {
        return $this->players;
    }

    /**
     * Returns the Player with the requested ID, or throws if the ID is not found
     *
     * @param string $player_id The ID of the player in this team to return
     *
     * @throws OutOfBoundsException A Player with the requested ID was not found
     *
     * @return Player the requested player for this team
     */
    public function getPlayerByID(string $player_id) : Player
    {
        if (!property_exists($this->player_lookup, $player_id)) {
            throw new OutOfBoundsException('Player with ID '.$player_id.' not found');
        }
        return $this->player_lookup->$player_id;
    }

    /**
     * Add the player to this team
     *
     * @param Player $player the player to add to this team
     *
     * @return int the number of players stored in this team
     */
    public function addPlayer(Player $player) : int
    {
        // TODO - screen for duplicates
        array_push($this->players, $player);
        $this->player_lookup->{$player->getID()} = $player;
        return count($this->players);
    }

    public function hasPlayerWithID(string $player_id) : bool
    {
        return property_exists($this->player_lookup, $player_id);
    }

    public function hasPlayers() : bool
    {
        return count($this->players) > 0;
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
}
