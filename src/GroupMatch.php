<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;
use Throwable;

/**
 * A match between two teams
 */
final class GroupMatch implements JsonSerializable, MatchInterface
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

    /** Whether the match is complete. This must be set when matchType is "continuous" or when a match has a "duration".  This is the value in the loaded data */
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

    /** Whether the match is a friendly.  These matches do not contribute toward a league position.  If a team only participates in friendly matches then they are not included in the league table at all */
    private bool $friendly = false;

    /** Free form string to add notes about a match */
    private ?string $notes = null;

    /** Whether the match is complete as a calculated value.  When the data sets a value, this is the same, but when a match completion must be calculated, this is the calculated version */
    private bool $is_complete = false;

    private bool $is_draw = false;
    private string $winner_team_id;
    private string $loser_team_id;
    private int $home_team_sets = 0;
    private int $away_team_sets = 0;

    private ?array $home_team_scores;
    private ?array $away_team_scores;
    private Group $group;

    /**
     * Contains the match data, creating any metadata needed
     *
     * @param Group $group The Group this match is in
     * @param object $match_data The data defining this Match
     *
     * @throws Exception If the two teams have scores arrays of different lengths
     */
    function __construct($group, $id)
    {
        if ($group->hasMatchWithID($id)) {
            throw new Exception('Group {'.$group->getStage()->getID().':'.$group->getID().'}: matches with duplicate IDs {'.$id.'} not allowed');
        }
        $this->group = $group;
        $this->id = $id;
    }

    public function loadFromData(object $match_data) : GroupMatch
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
        } else {
            // There seems to be a bug in opis/json-schema such that the schema rule to require the "complete" field when the "matchType" is "continuous"
            // is thrown off when the array of matches also includes a "break", so manually check here
            if ($this->group->getMatchType() === MatchType::CONTINUOUS) {
                throw new Exception('Group {'.$this->group->getStage()->getID().':'.$this->group->getID().'}, match ID {'.$match_data->id.'}, missing field "complete"');
            }
        }

        $this->setHomeTeam(MatchTeam::loadFromData($this, $match_data->homeTeam));
        $this->setAwayTeam(MatchTeam::loadFromData($this, $match_data->awayTeam));

        // $this->home_team = new MatchTeam($this, $match_data->homeTeam);
        $this->home_team_scores = $match_data->homeTeam->scores;
        // $this->away_team = new MatchTeam($this, $match_data->awayTeam);
        $this->away_team_scores = $match_data->awayTeam->scores;

        if (property_exists($match_data, 'officials')) {
            $officials = MatchOfficials::loadFromData($this, $match_data->officials);
            if ($officials->isTeam() &&
               ($officials->getTeamID() === $this->getHomeTeam()->getID() || $officials->getTeamID() === $this->getAwayTeam()->getID())) {
                throw new Exception('Refereeing team (in match {'.$this->group->getStage()->getID().':'.$this->group->getID().':'.$this->getID().'}) cannot be the same as one of the playing teams');
            }
            $this->setOfficials($officials);
        }
        if (property_exists($match_data, 'mvp')) {
            $this->setMVP($match_data->mvp);
        }
        if (property_exists($match_data, 'manager')) {
            $this->setManager(MatchManager::loadFromData($this, $match_data->manager));
        }
        if (property_exists($match_data, 'notes')) {
            $this->setNotes($match_data->notes);
        }
        if (property_exists($match_data, 'friendly')) {
            $this->setFriendly($match_data->friendly);
        }

        $this->calculateResult();

        return $this;
    }

    /**
     * Return the match data suitable for saving into a competition file
     *
     * @return object
     */
    public function jsonSerialize() : mixed
    {
        $match = new stdClass();
        $match->id = $this->id;

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

    /**
     * Get the Group this match is in
     *
     * @return Group|IfUnknown the Group this match is in
     */
    public function getGroup() : Group|IfUnknown
    {
        return $this->group;
    }

    /**
     * Get the ID for this match
     *
     * @return string the ID for this match
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Set the court for this match
     *
     * @param ?string $court the court for this match
     */
    public function setCourt(?string $court) : GroupMatch
    {
        $this->court = $court;
        return $this;
    }

    /**
     * Get the court for this match
     *
     * @return ?string the court for this match
     */
    public function getCourt() : ?string
    {
        return $this->court;
    }

    public function hasCourt() : bool
    {
        return $this->court !== null;
    }

    /**
     * Set the venue for this match
     *
     * @param ?string $venue the venue for this match
     */
    public function setVenue(?string $venue) : GroupMatch
    {
        $this->venue = $venue;
        return $this;
    }

    /**
     * Get the venue for this match
     *
     * @return ?string the venue for this match
     */
    public function getVenue() : ?string
    {
        return $this->venue;
    }

    public function hasVenue() : bool
    {
        return $this->venue !== null;
    }

    /**
     * Set the date for this match
     *
     * @param ?string $date the date for this match
     */
    public function setDate(?string $date) : GroupMatch
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get the date for this match
     *
     * @return ?string the date for this match
     */
    public function getDate() : ?string
    {
        return $this->date;
    }

    public function hasDate() : bool
    {
        return $this->date !== null;
    }

    /**
     * Set the warmup time for this match
     *
     * @param ?string $warmup the warmup time for this match
     */
    public function setWarmup(?string $warmup) : GroupMatch
    {
        $this->warmup = $warmup;
        return $this;
    }

    /**
     * Get the warmup time for this match
     *
     * @return ?string the warmup time for this match
     */
    public function getWarmup() : ?string
    {
        return $this->warmup;
    }

    public function hasWarmup() : bool
    {
        return $this->warmup !== null;
    }

    /**
     * Set the start time for this match
     *
     * @param ?string $start the start time for this match
     */
    public function setStart(?string $start) : GroupMatch
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Get the start time for this match
     *
     * @return ?string the start time for this match
     */
    public function getStart() : ?string
    {
        return $this->start;
    }

    public function hasStart() : bool
    {
        return $this->start !== null;
    }
    /**
     * Set the duration for this match
     *
     * @param ?string $duration the duration for this match
     */
    public function setDuration(?string $duration) : GroupMatch
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Get the duration for this match
     *
     * @return ?string the duration for this match
     */
    public function getDuration() : ?string
    {
        return $this->duration;
    }

    public function hasDuration() : bool
    {
        return $this->duration !== null;
    }

    /**
     * Set the completeness for this match
     *
     * @param bool $complete the completeness for this match
     *
     * @return void
     */
    public function setComplete(bool $complete) : void
    {
        $this->complete = $complete;
        $this->is_complete = $complete;
    }

    /**
     * Get the completeness for this match.  This is the explicit "complete" value from the original data
     *
     * @return ?bool the completeness for this match
     */
    public function getComplete() : ?bool
    {
        return $this->complete;
    }

    /**
     * Get the <i>calculated</i> completeness for this match.  This is for when the data does not explicitly state
     * the completeness, but the match configuration allows us to calculate whether the match is complete (e.g. set-based
     * matches without a duration limit)
     *
     * @return bool the completeness for this match
     */
    public function isComplete() : bool
    {
        return $this->is_complete;
    }

    /**
     * Get whether the match is a draw
     *
     * @return bool whether the match is a draw
     */
    public function isDraw() : bool
    {
        return $this->is_draw;
    }

    /**
     * Set the home team for this match
     *
     * @param MatchTeam $home_team the home team for this match
     */
    public function setHomeTeam(MatchTeam $home_team) : GroupMatch
    {
        $this->home_team = $home_team;
        return $this;
    }

    /**
     * Get the home team for this match
     *
     * @return MatchTeam the home team for this match
     */
    public function getHomeTeam() : MatchTeam
    {
        return $this->home_team;
    }

    public function getHomeTeamScores() : array
    {
        return $this->home_team_scores;
    }

    /**
     * Get the number of sets won by the home team
     *
     * @return int the number of sets won by the home team
     */
    public function getHomeTeamSets() : int
    {
        if ($this->group->getMatchType() === MatchType::CONTINUOUS) {
            throw new Exception('Match has no sets because the match type is continuous');
        }
        return $this->home_team_sets;
    }

    /**
     * Set the away team for this match
     *
     * @param MatchTeam $away_team the away team for this match
     */
    public function setAwayTeam(MatchTeam $away_team) : GroupMatch
    {
        $this->away_team = $away_team;
        return $this;
    }

    /**
     * Get the away team for this match
     *
     * @return MatchTeam the away team for this match
     */
    public function getAwayTeam() : MatchTeam
    {
        return $this->away_team;
    }

    public function getAwayTeamScores() : array
    {
        return $this->away_team_scores;
    }

    /**
     * Get the number of sets won by the away team
     *
     * @return int the number of sets won by the away team
     */
    public function getAwayTeamSets() : int
    {
        if ($this->group->getMatchType() === MatchType::CONTINUOUS) {
            throw new Exception('Match has no sets because the match type is continuous');
        }
        return $this->away_team_sets;
    }

    /**
     * Set the officials for this match
     *
     * @param ?MatchOfficials $officials the officials for this match
     */
    public function setOfficials(?MatchOfficials $officials) : GroupMatch
    {
        $this->officials = $officials;
        return $this;
    }

    /**
     * Get the officials for this match
     *
     * @return ?MatchOfficials the officials for this match
     */
    public function getOfficials() : ?MatchOfficials
    {
        return $this->officials;
    }

    public function hasOfficials() : bool
    {
        return $this->officials !== null;
    }

    /**
     * Set the MVP for this match
     *
     * @param ?string $mvp the MVP for this match
     */
    public function setMVP(?string $mvp) : GroupMatch
    {
        $this->mvp = $mvp;
        return $this;
    }

    /**
     * Get the MVP for this match
     *
     * @return ?string the MVP for this match
     */
    public function getMVP() : ?string
    {
        return $this->mvp;
    }

    public function hasMVP() : bool
    {
        return $this->mvp !== null;
    }

    /**
     * Set the court manager for this match
     *
     * @param ?MatchManager $manager the court manager for this match
     */
    public function setManager(?MatchManager $manager) : GroupMatch
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Get the court manager for this match
     *
     * @return ?MatchManager the court manager for this match
     */
    public function getManager() : ?MatchManager
    {
        return $this->manager;
    }

    public function hasManager() : bool
    {
        return $this->manager !== null;
    }

    /**
     * Set the notes for this match
     *
     * @param ?string $notes the notes for this match
     */
    public function setNotes(?string $notes) : GroupMatch
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Get the notes for this match
     *
     * @return ?string the notes for this match
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    public function hasNotes() : bool
    {
        return $this->notes !== null;
    }

    public function setFriendly(bool $friendly) : GroupMatch
    {
        $this->friendly = $friendly;
        return $this;
    }

    public function isFriendly() : bool
    {
        return $this->friendly;
    }

    /**
     * Set the scores for this match
     *
     * @param array<int> $home_team_scores The score array for the home team
     * @param array<int> $away_team_scores The score array for the away team
     * @param bool $complete Whether the match is complete or not (required for continuous scoring matches)
     */
    public function setScores(array $home_team_scores,array $away_team_scores, ?bool $complete = null) : GroupMatch
    {
        if ($this->group->getMatchType() === MatchType::CONTINUOUS) {
            if ($complete === null) {
                throw new Exception('Invalid score: match type is continuous, but the match completeness is not set');
            }
            GroupMatch::assertContinuousScoresValid($home_team_scores, $away_team_scores, $this->group);
            $this->setComplete($complete);
        } else {
            GroupMatch::assertSetScoresValid($home_team_scores, $away_team_scores, $this->group->getSetConfig());
            if ($this->duration !== null && $complete === null) {
                throw new Exception('Invalid results: match type is sets and match has a duration, but the match completeness is not set');
            }
            if ($complete !== null) {
                $this->setComplete($complete);
            }
        }
        $this->home_team_scores = $home_team_scores;
        $this->away_team_scores = $away_team_scores;
        $this->calculateResult();
        return $this;
    }

    /**
     * Calculate the result information for this match.  For example, is the match complete, who won,
     * how many sets did each team score, are the results valid
     *
     * @throws Exception the scores are invalid
     */
    private function calculateResult() : void
    {
        if (count($this->home_team->getScores()) !== count($this->away_team->getScores())) {
            throw new Exception('Invalid match information for match '.$this->id.': team scores have different length');
        }

        if ($this->group->getMatchType() === MatchType::CONTINUOUS) {
            $this->calculateContinuousResult();
        } else {
            if (count($this->home_team->getScores()) > $this->group->getSetConfig()->getMaxSets()) {
                throw new Exception('Invalid match information (in match {'.$this->group->getStage()->getID().':'.$this->group->getID().':'.$this->id.'}): team scores have more sets than the maximum allowed length');
            }
            try {
                $this->calculateSetsResult();
            } catch (Throwable $th) {
                throw new Exception('Invalid match information (in match {'.$this->group->getStage()->getID().':'.$this->group->getID().':'.$this->id.'}): '.$th->getMessage());
            }
        }
    }

    /**
     * Calculate the result information for this match, when the scores are continuous
     *
     * @throws Exception the match shows a draw but draws are not allowed
     */
    private function calculateContinuousResult() : void
    {
        if (count($this->home_team->getScores()) === 0) {
            return;
        }
        if ($this->home_team->getScores()[0] + $this->away_team->getScores()[0] === 0) {
            return;
        }

        if ($this->is_complete) {
            if ($this->home_team->getScores()[0] > $this->away_team->getScores()[0]) {
                $this->winner_team_id = $this->home_team->getID();
                $this->loser_team_id = $this->away_team->getID();
            } elseif ($this->home_team->getScores()[0] < $this->away_team->getScores()[0]) {
                $this->winner_team_id = $this->away_team->getID();
                $this->loser_team_id = $this->home_team->getID();
            } elseif ($this->group->getDrawsAllowed()) {
                $this->is_draw = true;
            } else {
                throw new Exception('Invalid match information (in match {'.$this->group->getStage()->getID().':'.$this->group->getID().':'.$this->id.'}): scores show a draw but draws are not allowed');
            }
        }
    }

    /**
     * Check that the continuous scores are valid
     *
     * @param array<int> $home_team_scores the home team's scores
     * @param array<int> $away_team_scores the away team's scores
     * @param object $group_config the configuration for the group this match is in
     *
     * @throws Exception the scores are invalid
     */
    public static function assertContinuousScoresValid(array $home_team_scores, array $away_team_scores, object $group_config) : void
    {
        $score_length = count($home_team_scores);
        if ($score_length > 1) {
            throw new Exception('Invalid results: match type is continuous, but score length is greater than one');
        }
        if ($group_config instanceof Group) {
            if (!$group_config->getDrawsAllowed() && $home_team_scores[0] === $away_team_scores[0] && $home_team_scores[0] !== 0) {
                throw new Exception('Invalid score: draws not allowed in this group');
            }
        } else {
            if (property_exists($group_config, 'drawsAllowed') && !$group_config->drawsAllowed && $home_team_scores[0] === $away_team_scores[0] && $home_team_scores[0] !== 0) {
                throw new Exception('Invalid score: draws not allowed in this group');
            }
        }
    }

    /**
     * Calculate the result information for this match, when the match is played to multiple sets
     *
     * @throws Exception the scores are invalid
     */
    private function calculateSetsResult() : void
    {
        GroupMatch::assertSetScoresValid($this->home_team->getScores(), $this->away_team->getScores(), $this->group->getSetConfig());

        $max_sets = $this->group->getSetConfig()->getMaxSets();
        $sets_to_win = $this->group->getSetConfig()->getSetsToWin();
        $score_length = count($this->home_team->getScores());

        $this->home_team_sets = 0;
        $this->away_team_sets = 0;
        for ($set_number = 0; $set_number < $score_length; $set_number++) {
            if ($this->home_team->getScores()[$set_number] < $this->group->getSetConfig()->getMinPoints() && $this->away_team->getScores()[$set_number] < $this->group->getSetConfig()->getMinPoints()) {
                continue;
            }
            if ($this->is_complete || GroupMatch::isSetComplete($set_number, $this->home_team->getScores()[$set_number], $this->away_team->getScores()[$set_number], $this->group->getSetConfig())) {
                if ($this->home_team->getScores()[$set_number] > $this->away_team->getScores()[$set_number]) {
                    $this->home_team_sets++;
                } else if ($this->home_team->getScores()[$set_number] < $this->away_team->getScores()[$set_number]) {
                    $this->away_team_sets++;
                }
            }
        }

        if ($this->duration === null &&
            ($this->home_team_sets + $this->away_team_sets === $max_sets || $this->home_team_sets >= $sets_to_win || $this->away_team_sets >= $sets_to_win)) {
            $this->is_complete = true;
        }

        if ($this->is_complete) {
            if ($this->home_team_sets > $this->away_team_sets) {
                $this->winner_team_id = $this->home_team->getID();
                $this->loser_team_id = $this->away_team->getID();
            } elseif ($this->home_team_sets < $this->away_team_sets) {
                $this->winner_team_id = $this->away_team->getID();
                $this->loser_team_id = $this->home_team->getID();
            } elseif ($this->group->getDrawsAllowed()) {
                $this->is_draw = true;
            } else {
                throw new Exception('Invalid match information (in match {'.$this->group->getStage()->getID().':'.$this->group->getID().':'.$this->id.'}): scores show a draw but draws are not allowed');
            }
        }
    }

    /**
     * Check that the set scores are valid
     *
     * @param array<int> $home_team_scores the home team's scores
     * @param array<int> $away_team_scores the away team's scores
     * @param SetConfig $set_config the set configuration for this match
     *
     * @throws Exception the scores are invalid
     */
    public static function assertSetScoresValid(array $home_team_scores, array $away_team_scores, SetConfig $set_config) : void
    {
        $home_score_length = count($home_team_scores);
        $away_score_length = count($away_team_scores);

        if ($home_score_length !== $away_score_length) {
            throw new Exception('Invalid set scores: score arrays are different lengths');
        }

        if ($home_score_length > $set_config->getMaxSets()) {
            throw new Exception('Invalid set scores: score arrays are longer than the maximum number of sets allowed');
        }

        $seen_incomplete_set = false;
        for ($set_number = 0; $set_number < $home_score_length; $set_number++) {
            if ($seen_incomplete_set && ($home_team_scores[$set_number] !== 0 || $away_team_scores[$set_number] !== 0)) {
                throw new Exception('Invalid set scores: data contains non-zero scores for a set after an incomplete set');
            }

            $decider_set = ($set_number === $set_config->getMaxSets() - 1);
            $clear_by_points = abs($home_team_scores[$set_number] - $away_team_scores[$set_number]);
            if ($decider_set) {
                // e.g. 28, 25
                if ($clear_by_points > $set_config->getClearPoints() && min($home_team_scores[$set_number], $away_team_scores[$set_number]) > $set_config->getLastSetPointsToWin()) {
                    if ($home_team_scores[$set_number] > $away_team_scores[$set_number]) {
                        throw new Exception('Invalid set scores: value for set score at index '.$set_number.' shows home team scoring more points than necessary to win the set');
                    } else {
                        throw new Exception('Invalid set scores: value for set score at index '.$set_number.' shows away team scoring more points than necessary to win the set');
                    }
                }
            } else {
                if (!GroupMatch::isMidSetComplete($home_team_scores[$set_number], $away_team_scores[$set_number], $clear_by_points, $set_config)) {
                    $seen_incomplete_set = true;
                }
            }
        }
    }

    /**
     * Work out whether the set is complete or not, first establishing whether this is the deciding set or not
     *
     * @param int $set_number the set number being checked
     * @param int $home_score the home team's score in this set
     * @param int $away_score the away team's score in this set
     * @param SetConfig $set_config the set configuration for this match
     */
    private static function isSetComplete(int $set_number, int $home_score, int $away_score, SetConfig $set_config) : bool
    {
        $decider_set = ($set_number === $set_config->getMaxSets() - 1);
        if ($decider_set) {
            return GroupMatch::isDeciderSetComplete($home_score, $away_score, abs($home_score - $away_score), $set_config);
        } else {
            return GroupMatch::isMidSetComplete($home_score, $away_score, abs($home_score - $away_score), $set_config);
        }
    }

    /**
     * Work out whether the set is complete or not, when this is not the deciding set
     *
     * @param int $home_score the home team's score in this set
     * @param int $away_score the away team's score in this set
     * @param int $clear_by_points the number of points clear a team must be
     * @param SetConfig $set_config the set configuration for this match
     *
     * @return bool whether the set is complete or not
     */
    private static function isMidSetComplete(int $home_score, int $away_score, int $clear_by_points, SetConfig $set_config) : bool
    {
        $has_enough_points = $home_score >= $set_config->getPointsToWin() || $away_score >= $set_config->getPointsToWin();
        $is_clear_by_enough_points = $clear_by_points >= $set_config->getClearPoints();
        $has_scored_maximum_points = ($home_score === $set_config->getMaxPoints() || $away_score === $set_config->getMaxPoints());
        return ($has_enough_points && $is_clear_by_enough_points) || $has_scored_maximum_points;
    }

    /**
     * Work out whether the set is complete or not, when this is the deciding set
     *
     * @param int $home_score the home team's score in this set
     * @param int $away_score the away team's score in this set
     * @param int $clear_by_points the number of points clear a team must be
     * @param SetConfig $set_config the set configuration for this match
     *
     * @return bool whether the set is complete or not
     */
    private static function isDeciderSetComplete(int $home_score, int $away_score, int $clear_by_points, SetConfig $set_config) : bool
    {
        $has_enough_points = $home_score >= $set_config->getLastSetPointsToWin() || $away_score >= $set_config->getLastSetPointsToWin();
        $is_clear_by_enough_points = $clear_by_points >= $set_config->getClearPoints();
        $has_scored_maximum_points = ($home_score === $set_config->getLastSetMaxPoints() || $away_score === $set_config->getLastSetMaxPoints());
        return ($has_enough_points && $is_clear_by_enough_points) || $has_scored_maximum_points;
    }

    /**
     * Get the ID of the winning team
     *
     * @throws Exception the match does not have a winner
     */
    public function getWinnerTeamID() : string
    {
        if (!$this->is_complete) {
            throw new Exception('Match incomplete, there is no winner');
        }

        if ($this->is_draw) {
            throw new Exception('Match drawn, there is no winner');
        }

        return $this->winner_team_id;
    }

    /**
     * Get the ID of the losing team
     *
     * @throws Exception the match does not have a loser
     */
    public function getLoserTeamID() : string
    {
        if (!$this->is_complete) {
            throw new Exception('Match incomplete, there is no loser');
        }

        if ($this->is_draw) {
            throw new Exception('Match drawn, there is no loser');
        }

        return $this->loser_team_id;
    }
}
