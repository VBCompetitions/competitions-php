<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;

/**
 * It can be useful to still present something to the user about the later stages of a competition, even if the teams playing in that stage is not yet known. This defines what should be presented in any application handling this competition's data in such cases
 */
final class IfUnknown implements JsonSerializable, MatchContainerInterface
{
    /** An array of string values to be presented in the case that the teams in this stage are not yet known, typically as an explanation of what this stage will contain (e.g. 'The crossover games will be between the top two teams in each pool') */
    private array $description = [];

    /** An array of matches in this group (or breaks in play) */
    private array $matches = [];

    /** The Stage this IfUnknown is in */
    private Stage $stage;

    private bool $matches_have_courts = false;
    private bool $matches_have_dates = false;
    private bool $matches_have_durations = false;
    private bool $matches_have_mvps = false;
    private bool $matches_have_managers = false;
    private bool $matches_have_notes = false;
    private bool $matches_have_officials = false;
    private bool $matches_have_starts = false;
    private bool $matches_have_venues = false;
    private bool $matches_have_warmups = false;

    /** A Lookup table from match IDs to that match */
    private object $match_lookup;

    /**
     * Contains the ifUnknown data of a competition, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this IfUnknown is in
     * @param object $if_unknown_data The data defining this IfUnknown
     */
    function __construct(Stage $stage, array $description)
    {
        $this->stage = $stage;
        $this->description = $description;
        $this->match_lookup = new stdClass();
    }

    public function loadFromData(object $if_unknown_data) : IfUnknown
    {
        foreach ($if_unknown_data->matches as $match) {
            if ($match->type === 'match') {
                $this->addMatch((new IfUnknownMatch($this, $match->id))->loadFromData($match));
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

    public function getCompetition() : Competition
    {
        return $this->stage->getCompetition();
    }

    public function getDescription() : array
    {
        return $this->description;
    }

    public function addMatch(IfUnknownMatch $match) : IfUnknown
    {
        array_push($this->matches, $match);
        $this->match_lookup->{$match->getID()} = $match;
        if (property_exists($match, 'court')) {
            $this->matches_have_courts = true;
        }
        if (property_exists($match, 'date')) {
            $this->matches_have_dates = true;
        }
        if (property_exists($match, 'duration')) {
            $this->matches_have_durations = true;
        }
        if (property_exists($match, 'mvp')) {
            $this->matches_have_mvps = true;
        }
        if (property_exists($match, 'manager')) {
            $this->matches_have_managers = true;
        }
        if (property_exists($match, 'notes')) {
            $this->matches_have_notes = true;
        }
        if (property_exists($match, 'officials')) {
            $this->matches_have_officials = true;
        }
        if (property_exists($match, 'start')) {
            $this->matches_have_starts = true;
        }
        if (property_exists($match, 'venue')) {
            $this->matches_have_venues = true;
        }
        if (property_exists($match, 'warmup')) {
            $this->matches_have_warmups = true;
        }
        return $this;
    }

    public function addBreak(IfUnknownBreak $break) : IfUnknown
    {
        array_push($this->matches, $break);
        return $this;
    }

    public function getMatches(string $team_id = null, int $flags = 0) : array
    {
        return $this->matches;
    }

    public function hasMatchWithID($id) : bool
    {
        return property_exists($this->match_lookup, $id);
    }

    public function getTeamIDs(int $flags = 0) : array
    {
        return [];
    }

    public function getMatchType() : MatchType
    {
        return MatchType::CONTINUOUS;
    }

    /**
     * Returns the match with the specified ID
     *
     * @param string $match_id The ID of the match
     *
     * @return IfUnknownMatch The requested match
     */
    public function getMatchById(string $match_id) : IfUnknownMatch
    {
        if (property_exists($this->match_lookup, $match_id)) {
            return $this->match_lookup->{$match_id};
        }
        throw new OutOfBoundsException('Match with ID '.$match_id.' not found', 1);
    }

    public function matchesHaveCourts() : bool
    {
        return $this->matches_have_courts;
    }

    public function matchesHaveDates() : bool
    {
        return $this->matches_have_dates;
    }

    public function matchesHaveDurations() : bool
    {
        return $this->matches_have_durations;
    }

    public function matchesHaveManagers() : bool
    {
        return $this->matches_have_managers;
    }

    public function matchesHaveMVPs() : bool
    {
        return $this->matches_have_mvps;
    }

    public function matchesHaveNotes() : bool
    {
        return $this->matches_have_notes;
    }

    public function matchesHaveOfficials() : bool
    {
        return $this->matches_have_officials;
    }

    public function matchesHaveStarts() : bool
    {
        return $this->matches_have_starts;
    }

    public function matchesHaveVenues() : bool
    {
        return $this->matches_have_venues;
    }

    public function matchesHaveWarmups() : bool
    {
        return $this->matches_have_warmups;
    }

    public function getID() : string{
        return 'unknown';
    }
}
