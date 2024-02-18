<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * A team definition
 */
final class Club implements JsonSerializable
{


    /** A unique ID for the club, e.g. 'CLUB1'.  This must be unique within the competition.  It must only contain letters (upper or lowercase), and numbers" */
    private string $id;

    /** The name for the club */
    private string $name;

    /** Free form string to add notes about a club.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** The Competition this club is in */
    private Competition $competition;

    /** A Lookup table from team IDs (including references) to the team */
    private object $team_lookup;

    public const UNKNOWN_CLUB_ID = 'UNKNOWN';
    public const UNKNOWN_CLUB_NAME = 'UNKNOWN';

    /**
     * Contains the club data of a competition, creating any metadata needed
     *
     * @param Competition $competition A link back to the Competition this Stage is in
     * @param string $club_id The ID of this Team
     * @param string $club_name The name of this Team
     */
    function __construct(Competition $competition, string $club_id, string $club_name)
    {
        if (strlen($club_id) > 100 || strlen($club_id) < 1) {
            throw new Exception('Invalid club ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $club_id)) {
            throw new Exception('Invalid club ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        if ($competition->hasClubWithID($club_id)) {
            throw new Exception('Club with ID "'.$club_id.'" already exists in the competition');
        }

        $this->competition = $competition;
        $this->id = $club_id;
        $this->setName($club_name);
        $this->team_lookup = new stdClass();
    }

    public static function loadFromData($competition, object $club_data) : Club
    {
        $club = new Club($competition, $club_data->id, $club_data->name);

        if (property_exists($club_data, 'notes')) {
            $club->setNotes($club_data->notes);
        }

        return $club;
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
     * Get the name for this club
     *
     * @return string the name for this club
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the name for this club
     *
     * @param string $name the name for this club
     */
    public function setName($name) : void
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid club name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
    }

    /**
     * Get the notes for this club
     *
     * @return string|null the notes for this club
     */
    public function getNotes() : string|null
    {
        return $this->notes;
    }

    /**
     * Set the notes for this club
     *
     * @param string|null $notes the notes for this club
     */
    public function setNotes(?string $notes) : void
    {
        $this->notes = $notes;
    }

    /**
     * Add a team to this club
     *
     * @param CompetitionTeam $team the team to add
     */
    public function addTeam(CompetitionTeam $team) : CompetitionTeam
    {
        $this->team_lookup->{$team->getID()} = $team;
        return $team;
    }

    public function deleteTeam(string $team_id) : void
    {
        if ($this->hasTeamWithID($team_id)) {
            unset($this->team_lookup->$team_id);
        }
    }

    /**
     * Get the teams in this club
     *
     * @return array<CompetitionTeam>
     */
    public function getTeams() : array
    {
        return array_keys(get_object_vars($this->team_lookup));
    }

    public function hasTeamWithID(string $team_id) : bool
    {
        return property_exists($this->team_lookup, $team_id);
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
}
