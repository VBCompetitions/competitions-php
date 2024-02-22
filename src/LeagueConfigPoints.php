<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * A team definition
 */
final class LeagueConfigPoints implements JsonSerializable
{
    /** Number of league points for playing the match */
    private int $played = 0;

    /** Number of league points for each set won */
    private int $per_set = 0;

    /** Number of league points for winning (by 2 sets or more if playing sets) */
    private int $win = 3;

    /** Number of league points for winning by 1 set */
    private int $win_by_one = 0;

    /** Number of league points for losing (by 2 sets or more if playing sets) */
    private int $lose = 0;

    /** Number of league points for losing by 1 set */
    private int $lose_by_one = 0;

    /** Number of league penalty points for forfeiting a match. This should be a positive number and will be subtracted from a team's league points for each forfeited match */
    private int $forfeit = 0;

    private LeagueConfig $league_config;

    /**
     *
     * Defined the match/court manager of a match, which may be an individual or a team
     *
     * @param MatchInterface $match The match this Manager is managing
     * @param string|object $manager_data The data for the match manager
     */
    function __construct(LeagueConfig $league_config)
    {
        $this->league_config = $league_config;
    }

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
     * Return the match manager definition suitable for saving into a competition file
     *
     * @return mixed
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

    public function getLeagueConfig() : LeagueConfig
    {
        return $this->league_config;
    }

    public function setPlayed(int $played) : LeagueConfigPoints
    {
        $this->played = $played;
        return $this;
    }

    public function getPlayed() : int
    {
        return $this->played;
    }

    public function setPerSet(int $per_set) : LeagueConfigPoints
    {
        $this->per_set = $per_set;
        return $this;
    }

    public function getPerSet() : int
    {
        return $this->per_set;
    }

    public function setWin(int $win) : LeagueConfigPoints
    {
        $this->win = $win;
        return $this;
    }

    public function getWin() : int
    {
        return $this->win;
    }

    public function setWinByOne(int $win_by_one) : LeagueConfigPoints
    {
        $this->win_by_one = $win_by_one;
        return $this;
    }

    public function getWinByOne() : int
    {
        return $this->win_by_one;
    }

    public function setLose(int $lose) : LeagueConfigPoints
    {
        $this->lose = $lose;
        return $this;
    }

    public function getLose() : int
    {
        return $this->lose;
    }

    public function setLoseByOne(int $lose_by_one) : LeagueConfigPoints
    {
        $this->lose_by_one = $lose_by_one;
        return $this;
    }

    public function getLoseByOne() : int
    {
        return $this->lose_by_one;
    }


    public function setForfeit(int $forfeit) : LeagueConfigPoints
    {
        $this->forfeit = $forfeit;
        return $this;
    }

    public function getForfeit() : int
    {
        return $this->forfeit;
    }
}
