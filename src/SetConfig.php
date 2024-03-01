<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use stdClass;

/**
 * Configuration defining the nature of a set.
 */
final class SetConfig implements JsonSerializable
{
    /** @var int The maximum number of sets that could be played, often known as 'best of', e.g., if this has the value '5' then the match is played as 'best of 5 sets' */
    private int $max_sets = 5;

    /** @var int The number of sets that must be won to win the match. */
    private int $sets_to_win = 3;

    /** @var int The number of points lead that the winning team must have. */
    private int $clear_points = 2;

    /** @var int The minimum number of points that either team must score for a set to count as valid. */
    private int $min_points = 1;

    /** @var int The minimum number of points required to win all but the last set. */
    private int $points_to_win = 25;

    /** @var int The minimum number of points required to win the last set. */
    private int $last_set_points_to_win = 15;

    /** @var int The upper limit of points that can be scored in a set. */
    private int $max_points = 1000;

    /** @var int The upper limit of points that can be scored in the last set. */
    private int $last_set_max_points = 1000;

    /** @var Group The group that this SetConfig belongs to */
    private Group $group;

    /**
     * Contains the configuration data that define the sets in a match.
     *
     * @param Group $group The group that this SetConfig belongs to
     */
    function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * Load set configuration data from an object.
     *
     * @param object $set_data The set configuration data
     * @return SetConfig The updated SetConfig object
     */
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

    /**
     * Get the group that this SetConfig belongs to.
     *
     * @return Group The group that this SetConfig belongs to
     */
    public function getGroup() : Group
    {
        return $this->group;
    }

    /**
     * Serialize the set configuration data.
     *
     * @return mixed The serialized set configuration data
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

    /**
     * Set the maximum number of sets that could be played.
     *
     * @param int $max_sets The maximum number of sets
     */
    public function setMaxSets(int $max_sets) : void
    {
        $this->max_sets = $max_sets;
    }

    /**
     * Get the maximum number of sets that could be played.
     *
     * @return int The maximum number of sets
     */
    public function getMaxSets() : int
    {
        return $this->max_sets;
    }

    /**
     * Set the number of sets that must be won to win the match.
     *
     * @param int $sets_to_win The number of sets to win
     */
    public function setSetsToWin(int $sets_to_win) : void
    {
        $this->sets_to_win = $sets_to_win;
    }

    /**
     * Get the number of sets that must be won to win the match.
     *
     * @return int The number of sets to win
     */
    public function getSetsToWin() : int
    {
        return $this->sets_to_win;
    }

    /**
     * Set the number of points lead that the winning team must have.
     *
     * @param int $clear_points The number of clear points
     */
    public function setClearPoints(int $clear_points) : void
    {
        $this->clear_points = $clear_points;
    }

    /**
     * Get the number of points lead that the winning team must have.
     *
     * @return int The number of clear points
     */
    public function getClearPoints() : int
    {
        return $this->clear_points;
    }

    /**
     * Set the minimum number of points that either team must score for a set to count as valid.
     *
     * @param int $min_points The minimum number of points
     */
    public function setMinPoints(int $min_points) : void
    {
        $this->min_points = $min_points;
    }

    /**
     * Get the minimum number of points that either team must score for a set to count as valid.
     *
     * @return int The minimum number of points
     */
    public function getMinPoints() : int
    {
        return $this->min_points;
    }

    /**
     * Set the minimum number of points required to win all but the last set.
     *
     * @param int $points_to_win The minimum number of points to win
     */
    public function setPointsToWin(int $points_to_win) : void
    {
        $this->points_to_win = $points_to_win;
    }

    /**
     * Get the minimum number of points required to win all but the last set.
     *
     * @return int The minimum number of points to win
     */
    public function getPointsToWin() : int
    {
        return $this->points_to_win;
    }

    /**
     * Set the minimum number of points required to win the last set.
     *
     * @param int $last_set_points_to_win The minimum number of points to win the last set
     */
    public function setLastSetPointsToWin(int $last_set_points_to_win) : void
    {
        $this->last_set_points_to_win = $last_set_points_to_win;
    }

    /**
     * Get the minimum number of points required to win the last set.
     *
     * @return int The minimum number of points to win the last set
     */
    public function getLastSetPointsToWin() : int
    {
        return $this->last_set_points_to_win;
    }

    /**
     * Set the upper limit of points that can be scored in a set.
     *
     * @param int $max_points The upper limit of points in a set
     */
    public function setMaxPoints(int $max_points) : void
    {
        $this->max_points = $max_points;
    }

    /**
     * Get the upper limit of points that can be scored in a set.
     *
     * @return int The upper limit of points in a set
     */
    public function getMaxPoints() : int
    {
        return $this->max_points;
    }

    /**
     * Set the upper limit of points that can be scored in the last set.
     *
     * @param int $last_set_max_points The upper limit of points in the last set
     */
    public function setLastSetMaxPoints(int $last_set_max_points) : void
    {
        $this->last_set_max_points = $last_set_max_points;
    }

    /**
     * Get the upper limit of points that can be scored in the last set.
     *
     * @return int The upper limit of points in the last set
     */
    public function getLastSetMaxPoints() : int
    {
        return $this->last_set_max_points;
    }
}
