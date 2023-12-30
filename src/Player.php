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

    /** A unique ID for this player. This may be the player's registration number.  This must be unique within the team */
    public string $id;

    /** The name of this contact */
    public string $name;

    /** The player's shirt number */
    public ?int $number = null;

    /** Free form string to add notes about the player.  This can be used for arbitrary content that various implementations can use */
     public ?string $notes = null;

    /**
     * Contains the data of a player in a team
     *
     * @param object $player_data The data defining this Player
     */
    function __construct(object $player_data)
    {
        $this->id = $player_data->id;
        $this->name = $player_data->name;

        if (property_exists($player_data, 'number')) {
            $this->number = $player_data->number;
        }

        if (property_exists($player_data, 'notes')) {
            $this->notes = $player_data->notes;
        }
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
