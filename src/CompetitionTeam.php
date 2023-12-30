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

    /** Free form string to add notes about a team.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    public const UNKNOWN_TEAM_ID = 'UNKNOWN';
    public const UNKNOWN_TEAM_NAME = 'UNKNOWN';

    /** A Lookup table from contact IDs to the contact */
    private object $contact_lookup;

    /** A Lookup table from player IDs to the contact */
    private object $player_lookup;

    /**
     * Contains the team data of a competition, creating any metadata needed
     *
     * @param object $team_data The data defining this Team
     */
    function __construct(object $team_data)
    {
        $this->contact_lookup = new stdClass();
        $this->player_lookup = new stdClass();

        $this->id = $team_data->id;
        $this->name = $team_data->name;

        if (property_exists($team_data, 'contacts')) {
            foreach ($team_data->contacts as $contact_data) {
                $new_contact = new Contact($contact_data);
                array_push($this->contacts, $new_contact);
                if (property_exists($this->contact_lookup, $contact_data->id)) {
                    throw new Exception('Competition data failed validation: team contacts with duplicate IDs within a team not allowed');
                }
                $this->contact_lookup->{$contact_data->id} = $new_contact;
            }
        }

        if (property_exists($team_data, 'players')) {
            foreach ($team_data->players as $player_data) {
                $new_player = new Player($player_data);
                array_push($this->players, $new_player);
                if (property_exists($this->player_lookup, $new_player->id)) {
                    throw new Exception('Competition data failed validation: team players with duplicate IDs within a team not allowed');
                }
                $this->player_lookup->{$new_player->id} = $new_player;
            }

        }

        if (property_exists($team_data, 'notes')) {
            $this->notes = $team_data->notes;
        }
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
     * Get the players for this team
     *
     * @return array the players for this team
     */
    public function getPlayers() : array
    {
        return $this->players;
    }

    /**
     * Get the notes for this competition
     *
     * @return string|null the notes for this competition
     */
    public function getNotes() : string|null
    {
        return $this->notes;
    }

    /**
     * Returns an array of Contacts
     *
     * @return array the contacts for this team
     */
    public function getContacts() : array
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
     * Return the list of team definition suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $team = new stdClass();
        $team->id = $this->id;
        $team->name = $this->name;
        $team->contacts = $this->contacts;
        $team->players = $this->players;

        if ($this->notes !== null) {
            $team->notes = $this->notes;
        }

        return $team;
    }
}
