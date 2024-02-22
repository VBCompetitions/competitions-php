<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * A team definition
 */
final class LeagueConfig implements JsonSerializable
{
    /** An array of parameters that define how the league positions are worked out */
    private array $ordering;

    /** Properties defining how to calculate the league points based on match results */
    private LeagueConfigPoints $points;

    /** The league this config is for */
    private League $league;

    /**
     *
     * Defined the match/court manager of a match, which may be an individual or a team
     *
     * @param MatchInterface $match The match this Manager is managing
     * @param string|object $manager_data The data for the match manager
     */
    function __construct(League $league)
    {
        $this->league = $league;
    }

    public function loadFromData(object $league_data) : LeagueConfig
    {
        $league_config_points = (new LeagueConfigPoints($this))->loadFromData($league_data->points);
        $this->setOrdering($league_data->ordering)->setPoints($league_config_points);
        return $this;
    }

    /**
     * Return the match manager definition suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $league_config = new stdClass();
        $league_config->ordering = $this->ordering;
        $league_config->points = $this->points;
        return $league_config;
    }

    /**
     * Get the league this config is for
     *
     * @return League the league this config is for
     */
    public function getLeague() : League
    {
        return $this->league;
    }

    public function setOrdering($ordering) : LeagueConfig
    {
        $this->ordering = $ordering;
        return $this;
    }

    /**
     * Get the ordering config for the league
     *
     * @return array the ordering config for the league
     */
    public function getOrdering() : array
    {
        return $this->ordering;
    }

    /**
     * Get the points config for the league
     *
     * @return LeagueConfigPoints the points config for the league
     */
    public function getPoints() : LeagueConfigPoints
    {
        return $this->points;
    }

    public function setPoints(LeagueConfigPoints $points) : LeagueConfig
    {
        $this->points = $points;
        return $this;
    }
}
