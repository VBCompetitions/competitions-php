<?php

namespace VBCompetitions\Competitions;

use DateTime;
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
    private string $id;

    /** The court that a match takes place on */
    private ?string $court = null;

    /** The venue that a match takes place at */
    private ?string $venue = null;

    /** The type of match, i.e. 'match' */
    // public string $type;

    /** The date of the match */
    private ?string $date = null;

    /** The start time for the warmup */
    private ?string $warmup = null;

    /** The start time for the match */
    private ?string $start = null;

    /** The maximum duration of the match */
    private ?string $duration = null;

    /** Whether the match is complete. This is kinda meaningless for an "IfUnknownMatch" but it allows round-tripping the JSON */
    private ?bool $complete = null;

    /** The 'home' team for the match */
    private MatchTeam $home_team;

    /** The 'away' team for the match */
    private MatchTeam $away_team;

    /** The officials for this match */
    private ?MatchOfficials $officials = null;

    /** A most valuable player award for the match */
    private ?string $mvp = null;

    /** The court manager in charge of this match */
    private ?MatchManager $manager = null;

    /** Free form string to add notes about a match */
    private ?string $notes = null;

    private IfUnknown $if_unknown;

    /**
     * Contains the match data, creating any metadata needed
     *
     * @param IfUnknown $if_unknown The Group or "IfUnknown" this match is in
     * @param object $match_data The data defining this Match
     *
     * @throws Exception If the two teams have scores arrays of different lengths
     */
    function __construct($if_unknown, string $id)
    {
        if ($if_unknown->hasMatchWithID($id)) {
            throw new Exception('stage ID {'.$this->if_unknown->getID().'}, ifUnknown: matches with duplicate IDs {'.$id.'} not allowed');
        }

        $this->if_unknown = $if_unknown;
        $this->id = $id;
    }

    public static function loadFromData(IfUnknown $if_unknown, object $match_data) : IfUnknownMatch
    {
        $match = new IfUnknownMatch($if_unknown, $match_data->id);

        if (property_exists($match_data, 'court')) {
            $match->setCourt($match_data->court);
        }
        if (property_exists($match_data, 'venue')) {
            $match->setVenue($match_data->venue);
        }
        if (property_exists($match_data, 'date')) {
            $match->setDate($match_data->date);
        }
        if (property_exists($match_data, 'warmup')) {
            $match->setWarmup($match_data->warmup);
        }
        if (property_exists($match_data, 'start')) {
            $match->setStart($match_data->start);
        }
        if (property_exists($match_data, 'duration')) {
            $match->setDuration($match_data->duration);
        }
        if (property_exists($match_data, 'complete')) {
            $match->setComplete($match_data->complete);
        }

        $match->setHomeTeam(MatchTeam::loadFromData($match, $match_data->homeTeam));
        $match->setAwayTeam(MatchTeam::loadFromData($match, $match_data->awayTeam));

        if (property_exists($match_data, 'officials')) {
            $match->setOfficials(MatchOfficials::loadFromData($match, $match_data->officials));
        }
        if (property_exists($match_data, 'mvp')) {
            $match->setMVP($match_data->mvp);
        }
        if (property_exists($match_data, 'manager')) {
            $match->setManager(MatchManager::loadFromData($match, $match_data->manager));
        }
        if (property_exists($match_data, 'notes')) {
            $match->setNotes($match_data->notes);
        }

        return $match;
    }

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
        $match->complete = false;

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

    /**
     * Get the IfUnknown this match is in
     *
     * @return IfUnknown the IfUnknown this match is in
     */
    public function getIfUnknown() : IfUnknown
    {
        return $this->if_unknown;
    }

    public function getGroup() : Group|IfUnknown
    {
        return $this->if_unknown;
    }

    public function setComplete(bool $complete) : IfUnknownMatch
    {
        // "unknown" matches can't be complete, so ignore
        return $this;
    }

    public function isComplete() : bool
    {
        // "unknown" matches can't be complete
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

    public function setCourt(string $court) : IfUnknownMatch
    {
        if (strlen($court) > 1000 || strlen($court) < 1) {
            throw new Exception('Invalid court: must be between 1 and 1000 characters long');
        }
        $this->court = $court;
        return $this;
    }

    public function getCourt() : ?string
    {
        return $this->court;
    }

    public function setVenue(string $venue) : IfUnknownMatch
    {
        if (strlen($venue) > 10000 || strlen($venue) < 1) {
            throw new Exception('Invalid venue: must be between 1 and 10000 characters long');
        }
        $this->venue = $venue;
        return $this;
    }

    public function getVenue() : ?string
    {
        return $this->venue;
    }

    public function setDate(string $date) : IfUnknownMatch
    {
        if (!preg_match('/^[0-9]{4}-(0[0-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $date)) {
            throw new Exception('Invalid date "'.$date.'": must contain a value of the form "YYYY-MM-DD"');
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d === false || $d->format('Y-m-d') !== $date) {
            throw new Exception('Invalid date "'.$date.'": date does not exist');
        }

        $this->date = $date;
        return $this;
    }

    public function getDate() : ?string
    {
        return $this->date;
    }

    public function setWarmup(string $warmup) : IfUnknownMatch
    {
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $warmup)) {
            throw new Exception('Invalid warmup time "'.$warmup.'": must contain a value of the form "HH:mm" using a 24 hour clock');
        }
        $this->warmup = $warmup;
        return $this;
    }

    public function getWarmup() : ?string
    {
        return $this->warmup;
    }

    public function setDuration(string $duration) : IfUnknownMatch
    {
        if (!preg_match('/^[0-9]+:[0-5][0-9]$/', $duration)) {
            throw new Exception('Invalid duration "'.$duration.'": must contain a value of the form "HH:mm"');
        }
        $this->duration = $duration;
        return $this;
    }

    public function getDuration() : ?string
    {
        return $this->duration;
    }

    public function setStart(string $start) : IfUnknownMatch
    {
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $start)) {
            throw new Exception('Invalid start time "'.$start.'": must contain a value of the form "HH:mm" using a 24 hour clock');
        }
        $this->start = $start;
        return $this;
    }

    public function getStart() : ?string
    {
        return $this->start;
    }

    public function setManager(MatchManager $manager) : IfUnknownMatch
    {
        $this->manager = $manager;
        return $this;
    }

    public function getManager() : MatchManager
    {
        return $this->manager;
    }

    public function setMVP(string $mvp) : IfUnknownMatch
    {
        if (strlen($mvp) > 203 || strlen($mvp) < 1) {
            throw new Exception('Invalid manager: must be between 1 and 203 characters long');
        }
        $this->mvp = $mvp;
        return $this;
    }

    public function getMVP() : ?string
    {
        return $this->mvp;
    }

    public function setNotes(string $notes) : IfUnknownMatch
    {
        $this->notes = $notes;
        return $this;
    }

    public function getNotes() : ?string
    {
        return $this->notes;
    }

    public function setOfficials(MatchOfficials $officials) : IfUnknownMatch
    {
        $this->officials = $officials;
        return $this;
    }

    public function getOfficials() : ?MatchOfficials
    {
        return $this->officials;
    }

    public function hasOfficials() : bool
    {
        return $this->officials !== null;
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

    public function setAwayTeam(MatchTeam $team) : IfUnknownMatch
    {
        $this->away_team = $team;
        return $this;
    }

    public function getAwayTeam() : MatchTeam
    {
        return $this->away_team;
    }

    public function setHomeTeam(MatchTeam $team) : IfUnknownMatch
    {
        $this->home_team = $team;
        return $this;
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
}
