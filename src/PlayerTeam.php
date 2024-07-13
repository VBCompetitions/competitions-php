<?php

namespace VBCompetitions\Competitions;

use DateTime;
use Exception;
use JsonSerializable;
use stdClass;

/**
 * Represents a team that the player is/has been registered to
 */
final class PlayerTeam implements JsonSerializable
{
    /** @var string The team ID that the player is/was registered with */
    private string $id;

    /** @var string|null The date from which the player is/was registered with this team.  When this is not present, there should not be any \"from\"
     * or \"until\" values in any entry in this player's \"teams\" array */
    private ?string $from;

    /** @var string|null The date up to which the player was registered with this team.  When a \"from\" date is specified and this is not, it should
     * be taken that a player is still registered with this team */
    private ?string $until;

    /** @var string|null Free form string to add notes about this team registration entry for the player */
    private ?string $notes;

    /** @var Player  The player associated with this record */
    private Player $player;

    /**
     * @param Player $player A link back to the Player for this record
     * @param string $id The ID of the team that the player's registry represents
     *
     * @throws {Error} If the ID is invalid
     */
    function __construct($player, $id)
    {
        if (strlen($id) > 100 || strlen($id) < 1) {
            throw new Exception('Invalid team ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $id)) {
            throw new Exception('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        $this->player = $player;
        $this->id = $id;
        $this->from = null;
        $this->until = null;
        $this->notes = null;
    }

    /**
     * Load player team data from an object.
     *
     * @param object $player_team_data The data defining this PlayerTeam entry
     *
     * @return Player The updated PlayerTeam object
     */
    public function loadFromData($player_team_data) : PlayerTeam
    {
        if (property_exists($player_team_data, 'from')) {
            $this->setFrom($player_team_data->from);
        }

        if (property_exists($player_team_data, 'until')) {
            $this->setUntil($player_team_data->until);
        }

        if (property_exists($player_team_data, 'notes')) {
            $this->setNotes($player_team_data->notes);
        }

        return $this;
    }

    /**
     * Return the playerTeam data in a form suitable for serializing
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $player_team = new stdClass();
        $player_team->id = $this->id;

        if ($this->from !== null) {
            $player_team->from = $this->from;
        }

        if ($this->until !== null) {
            $player_team->until = $this->until;
        }

        if ($this->notes !== null) {
            $player_team->notes = $this->notes;
        }

        return $player_team;
    }

    /**
     * Get the player this player team entry belongs to
     *
     * @return Player The player this player team entry belongs to
     */
    public function getPlayer() : Player
    {
        return $this->player;
    }

    /**
     * Get the ID for the Team this entry represents.
     *
     * @return string The ID of the team for this playerTeam entry
     */
    public function getID() {
        return $this->id;
    }

    /**
     * Get the from date for this player team entry.
     *
     * @return string The from date for this player team entry
     */
    public function getFrom() {
        return $this->from;
    }

    /**
     * Set the from date for this player team entry
     *
     * @param string $from The from date for this player team entry
     *
     * @throws Exception If the from date is invalid
     *
     * @return PlayerTeam This player team entry
     */
    public function setFrom($from) {
        if (!preg_match('/^[0-9]{4}-(0[0-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $from)) {
            throw new Exception('Invalid date "'.$from.'": must contain a value of the form "YYYY-MM-DD"');
        }

        $d = DateTime::createFromFormat('Y-m-d', $from);
        if ($d === false || $d->format('Y-m-d') !== $from) {
            throw new Exception('Invalid date "'.$from.'": date does not exist');
        }

        $this->from = $from;
        return $this;
    }

    /**
     * Get the until date for this player team entry
     *
     * @return string|null The until date for this player team entry
     */
    public function getUntil() : ?string
    {
        return $this->until;
    }

    /**
     * Set the until date for this player team entry
     *
     * @param string|null $until The until date for this player team entry
     *
     * @throws Exception If the until date is invalid
     *
     * @return PlayerTeam This player team entry
     */
    public function setUntil($until) {
        if (!preg_match('/^[0-9]{4}-(0[0-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $until)) {
            throw new Exception('Invalid date "'.$until.'": must contain a value of the form "YYYY-MM-DD"');
        }

        $d = DateTime::createFromFormat('Y-m-d', $until);
        if ($d === false || $d->format('Y-m-d') !== $until) {
            throw new Exception('Invalid date "'.$until.'": date does not exist');
        }

        $this->until = $until;
        return $this;
    }

    /**
     * Get the notes for this player.
     *
     * @return {string|null} The notes for this player
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Set the notes for this player.
     *
     * @param {string|null} notes The notes for this player
     *
     * @return PlayerTeam This player team entry
     */
    public function setNotes($notes) : PlayerTeam
    {
        $this->notes = $notes;
        return $this;
    }
}
