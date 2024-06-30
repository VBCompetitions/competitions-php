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

    /** @var ?string The court that a match takes place on */
    private ?string $court = null;

    /** @var ?string The venue that a match takes place at */
    private ?string $venue = null;

    /** @var ?string The date of the match */
    private ?string $date = null;

    /** @var ?string The start time for the warmup */
    private ?string $warmup = null;

    /** @var ?string The start time for the match */
    private ?string $start = null;

    /** @var ?string The maximum duration of the match */
    private ?string $duration = null;

    /** @var ?bool Whether the match is complete. This is kinda meaningless for an "IfUnknownMatch" but it allows round-tripping the JSON */
    private ?bool $complete = null;

    /** @var MatchTeam The 'home' team for the match */
    private MatchTeam $home_team;

    /** @var MatchTeam The 'away' team for the match */
    private MatchTeam $away_team;

    /** @var ?MatchOfficials The officials for this match */
    private ?MatchOfficials $officials = null;

    /** @var null|Player A most valuable player award for the match */
    private ?Player $mvp = null;

    /** @var ?MatchManager The court manager in charge of this match */
    private ?MatchManager $manager = null;

    /** @var bool Whether the match is a friendly.  These matches do not contribute toward a league position.  If a team only participates in friendly matches then they are not included in the league table at all */
    private bool $friendly;

    /** @var ?string Free form string to add notes about a match */
    private ?string $notes = null;

    /** @var IfUnknown The Group or "IfUnknown" this match is in */
    private IfUnknown $if_unknown;

    /**
     * Contains the match data, creating any metadata needed
     *
     * @param IfUnknown $if_unknown The Group or "IfUnknown" this match is in
     * @param string $id An identifier for this match
     *
     * @throws Exception If the two teams have scores arrays of different lengths
     */
    function __construct(IfUnknown $if_unknown, string $id)
    {
        if ($if_unknown->hasMatch($id)) {
            throw new Exception('stage ID {'.$if_unknown->getStage()->getID().'}, ifUnknown: matches with duplicate IDs {'.$id.'} not allowed');
        }

        $this->if_unknown = $if_unknown;
        $this->id = $id;
    }

    /**
     * Loads data from an object into the IfUnknownMatch instance
     *
     * @param object $match_data The data defining this Match
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function loadFromData(object $match_data) : IfUnknownMatch
    {
        if (property_exists($match_data, 'court')) {
            $this->setCourt($match_data->court);
        }
        if (property_exists($match_data, 'venue')) {
            $this->setVenue($match_data->venue);
        }
        if (property_exists($match_data, 'date')) {
            $this->setDate($match_data->date);
        }
        if (property_exists($match_data, 'warmup')) {
            $this->setWarmup($match_data->warmup);
        }
        if (property_exists($match_data, 'start')) {
            $this->setStart($match_data->start);
        }
        if (property_exists($match_data, 'duration')) {
            $this->setDuration($match_data->duration);
        }
        if (property_exists($match_data, 'complete')) {
            $this->setComplete($match_data->complete);
        }

        $this->setHomeTeam(MatchTeam::loadFromData($this, $match_data->homeTeam));
        $this->setAwayTeam(MatchTeam::loadFromData($this, $match_data->awayTeam));

        if (property_exists($match_data, 'officials')) {
            $this->setOfficials(MatchOfficials::loadFromData($this, $match_data->officials));
        }
        if (property_exists($match_data, 'mvp')) {
            if (preg_match('/^{(.*)}$/', $match_data->mvp, $mvp_match)) {
                $this->setMVP($this->getGroup()->getStage()->getCompetition()->getPlayer($mvp_match[1]));
            } else {
                $this->setMVP(new Player($this->getGroup()->getStage()->getCompetition(), Player::UNREGISTERED_PLAYER_ID, $match_data->mvp));
            }
        }
        if (property_exists($match_data, 'manager')) {
            $this->setManager(MatchManager::loadFromData($this, $match_data->manager));
        }
        if (property_exists($match_data, 'notes')) {
            $this->setNotes($match_data->notes);
        }

        return $this;
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
            if ($this->mvp->getID() === Player::UNREGISTERED_PLAYER_ID) {
                $match->mvp = $this->mvp->getName();
            } else {
                $match->mvp = '{'.$this->mvp->getID().'}';
            }
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
     * @return IfUnknown The IfUnknown instance this match is in
     */
    public function getIfUnknown() : IfUnknown
    {
        return $this->if_unknown;
    }

    /**
     * Get the "IfUnknown" this match is in
     *
     * @return Group|IfUnknown The Group or IfUnknown instance this match is in
     */
    public function getGroup() : Group|IfUnknown
    {
        return $this->if_unknown;
    }

    /**
     * Set the completion status of the match
     *
     * An "unknown" match cannot be complete, so this method does nothing.
     *
     * @param bool $complete Whether the match is complete or not
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function setComplete(bool $complete) : IfUnknownMatch
    {
        // "unknown" matches can't be complete, so ignore
        return $this;
    }

    /**
     * Check if the match is complete
     *
     * An "unknown" match cannot be complete.
     *
     * @return bool Always returns false
     */
    public function isComplete() : bool
    {
        // "unknown" matches can't be complete
        return false;
    }

    /**
     * Check if the match is a draw
     *
     * Since an "unknown" match cannot be complete, it cannot be a draw either.
     *
     * @return bool Always returns false
     */
    public function isDraw() : bool
    {
        return false;
    }

    /**
     * Get the ID of the match
     *
     * @return string The ID of the match
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Set the court where the match takes place
     *
     * @param string $court The court where the match takes place
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     * @throws Exception If the court name is invalid
     */
    public function setCourt(string $court) : IfUnknownMatch
    {
        if (strlen($court) > 1000 || strlen($court) < 1) {
            throw new Exception('Invalid court: must be between 1 and 1000 characters long');
        }
        $this->court = $court;
        return $this;
    }

    /**
     * Get the court where the match takes place
     *
     * @return ?string The court where the match takes place
     */
    public function getCourt() : ?string
    {
        return $this->court;
    }

    /**
     * Set the venue where the match takes place
     *
     * @param string $venue The venue where the match takes place
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     * @throws Exception If the venue name is invalid
     */
    public function setVenue(string $venue) : IfUnknownMatch
    {
        if (strlen($venue) > 10000 || strlen($venue) < 1) {
            throw new Exception('Invalid venue: must be between 1 and 10000 characters long');
        }
        $this->venue = $venue;
        return $this;
    }

    /**
     * Get the venue where the match takes place
     *
     * @return ?string The venue where the match takes place
     */
    public function getVenue() : ?string
    {
        return $this->venue;
    }

    /**
     * Set the date of the match
     *
     * @param string $date The date of the match (format: YYYY-MM-DD)
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     * @throws Exception If the date is invalid
     */
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

    /**
     * Get the date of the match
     *
     * @return ?string The date of the match (format: YYYY-MM-DD)
     */
    public function getDate() : ?string
    {
        return $this->date;
    }

    /**
     * Set the warmup start time of the match
     *
     * @param string $warmup The warmup start time of the match (format: HH:mm)
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     * @throws Exception If the warmup time is invalid
     */
    public function setWarmup(string $warmup) : IfUnknownMatch
    {
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $warmup)) {
            throw new Exception('Invalid warmup time "'.$warmup.'": must contain a value of the form "HH:mm" using a 24 hour clock');
        }
        $this->warmup = $warmup;
        return $this;
    }

    /**
     * Get the warmup start time of the match
     *
     * @return ?string The warmup start time of the match (format: HH:mm)
     */
    public function getWarmup() : ?string
    {
        return $this->warmup;
    }

    /**
     * Set the duration for the match
     *
     * @param string $duration The duration for the match in the format "HH:mm"
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     * @throws Exception If the duration is invalid
     */
    public function setDuration(string $duration) : IfUnknownMatch
    {
        if (!preg_match('/^[0-9]+:[0-5][0-9]$/', $duration)) {
            throw new Exception('Invalid duration "'.$duration.'": must contain a value of the form "HH:mm"');
        }
        $this->duration = $duration;
        return $this;
    }

    /**
     * Get the duration for the match
     *
     * @return ?string The duration for the match in the format "HH:mm"
     */
    public function getDuration() : ?string
    {
        return $this->duration;
    }

    /**
     * Set the start time of the match
     *
     * @param string $start The start time of the match (format: HH:mm)
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     * @throws Exception If the start time is invalid
     */
    public function setStart(string $start) : IfUnknownMatch
    {
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $start)) {
            throw new Exception('Invalid start time "'.$start.'": must contain a value of the form "HH:mm" using a 24 hour clock');
        }
        $this->start = $start;
        return $this;
    }

    /**
     * Get the start time of the match
     *
     * @return ?string The start time of the match (format: HH:mm)
     */
    public function getStart() : ?string
    {
        return $this->start;
    }

    /**
     * Set the manager for the match
     *
     * @param MatchManager $manager The manager for the match
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function setManager(MatchManager $manager) : IfUnknownMatch
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Get the manager for the match
     *
     * @return ?MatchManager The manager for the match
     */
    public function getManager() : ?MatchManager
    {
        return $this->manager;
    }

    /**
     * Set the Most Valuable Player (MVP) for the match
     *
     * @param Player $mvp The Most Valuable Player for the match
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     * @throws Exception If the MVP string is invalid
     */
    public function setMVP(Player $mvp) : IfUnknownMatch
    {
        $this->mvp = $mvp;
        return $this;
    }

    /**
     * Get the Most Valuable Player (MVP) for the match
     *
     * @return ?Player The Most Valuable Player for the match
     */
    public function getMVP() : ?Player
    {
        return $this->mvp;
    }

    /**
     * Set notes for the match
     *
     * @param string $notes Notes about the match
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function setNotes(string $notes) : IfUnknownMatch
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Get notes for the match
     *
     * @return ?string Notes about the match
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Set whether the match is a friendly match
     *
     * @param bool $friendly Whether the match is a friendly match
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function setFriendly(bool $friendly) : IfUnknownMatch
    {
        $this->friendly = $friendly;
        return $this;
    }

    /**
     * Check if the match is a friendly match
     *
     * @return bool Whether the match is a friendly match
     */
    public function isFriendly() : bool
    {
        return $this->friendly;
    }

    /**
     * Set the officials for the match
     *
     * @param MatchOfficials $officials The officials for the match
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function setOfficials(MatchOfficials $officials) : IfUnknownMatch
    {
        $this->officials = $officials;
        return $this;
    }

    /**
     * Get the officials for the match
     *
     * @return ?MatchOfficials The officials for the match
     */
    public function getOfficials() : ?MatchOfficials
    {
        return $this->officials;
    }

    /**
     * Check if the match has officials assigned
     *
     * @return bool Whether the match has officials assigned
     */
    public function hasOfficials() : bool
    {
        return $this->officials !== null;
    }

    /**
     * Get the ID of the winning team
     *
     * @return string The ID of the winning team
     */
    public function getWinnerTeamID() : string
    {
        return CompetitionTeam::UNKNOWN_TEAM_ID;
    }

     /**
     * Get the ID of the losing team
     *
     * @return string The ID of the losing team
     */
    public function getLoserTeamID() : string
    {
        return CompetitionTeam::UNKNOWN_TEAM_ID;
    }

    /**
     * Get the scores of the home team
     *
     * @return array The scores of the home team
     */
    public function getHomeTeamScores() : array
    {
        return [];
    }

    /**
     * Get the scores of the away team
     *
     * @return array The scores of the away team
     */
    public function getAwayTeamScores() : array
    {
        return [];
    }

    /**
     * Get the number of sets won by the home team
     *
     * @return int The number of sets won by the home team
     */
    public function getHomeTeamSets() : int
    {
        return 0;
    }

    /**
     * Get the number of sets won by the away team
     *
     * @return int The number of sets won by the away team
     */
    public function getAwayTeamSets() : int
    {
        return 0;
    }

    /**
     * Set the away team for the match
     *
     * @param MatchTeam $team The away team for the match
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function setAwayTeam(MatchTeam $team) : IfUnknownMatch
    {
        $this->away_team = $team;
        return $this;
    }

    /**
     * Get the away team for the match
     *
     * @return MatchTeam The away team for the match
     */
    public function getAwayTeam() : MatchTeam
    {
        return $this->away_team;
    }

    /**
     * Set the home team for the match
     *
     * @param MatchTeam $team The home team for the match
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function setHomeTeam(MatchTeam $team) : IfUnknownMatch
    {
        $this->home_team = $team;
        return $this;
    }

    /**
     * Get the home team for the match
     *
     * @return MatchTeam The home team for the match
     */
    public function getHomeTeam() : MatchTeam
    {
        return $this->home_team;
    }

    /**
     * An IfUnknown match has no scores so this function has no effect
     *
     * @param array<int> $home_team_scores The score array for the home team
     * @param array<int> $away_team_scores The score array for the away team
     * @param ?bool $complete Whether the match is complete or not
     * @return IfUnknownMatch The updated IfUnknownMatch instance
     */
    public function setScores(array $home_team_scores, array $away_team_scores, ?bool $complete = null) : IfUnknownMatch
    {
        return $this;
    }
}
