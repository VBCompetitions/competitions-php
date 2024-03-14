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
    /** @var string A unique ID for this player. This may be the player's registration number. This must be unique within the team */
    private string $id;

    /** @var string The name of this player */
    private string $name;

    /** @var ?int The player's shirt number */
    private ?int $number = null;

    /** @var ?string Free form string to add notes about the player. This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** @var CompetitionTeam The team this player belongs to */
    private CompetitionTeam $team;

    /**
     * Contains the data of a player in a team.
     *
     * @param CompetitionTeam $team The team to which this player belongs
     * @param string $id The ID of the player
     * @param string $name The name of the player
     * @throws Exception
     */
    function __construct(CompetitionTeam $team, string $id, string $name)
    {
        if (strlen($id) > 100 || strlen($id) < 1) {
            throw new Exception('Invalid player ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $id)) {
            throw new Exception('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        if ($team->hasPlayerWithID($id)) {
            throw new Exception('Player with ID "'.$id.'" already exists in the team');
        }

        $this->team = $team;
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

        if ($this->notes !== null) {
            $player->notes = $this->notes;
        }

        return $player;
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
