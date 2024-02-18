<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;

/**
 * A team definition
 */
final class Player implements JsonSerializable
{
    // TODO - private properties with getters and setters

    /** A unique ID for this player. This may be the player's registration number.  This must be unique within the team */
    private string $id;

    /** The name of this contact */
    private string $name;

    /** The player's shirt number */
    private ?int $number = null;

    /** Free form string to add notes about the player.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /**
     * Contains the data of a player in a team
     *
     * @param object $player_data The data defining this Player
     */
    function __construct(CompetitionTeam $team, string $id, string $name)
    {
        if (strlen($id) > 100 || strlen($id) < 1) {
            throw new Exception('Invalid player ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $id)) {
            throw new Exception('Invalid player ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        if ($team->hasContactWithID($id)) {
            throw new Exception('Player with ID "'.$id.'" already exists in the team');
        }

        $this->id = $id;
        $this->setName($name);
    }

    public static function loadFromData(CompetitionTeam $competition_team, object $player_data) : Player
    {
        $player = new Player($competition_team, $player_data->id, $player_data->name);

        if (property_exists($player_data, 'number')) {
            $player->setNumber($player_data->number);
        }

        if (property_exists($player_data, 'notes')) {
            $player->setNotes($player_data->notes);
        }

        return $player;
    }

    /**
     * Get the ID for this player
     *
     * @return string the id for this player
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Get the name for this player
     *
     * @return string the name for this player
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the name for this player
     *
     * @param string $name the name for this player
     */
    public function setName($name) : void
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid player name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
    }

    /**
     * Get the shirt number for this player
     *
     * @return int|null the shirt number for this player
     */
    public function getNumber() : int|null
    {
        return $this->number;
    }

    /**
     * Set the notes for this team
     *
     * @param int|null $notes the notes for this team
     */
    public function setNumber(?int $number) : void
    {
        if ($number !== null && $number < 1) {
            throw new Exception('Invalid player number "'.$number.'": must be greater than 1');
        }
        $this->number = $number;
    }

    /**
     * Get the notes for this player
     *
     * @return string|null the notes for this player
     */
    public function getNotes() : string|null
    {
        return $this->notes;
    }

    /**
     * Set the notes for this player
     *
     * @param string|null $notes the notes for this player
     */
    public function setNotes(?string $notes) : void
    {
        $this->notes = $notes;
    }
    /**
     * Return the list of team definition suitable for saving into a competition file
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
}
