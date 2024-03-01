<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use stdClass;

/**
 * Configuration for league points in a competition.
 */
final class LeagueConfigPoints implements JsonSerializable
{
    /** @var int Number of league points for playing the match */
    private int $played = 0;

    /** @var int Number of league points for each set won */
    private int $per_set = 0;

    /** @var int Number of league points for winning (by 2 sets or more if playing sets) */
    private int $win = 3;

    /** @var int Number of league points for winning by 1 set */
    private int $win_by_one = 0;

    /** @var int Number of league points for losing (by 2 sets or more if playing sets) */
    private int $lose = 0;

    /** @var int Number of league points for losing by 1 set */
    private int $lose_by_one = 0;

    /** @var int Number of league penalty points for forfeiting a match */
    private int $forfeit = 0;

    /** @var LeagueConfig The league configuration associated with these points */
    private LeagueConfig $league_config;

    /**
     * Contains configuration for the league points.
     *
     * @param LeagueConfig $league_config The league configuration associated with these points
     */
    function __construct(LeagueConfig $league_config)
    {
        $this->league_config = $league_config;
    }

    /**
     * Load league points configuration data from a provided object.
     *
     * @param object $league_config_data The league points configuration data to load
     * @return LeagueConfigPoints Returns the LeagueConfigPoints instance after loading the data
     */
    public function loadFromData(object $league_config_data) : LeagueConfigPoints
    {
        if (property_exists($league_config_data, 'played')) {
            $this->setPlayed($league_config_data->played);
        }
        if (property_exists($league_config_data, 'perSet')) {
            $this->setPerSet($league_config_data->perSet);
        }
        if (property_exists($league_config_data, 'win')) {
            $this->setWin($league_config_data->win);
        }
        if (property_exists($league_config_data, 'winByOne')) {
            $this->setWinByOne($league_config_data->winByOne);
        }
        if (property_exists($league_config_data, 'lose')) {
            $this->setLose($league_config_data->lose);
        }
        if (property_exists($league_config_data, 'loseByOne')) {
            $this->setLoseByOne($league_config_data->loseByOne);
        }
        if (property_exists($league_config_data, 'forfeit')) {
            $this->setForfeit($league_config_data->forfeit);
        }

        return $this;
    }

    /**
     * Serialize the league points configuration data for JSON representation.
     *
     * @return mixed The serialized league points configuration data
     */
    public function jsonSerialize() : mixed
    {
        $league_config_points = new stdClass();
        $league_config_points->played = $this->played;
        $league_config_points->perSet = $this->per_set;
        $league_config_points->win = $this->win;
        $league_config_points->winByOne = $this->win_by_one;
        $league_config_points->lose = $this->lose;
        $league_config_points->loseByOne = $this->lose_by_one;
        $league_config_points->forfeit = $this->forfeit;
        return $league_config_points;
    }

    /**
     * Get the league configuration associated with these points.
     *
     * @return LeagueConfig The league configuration associated with these points
     */
    public function getLeagueConfig() : LeagueConfig
    {
        return $this->league_config;
    }

    /**
     * Set the number of league points for playing the match.
     *
     * @param int $played The number of league points for playing the match
     * @return LeagueConfigPoints Returns the LeagueConfigPoints instance for method chaining
     */
    public function setPlayed(int $played) : LeagueConfigPoints
    {
        $this->played = $played;
        return $this;
    }

    /**
     * Get the number of league points for playing the match.
     *
     * @return int The number of league points for playing the match
     */
    public function getPlayed() : int
    {
        return $this->played;
    }

    /**
     * Set the number of league points for each set won.
     *
     * @param int $per_set The number of league points for each set won
     * @return LeagueConfigPoints Returns the LeagueConfigPoints instance for method chaining
     */
    public function setPerSet(int $per_set) : LeagueConfigPoints
    {
        $this->per_set = $per_set;
        return $this;
    }

    /**
     * Get the number of league points for each set won.
     *
     * @return int The number of league points for each set won
     */
    public function getPerSet() : int
    {
        return $this->per_set;
    }

    /**
     * Set the number of league points for winning.
     *
     * @param int $win The number of league points for winning
     * @return LeagueConfigPoints Returns the LeagueConfigPoints instance for method chaining
     */
    public function setWin(int $win) : LeagueConfigPoints
    {
        $this->win = $win;
        return $this;
    }

    /**
     * Get the number of league points for winning.
     *
     * @return int The number of league points for winning
     */
    public function getWin() : int
    {
        return $this->win;
    }

    /**
     * Set the number of league points for winning by one set.
     *
     * @param int $win_by_one The number of league points for winning by one set
     * @return LeagueConfigPoints Returns the LeagueConfigPoints instance for method chaining
     */
    public function setWinByOne(int $win_by_one) : LeagueConfigPoints
    {
        $this->win_by_one = $win_by_one;
        return $this;
    }

    /**
     * Get the number of league points for winning by one set.
     *
     * @return int The number of league points for winning by one set
     */
    public function getWinByOne() : int
    {
        return $this->win_by_one;
    }

    /**
     * Set the number of league points for losing.
     *
     * @param int $lose The number of league points for losing
     * @return LeagueConfigPoints Returns the LeagueConfigPoints instance for method chaining
     */
    public function setLose(int $lose) : LeagueConfigPoints
    {
        $this->lose = $lose;
        return $this;
    }

    /**
     * Get the number of league points for losing.
     *
     * @return int The number of league points for losing
     */
    public function getLose() : int
    {
        return $this->lose;
    }

    /**
     * Set the number of league points for losing by one set.
     *
     * @param int $lose_by_one The number of league points for losing by one set
     * @return LeagueConfigPoints Returns the LeagueConfigPoints instance for method chaining
     */
    public function setLoseByOne(int $lose_by_one) : LeagueConfigPoints
    {
        $this->lose_by_one = $lose_by_one;
        return $this;
    }

    /**
     * Get the number of league points for losing by one set.
     *
     * @return int The number of league points for losing by one set
     */
    public function getLoseByOne() : int
    {
        return $this->lose_by_one;
    }


    /**
     * Set the number of league penalty points for forfeiting a match.
     *
     * @param int $forfeit The number of league penalty points for forfeiting a match
     * @return LeagueConfigPoints Returns the LeagueConfigPoints instance for method chaining
     */
    public function setForfeit(int $forfeit) : LeagueConfigPoints
    {
        $this->forfeit = $forfeit;
        return $this;
    }

    /**
     * Get the number of league penalty points for forfeiting a match.
     *
     * @return int The number of league penalty points for forfeiting a match
     */
    public function getForfeit() : int
    {
        return $this->forfeit;
    }
}
