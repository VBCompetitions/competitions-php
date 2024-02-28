<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use OutOfBoundsException;
use stdClass;

/**
 * It can be useful to still present something to the user about the later stages of a competition, even if the teams playing in that stage is not yet known. This defines what should be presented in any application handling this competition's data in such cases
 */
final class IfUnknown implements JsonSerializable, MatchContainerInterface
{
    /** @var array An array of string values to be presented in the case that the teams in this stage are not yet known, typically as an explanation of what this stage will contain (e.g. 'The crossover games will be between the top two teams in each pool') */
    private array $description = [];

    /** @var array An array of matches in this group (or breaks in play) */
    private array $matches = [];

    /** @var Stage The Stage this IfUnknown is in */
    private Stage $stage;

    /** @var bool Whether matches have courts */
    private bool $matches_have_courts = false;

    /** @var bool Whether matches have dates */
    private bool $matches_have_dates = false;

    /** @var bool Whether matches have durations */
    private bool $matches_have_durations = false;

    /** @var bool Whether matches have MVPs */
    private bool $matches_have_mvps = false;

    /** @var bool Whether matches have managers */
    private bool $matches_have_managers = false;

    /** @var bool Whether matches have notes */
    private bool $matches_have_notes = false;

    /** @var bool Whether matches have officials */
    private bool $matches_have_officials = false;

    /** @var bool Whether matches have starts */
    private bool $matches_have_starts = false;

    /** @var bool Whether matches have venues */
    private bool $matches_have_venues = false;

    /** @var bool Whether matches have warmups */
    private bool $matches_have_warmups = false;

    /** @var object A Lookup table from match IDs to that match */
    private object $match_lookup;

    /**
     * Contains the ifUnknown data of a competition, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this IfUnknown is in
     * @param array $description An array of string values to be presented in the case that the teams in this stage are not yet known
     */
    function __construct(Stage $stage, array $description)
    {
        $this->stage = $stage;
        $this->description = $description;
        $this->match_lookup = new stdClass();
    }

    /**
     * Load data from an object into the IfUnknown instance
     *
     * @param object $if_unknown_data The data defining this IfUnknown
     * @return IfUnknown
     */
    public function loadFromData(object $if_unknown_data) : IfUnknown
    {
        foreach ($if_unknown_data->matches as $match) {
            if ($match->type === 'match') {
                $new_match = new IfUnknownMatch($this, $match->id);
                $new_match->loadFromData($match);
                $this->addMatch($new_match);
            } elseif ($match->type === 'break') {
                $this->addBreak((new IfUnknownBreak($this))->loadFromData($match));
            }
        }

        return $this;
    }

    /**
     * Return the "ifUnknown" data suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $ifUnknown = new stdClass();

        $ifUnknown->description = $this->description;

        $ifUnknown->matches = $this->matches;

        return $ifUnknown;
    }

    /**
     * Get the stage this IfUnknown belongs to
     *
     * @return Stage
     */
    public function getStage() : Stage
    {
        return $this->stage;
    }

    /**
     * Get the competition this IfUnknown belongs to
     *
     * @return Competition
     */
    public function getCompetition() : Competition
    {
        return $this->stage->getCompetition();
    }

    /**
     * Get the description of this IfUnknown
     *
     * @return array
     */
    public function getDescription() : array
    {
        return $this->description;
    }

    /**
     * Add a match to this IfUnknown
     *
     * @param IfUnknownMatch $match The match to add
     * @return IfUnknown
     */
    public function addMatch(IfUnknownMatch $match) : IfUnknown
    {
        array_push($this->matches, $match);
        $this->match_lookup->{$match->getID()} = $match;
        if ($match->getCourt() !== null) {
            $this->matches_have_courts = true;
        }
        if ($match->getDate() !== null) {
            $this->matches_have_dates = true;
        }
        if ($match->getDuration() !== null) {
            $this->matches_have_durations = true;
        }
        if ($match->getMVP() !== null) {
            $this->matches_have_mvps = true;
        }
        if ($match->getManager() !== null) {
            $this->matches_have_managers = true;
        }
        if ($match->getNotes() !== null) {
            $this->matches_have_notes = true;
        }
        if ($match->getOfficials() !== null) {
            $this->matches_have_officials = true;
        }
        if ($match->getStart() !== null) {
            $this->matches_have_starts = true;
        }
        if ($match->getVenue() !== null) {
            $this->matches_have_venues = true;
        }
        if ($match->getWarmup() !== null) {
            $this->matches_have_warmups = true;
        }
        return $this;
    }

    /**
     * Add a break to this IfUnknown
     *
     * @param IfUnknownBreak $break The break to add
     * @return IfUnknown
     */
    public function addBreak(IfUnknownBreak $break) : IfUnknown
    {
        array_push($this->matches, $break);
        return $this;
    }

    /**
     * Get the matches in this IfUnknown
     *
     * @param string|null $team_id The ID of the team
     * @param int $flags Flags to filter matches
     * @return array
     */
    public function getMatches(string $team_id = null, int $flags = 0) : array
    {
        return $this->matches;
    }

    /**
     * Check if a match with the given ID exists in this IfUnknown
     *
     * @param mixed $id The ID of the match
     * @return bool
     */
    public function hasMatchWithID($id) : bool
    {
        return property_exists($this->match_lookup, $id);
    }

    /**
     * Get the IDs of the teams in this IfUnknown
     *
     * @param int $flags Flags to filter teams
     * @return array
     */
    public function getTeamIDs(int $flags = 0) : array
    {
        return [];
    }

    /**
     * Get the match type of this IfUnknown
     *
     * @return MatchType
     */
    public function getMatchType() : MatchType
    {
        return MatchType::CONTINUOUS;
    }

    /**
     * Get the match with the specified ID
     *
     * @param string $match_id The ID of the match
     * @return IfUnknownMatch The requested match
     * @throws OutOfBoundsException When the match with the specified ID is not found
     */
    public function getMatchById(string $match_id) : IfUnknownMatch
    {
        if (property_exists($this->match_lookup, $match_id)) {
            return $this->match_lookup->{$match_id};
        }
        throw new OutOfBoundsException('Match with ID '.$match_id.' not found', 1);
    }

    /**
     * Check if matches have courts
     *
     * @return bool
     */
    public function matchesHaveCourts() : bool
    {
        return $this->matches_have_courts;
    }

    /**
     * Check if matches have dates
     *
     * @return bool
     */
    public function matchesHaveDates() : bool
    {
        return $this->matches_have_dates;
    }

    /**
     * Check if matches have durations
     *
     * @return bool True if any match has a duration, false otherwise
     */
    public function matchesHaveDurations() : bool
    {
        return $this->matches_have_durations;
    }

    /**
     * Check if matches have managers
     *
     * @return bool True if any match has a manager, false otherwise
     */
    public function matchesHaveManagers() : bool
    {
        return $this->matches_have_managers;
    }

    /**
     * Check if matches have MVPs
     *
     * @return bool True if any match has an MVP, false otherwise
     */
    public function matchesHaveMVPs() : bool
    {
        return $this->matches_have_mvps;
    }

    /**
     * Check if matches have notes
     *
     * @return bool True if any match has notes, false otherwise
     */
    public function matchesHaveNotes() : bool
    {
        return $this->matches_have_notes;
    }

    /**
     * Check if matches have officials
     *
     * @return bool True if any match has officials, false otherwise
     */
    public function matchesHaveOfficials() : bool
    {
        return $this->matches_have_officials;
    }

    /**
     * Check if matches have starts
     *
     * @return bool True if any match has a start time, false otherwise
     */
    public function matchesHaveStarts() : bool
    {
        return $this->matches_have_starts;
    }

    /**
     * Check if matches have venues
     *
     * @return bool True if any match has a venue, false otherwise
     */
    public function matchesHaveVenues() : bool
    {
        return $this->matches_have_venues;
    }

    /**
     * Check if matches have warmups
     *
     * @return bool True if any match has a warmup time, false otherwise
     */
    public function matchesHaveWarmups() : bool
    {
        return $this->matches_have_warmups;
    }

    /**
     * Get the ID of this IfUnknown. Since IfUnknown blocks don't have a unique id,
     * this is always the string "unknown"
     *
     * @return string
     */
    public function getID() : string
    {
        return 'unknown';
    }
}
