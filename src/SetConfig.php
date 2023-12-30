<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use stdClass;

/**
 * Configuration defining the nature of a set
 */
final class SetConfig implements JsonSerializable
{

    /** The maximum number of sets that could be played, often known as 'best of', e.g. if this has the value '5' then the match is played as 'best of 5 sets' **/
    private int $max_sets = 5;

    /** The number of sets that must be won to win the match.  This is usually one more than half the 'maxSets', but may be needed if draws are allowed, e.g. if a competition dictates that exactly 2 sets must be played (by setting 'maxSets' to '2') and that draws are allowed, then 'setsToWin' should still be set to '2' to indicate that 2 sets are needed to win the match */
    private int $sets_to_win = 3;

    /** The number of points lead that the winning team must have, e.g. if this has the value '2' then teams must 'win by 2 clear points'.  Note that if 'maxPoints' has a value then that takes precedence, i.e. if 'maxPoints' is set to '35' then a team can win '35-34' irrespective of the value of 'clearPoints' */
    private int $clear_points = 2;

    /** The minimum number of points that either team must score for a set to count as valid.  Usually only used for time-limited matches */
    private int $min_points = 1;

    /** The minimum number of points required to win all but the last set */
    private int $points_to_win = 25;

    /** The minimum number of points required to win the last set */
    private int $last_set_points_to_win = 15;

    /** The upper limit of points that can be scored in a set */
    private int $max_points = 1000;

    /** The upper limit of points that can be scored in the last set */
    private int $last_set_max_points = 1000;

    /**
     * Contains the configuration data that define the sets in a match.  Uses the defaults if the config is null
     *
     * @param object|null $set_config The configuration defining the set
     */
    function __construct(object $set_config = null)
    {
        if (is_null($set_config)) {
            return;
        }

        if (property_exists($set_config, 'maxSets')) {
            $this->max_sets = $set_config->maxSets;
        }

        if (property_exists($set_config, 'setsToWin')) {
            $this->sets_to_win = $set_config->setsToWin;
        }

        if (property_exists($set_config, 'clearPoints')) {
            $this->clear_points = $set_config->clearPoints;
        }

        if (property_exists($set_config, 'minPoints')) {
            $this->min_points = $set_config->minPoints;
        }

        if (property_exists($set_config, 'pointsToWin')) {
            $this->points_to_win = $set_config->pointsToWin;
        }

        if (property_exists($set_config, 'lastSetPointsToWin')) {
            $this->last_set_points_to_win = $set_config->lastSetPointsToWin;
        }

        if (property_exists($set_config, 'maxPoints')) {
            $this->max_points = $set_config->maxPoints;
        }

        if (property_exists($set_config, 'lastSetMaxPoints')) {
            $this->last_set_max_points = $set_config->lastSetMaxPoints;
        }
    }

    public function getMaxSets() : int
    {
        return $this->max_sets;
    }

    public function getSetsToWin() : int
    {
        return $this->sets_to_win;
    }

    public function getClearPoints() : int
    {
        return $this->clear_points;
    }

    public function getMinPoints() : int
    {
        return $this->min_points;
    }

    public function getPointsToWin() : int
    {
        return $this->points_to_win;
    }

    public function getLastSetPointsToWin() : int
    {
        return $this->last_set_points_to_win;
    }

    public function getMaxPoints() : int
    {
        return $this->max_points;
    }

    public function getLastSetMaxPoints() : int
    {
        return $this->last_set_max_points;
    }


    /**
     * Return the list of team definition suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $set_config = new stdClass();

        $set_config->maxSets = $this->max_sets;
        $set_config->setsToWin = $this->sets_to_win;
        $set_config->clearPoints = $this->clear_points;
        $set_config->minPoints = $this->min_points;
        $set_config->pointsToWin = $this->points_to_win;
        $set_config->lastSetPointsToWin = $this->last_set_points_to_win;
        $set_config->maxPoints = $this->max_points;
        $set_config->lastSetMaxPoints = $this->last_set_max_points;

        return $set_config;
    }
}
