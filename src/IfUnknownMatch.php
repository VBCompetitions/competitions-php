<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * A match between two teams
 */
final class IfUnknownMatch implements JsonSerializable, MatchInterface
{
    /**
     * An identifier for this match, i.e. a match number.  If the document uses any team references then all match
     * identifiers in the document must be unique or a document reader's behaviour is undefined
     */
    public string $id;

    /** The court that a match takes place on */
    public ?string $court = null;

    /** The venue that a match takes place at */
    public ?string $venue = null;

    /** The type of match, i.e. 'match' */
    // public string $type;

    /** The date of the match */
    public ?string $date = null;

    /** The start time for the warmup */
    public ?string $warmup = null;

    /** The start time for the match */
    public ?string $start = null;

    /** The maximum duration of the match */
    public ?string $duration = null;

    /** Whether the match is complete. This is kinda meaningless for an "IfUnknownMatch" but it allows round-tripping the JSON */
    public ?bool $complete = null;

    /** The 'home' team for the match */
    public MatchTeam $home_team;

    /** The 'away' team for the match */
    public MatchTeam $away_team;

    /** The officials for this match */
    public ?object $officials = null;

    /** A most valuable player award for the match */
    public ?string $mvp = null;

    /** The court manager in charge of this match */
    public ?string $manager = null;

    /** Free form string to add notes about a match */
    public ?string $notes = null;

    public IfUnknown $if_unknown;

    /**
     * Contains the match data, creating any metadata needed
     *
     * @param IfUnknown $if_unknown The Group or "IfUnknown" this match is in
     * @param object $match_data The data defining this Match
     *
     * @throws Exception If the two teams have scores arrays of different lengths
     */
    function __construct($if_unknown, $match_data)
    {
        $this->if_unknown = $if_unknown;
        $this->id = $match_data->id;
        if (property_exists($match_data, 'court')) {
            $this->court = $match_data->court;
        }
        if (property_exists($match_data, 'venue')) {
            $this->venue = $match_data->venue;
        }
        if (property_exists($match_data, 'date')) {
            $this->date = $match_data->date;
        }
        if (property_exists($match_data, 'warmup')) {
            $this->warmup = $match_data->warmup;
        }
        if (property_exists($match_data, 'start')) {
            $this->start = $match_data->start;
        }
        if (property_exists($match_data, 'duration')) {
            $this->duration = $match_data->duration;
        }
        if (property_exists($match_data, 'complete')) {
            $this->complete = $match_data->complete;
        }
        $this->home_team = new MatchTeam($match_data->homeTeam, $this);
        $this->away_team = new MatchTeam($match_data->awayTeam, $this);
        if (property_exists($match_data, 'officials')) {
            $this->officials = $match_data->officials;
        }
        if (property_exists($match_data, 'mvp')) {
            $this->mvp = $match_data->mvp;
        }
        if (property_exists($match_data, 'manager')) {
            $this->manager = $match_data->manager;
        }
        if (property_exists($match_data, 'notes')) {
            $this->notes = $match_data->notes;
        }
    }

    public function getGroup() : Group|IfUnknown
    {
        return $this->if_unknown;
    }

    public function isComplete() : bool
    {
        return false;
    }

    public function isDraw() : bool
    {
        return false;
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getCourt() : ?string
    {
        return $this->court;
    }

    public function getVenue() : ?string
    {
        return $this->venue;
    }

    public function getDate() : ?string
    {
        return $this->date;
    }

    public function getWarmup() : ?string
    {
        return $this->warmup;
    }

    public function getDuration() : ?string
    {
        return $this->duration;
    }

    public function getStart() : ?string
    {
        return $this->start;
    }

    public function getManager() : mixed
    {
        return $this->manager;
    }

    public function getMVP() : ?string
    {
        return $this->mvp;
    }

    public function getNotes() : ?string
    {
        return $this->notes;
    }

    public function getOfficials() : ?object
    {
        return $this->officials;
    }


    public function getWinnerTeamId() : string
    {
        return CompetitionTeam::UNKNOWN_TEAM_ID;
    }

    public function getLoserTeamId() : string
    {
        return CompetitionTeam::UNKNOWN_TEAM_ID;
    }

    public function getHomeTeamScores() : array
    {
        return [];
    }

    public function getAwayTeamScores() : array
    {
        return [];
    }

    public function getHomeTeamSets() : int
    {
        return 0;
    }

    public function getAwayTeamSets() : int
    {
        return 0;
    }

    public function getAwayTeam() : MatchTeam
    {
        return $this->away_team;
    }

    public function getHomeTeam() : MatchTeam
    {
        return $this->home_team;
    }

    /**
     * Set the scores for this match
     *
     * @param array<int> $home_team_scores The score array for the home team
     * @param array<int> $away_team_scores The score array for the away team
     * @param bool $complete Whether the match is complete or not (required for continuous scoring matches)
     */
    public function setScores(array $home_team_scores, array $away_team_scores, ?bool $complete = null) : void {}

    /**
     * Return the match data suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $match = new stdClass();

        $match->id = $this->id;
        $match->type = 'match';

        if ($this->court !== null) {
            $match->court = $this->court;
        }
        if ($this->venue !== null) {
            $match->venue = $this->venue;
        }

        $match->type = 'match';

        if ($this->date !== null) {
            $match->date = $this->date;
        }
        if ($this->warmup !== null) {
            $match->warmup = $this->warmup;
        }
        if ($this->start !== null) {
            $match->start = $this->start;
        }
        if ($this->duration !== null) {
            $match->duration = $this->duration;
        }
        if ($this->complete !== null) {
            $match->complete = $this->complete;
        }

        $match->homeTeam = $this->home_team;
        $match->awayTeam = $this->away_team;

        if ($this->officials !== null) {
            $match->officials = $this->officials;
        }
        if ($this->mvp !== null) {
            $match->mvp = $this->mvp;
        }
        if ($this->manager !== null) {
            $match->manager = $this->manager;
        }
        if ($this->notes !== null) {
            $match->notes = $this->notes;
        }
        return $match;
    }
}
