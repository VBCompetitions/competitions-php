<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * Represents a player in a team.
 */
final class Player implements JsonSerializable
{
    public const UNREGISTERED_PLAYER_ID = 'UNKNOWN';

    /** @var string A unique ID for this player. This may be the player's registration number. This must be unique within the team */
    private string $id;

    /** @var string The name of this player */
    private string $name;

    /** @var ?int The player's shirt number */
    private ?int $number = null;

    /**
     * @var array<PlayerTeam> An ordered list of teams the player is/has been registered for in this competition, in the order that they have been
     * registered (and therefore transferred in the case of more than one entry).  A player can only be registered with one
     * team at any time within this competition, meaning that if there are multiple teams listed, either all but the last
     * entry MUST have an \"until\" value, or there must be no \"from\" or \"until\" values in any entry
     */
    private array $teams = [];

    /** @var ?string Free form string to add notes about the player. This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** @var Competition The competition this player belongs to */
    private Competition $competition;

    /**
     * Contains the data of a player in a team.
     *
     * @param Competition $competition The competition to which this player belongs
     * @param string $id The ID of the player
     * @param string $name The name of the player
     * @throws Exception
     */
    function __construct(Competition $competition, string $id, string $name)
    {
        if (strlen($id) > 100 || strlen($id) < 1) {
            throw new Exception('Invalid player ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $id)) {
            throw new Exception('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        if ($competition->hasPlayer($id)) {
            throw new Exception('Player with ID "'.$id.'" already exists in the competition');
        }

        $this->competition = $competition;
        $this->id = $id;
        $this->setName($name);
    }

    /**
     * Load player data from an object.
     *
     * @param object $player_data The data defining this Player
     * @return Player The updated Player object
     */
    public function loadFromData(object $player_data) : Player
    {
        if (property_exists($player_data, 'number')) {
            $this->setNumber($player_data->number);
        }

        if (property_exists($player_data, 'teams')) {
            foreach ($player_data->teams as $player_team_data) {
                $this->appendTeamEntry((new PlayerTeam($this, $player_team_data->id))->loadFromData($player_team_data));
            }
        }

        if (property_exists($player_data, 'notes')) {
            $this->setNotes($player_data->notes);
        }

        return $this;
    }

    /**
     * Return the list of player data suitable for serialization.
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $player = new stdClass();
        $player->id = $this->id;
        $player->name = $this->name;

        if ($this->number !== null) {
            $player->number = $this->number;
        }

        if (count($this->teams) > 0) {
            $player->teams = $this->teams;
        }

        if ($this->notes !== null) {
            $player->notes = $this->notes;
        }

        return $player;
    }

    /**
     * Get the competition this contact belongs to
     *
     * @return Competition The competition this contact belongs to
     */
    public function getCompetition() : Competition
    {
        return $this->competition;
    }

    /**
     * Get the ID for this player.
     *
     * @return string The ID for this player
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Get the name for this player.
     *
     * @return string The name for this player
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the name for this player.
     *
     * @param string $name The name for this player
     * @throws Exception
     */
    public function setName(string $name) : void
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid player name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
    }

    /**
     * Get the shirt number for this player.
     *
     * @return ?int The shirt number for this player
     */
    public function getNumber() : ?int
    {
        return $this->number;
    }

    /**
     * Set the shirt number for this player.
     *
     * @param ?int $number The shirt number for this player
     * @throws Exception
     */
    public function setNumber(?int $number) : void
    {
        if ($number !== null && $number < 1) {
            throw new Exception('Invalid player number "'.$number.'": must be greater than 1');
        }
        $this->number = $number;
    }

    /**
     * Append a PlayerTeam entry to the end of the list of teams that the player has been registered with
     *
     * @param PlayerTeam $team_entry the PlayerTeam entry to add
     * @return Player this Player
     */
    public function appendTeamEntry($team_entry) : Player
    {
        array_push($this->teams, $team_entry);
        return $this;
    }

    /**
     * Get the list of teams this player has been registered with
     *
     * @return array<PlayerTeam> The team this player belongs to
     */
    public function getTeamEntries() : array
    {
        return $this->teams;
    }

    /**
     * Get the most recent PlayerTeam that the player has been registered with
     *
     * @return PlayerTeam|null The most recent PlayerTeam that the player has been registered with
     */
    public function getLatestTeamEntry() : ?PlayerTeam
    {
        if (count($this->teams) === 0) {
            return null;
        }

        return $this->teams[count($this->teams) - 1];
    }

    /**
     * Get the most recent CompetitionTeam that the player has been registered with
     *
     * @return CompetitionTeam|null The most recent CompetitionTeam that the player has been registered with
     */
    public function getCurrentTeam() : ?CompetitionTeam
    {
        $id = count($this->teams) > 0 ? $this->teams[count($this->teams) - 1]->getID() : '';
        return $this->competition->getTeam($id);
    }

    /**
     * Check if a team entry exists with the given team ID
     *
     * @param string $id the team ID to check for
     *
     * @return bool whether the player has ever been registered to the team with the given ID
     */
    public function hasTeamEntry($id) : bool
    {
        foreach ($this->teams as $team) {
            if ($team->getID() === $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes up to "count" elements from the array of team entries, starting at element "start".
     * This calls "splice" directly so has the same behaviour in terms of negative values and
     * values out of bounds
     *
     * @param int $start
     * @param int $count
     *
     * @return Player this Player
     */
    public function spliceTeamEntries($start, $count) : Player
    {
        array_splice($this->teams, $start, $count);
        return $this;
    }

    /**
     * Get the notes for this player.
     *
     * @return ?string The notes for this player
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Set the notes for this player.
     *
     * @param ?string $notes The notes for this player
     */
    public function setNotes(?string $notes) : void
    {
        $this->notes = $notes;
    }
}
