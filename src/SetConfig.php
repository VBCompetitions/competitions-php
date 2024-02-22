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

    /** The group that this SetConfig belongs to */
    private Group $group;

    /**
     * Contains the configuration data that define the sets in a match.  Uses the defaults if the config is null
     *
     * @param object|null $set_config The configuration defining the set
     */
    function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function loadFromData(object $set_data) : SetConfig
    {
        if (property_exists($set_data, 'maxSets')) {
            $this->setMaxSets($set_data->maxSets);
        }

        if (property_exists($set_data, 'setsToWin')) {
            $this->setSetsToWin($set_data->setsToWin);
        }

        if (property_exists($set_data, 'clearPoints')) {
            $this->setClearPoints($set_data->clearPoints);
        }

        if (property_exists($set_data, 'minPoints')) {
            $this->setMinPoints($set_data->minPoints);
        }

        if (property_exists($set_data, 'pointsToWin')) {
            $this->setPointsToWin($set_data->pointsToWin);
        }

        if (property_exists($set_data, 'lastSetPointsToWin')) {
            $this->setLastSetPointsToWin($set_data->lastSetPointsToWin);
        }

        if (property_exists($set_data, 'maxPoints')) {
            $this->setMaxPoints($set_data->maxPoints);
        }

        if (property_exists($set_data, 'lastSetMaxPoints')) {
            $this->setLastSetMaxPoints($set_data->lastSetMaxPoints);
        }

        return $this;
    }

    public function getGroup() : Group
    {
        return $this->group;
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

    public function setMaxSets(int $max_sets) : void
    {
        $this->max_sets = $max_sets;
    }

    public function getMaxSets() : int
    {
        return $this->max_sets;
    }

    public function setSetsToWin(int $sets_to_win) : void
    {
        $this->sets_to_win = $sets_to_win;
    }

    public function getSetsToWin() : int
    {
        return $this->sets_to_win;
    }

    public function setClearPoints(int $clear_points) : void
    {
        $this->clear_points = $clear_points;
    }

    public function getClearPoints() : int
    {
        return $this->clear_points;
    }

    public function setMinPoints(int $min_points) : void
    {
        $this->min_points = $min_points;
    }

    public function getMinPoints() : int
    {
        return $this->min_points;
    }

    public function setPointsToWin(int $points_to_win) : void
    {
        $this->points_to_win = $points_to_win;
    }

    public function getPointsToWin() : int
    {
        return $this->points_to_win;
    }

    public function setLastSetPointsToWin(int $last_set_points_to_win) : void
    {
        $this->last_set_points_to_win = $last_set_points_to_win;
    }

    public function getLastSetPointsToWin() : int
    {
        return $this->last_set_points_to_win;
    }

    public function setMaxPoints(int $max_points) : void
    {
        $this->max_points = $max_points;
    }

    public function getMaxPoints() : int
    {
        return $this->max_points;
    }

    public function setLastSetMaxPoints(int $last_set_max_points) : void
    {
        $this->last_set_max_points = $last_set_max_points;
    }

    public function getLastSetMaxPoints() : int
    {
        return $this->last_set_max_points;
    }
}
