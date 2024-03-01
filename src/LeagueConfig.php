<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use stdClass;

/**
 * Configuration for a league within a competition.
 */
final class LeagueConfig implements JsonSerializable
{
    /** @var array An array of parameters that define how the league positions are determined */
    private array $ordering;

    /** @var LeagueConfigPoints Properties defining how to calculate the league points based on match results */
    private LeagueConfigPoints $points;

    /** @var League The league this config is for */
    private League $league;

    /**
     * Contains the configuration for the league.
     *
     * @param League $league The league this configuration is associated with
     */
    function __construct(League $league)
    {
        $this->league = $league;
    }

    /**
     * Load league configuration data from a provided object.
     *
     * @param object $league_data The league configuration data to load
     * @return LeagueConfig Returns the LeagueConfig instance after loading the data
     */
    public function loadFromData(object $league_data) : LeagueConfig
    {
        $league_config_points = (new LeagueConfigPoints($this))->loadFromData($league_data->points);
        $this->setOrdering($league_data->ordering)->setPoints($league_config_points);
        return $this;
    }

    /**
     * Serialize the league configuration data for JSON representation.
     *
     * @return mixed The serialized league configuration data
     */
    public function jsonSerialize() : mixed
    {
        $league_config = new stdClass();
        $league_config->ordering = $this->ordering;
        $league_config->points = $this->points;
        return $league_config;
    }

    /**
     * Get the league associated with this configuration.
     *
     * @return League The league associated with this configuration
     */
    public function getLeague() : League
    {
        return $this->league;
    }

    /**
     * Set the ordering configuration for the league.
     *
     * @param array $ordering The ordering configuration for the league
     * @return LeagueConfig Returns the LeagueConfig instance for method chaining
     */
    public function setOrdering(array $ordering) : LeagueConfig
    {
        $this->ordering = $ordering;
        return $this;
    }

    /**
     * Get the ordering configuration for the league.
     *
     * @return array The ordering configuration for the league
     */
    public function getOrdering() : array
    {
        return $this->ordering;
    }

    /**
     * Get the points configuration for the league.
     *
     * @return LeagueConfigPoints The points configuration for the league
     */
    public function getPoints() : LeagueConfigPoints
    {
        return $this->points;
    }

    /**
     * Set the points configuration for the league.
     *
     * @param LeagueConfigPoints $points The points configuration for the league
     * @return LeagueConfig Returns the LeagueConfig instance for method chaining
     */
    public function setPoints(LeagueConfigPoints $points) : LeagueConfig
    {
        $this->points = $points;
        return $this;
    }
}
