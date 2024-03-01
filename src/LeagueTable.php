<?php

namespace VBCompetitions\Competitions;

/**
 * Represents a league table for a competition.
 */
final class LeagueTable
{
    public const ORDERING_LEAGUE_POINTS = 'PTS';
    public const ORDERING_WINS = 'WINS';
    public const ORDERING_LOSSES = 'LOSSES';
    public const ORDERING_HEAD_TO_HEAD = 'H2H';
    public const ORDERING_POINTS_FOR = 'PF';
    public const ORDERING_POINTS_AGAINST = 'PA';
    public const ORDERING_POINTS_DIFFERENCE = 'PD';
    public const ORDERING_SETS_FOR = 'SF';
    public const ORDERING_SETS_AGAINST = 'SA';
    public const ORDERING_SETS_DIFFERENCE = 'SD';

    /** @var array An array of entries in the league table */
    public array $entries = [];

    /** @var League The league associated with this table */
    private League $league;

    /** @var bool Indicates if draws are allowed in the league */
    private bool $has_draws;

    /** @var bool Indicates if the league uses sets */
    private bool $has_sets;

    /** @var array The ordering criteria for the league table */
    private array $ordering;

    /**
     * Contains the league table and some configuration for the table such as the ordering rules.
     *
     * @param League $league The league associated with this table
     */
    function __construct(League $league)
    {
        $this->league = $league;
        $this->has_draws = $league->getDrawsAllowed();
        $this->has_sets = $league->getMatchType() === MatchType::SETS;
        $this->ordering = $league->getLeagueConfig()->getOrdering();
    }

    /**
     * Get the league associated with this table.
     *
     * @return League The league associated with this table
     */
    public function getLeague() : League
    {
        return $this->league;
    }

    /**
     * Get the text representation of the ordering criteria for the league table.
     *
     * @return string The text representation of the ordering criteria
     */
    public function getOrderingText() : string
    {
        $ordering_text = 'Position is decided by '.LeagueTable::mapOrderingToString($this->ordering[0]);
        for ($i=1; $i < count($this->ordering); $i++) {
            $ordering_text .= ', then '.LeagueTable::mapOrderingToString($this->ordering[$i]);
        }
        return $ordering_text;
    }

    /**
     * Maps ordering string to human-readable format.
     *
     * @param string $ordering_string The ordering string to map
     * @return string The human-readable representation of the ordering string
     */
    private static function mapOrderingToString(string $ordering_string) : string
    {
        return match($ordering_string) {
            LeagueTable::ORDERING_LEAGUE_POINTS => 'points',
            LeagueTable::ORDERING_WINS => 'wins',
            LeagueTable::ORDERING_LOSSES => 'losses',
            LeagueTable::ORDERING_HEAD_TO_HEAD => 'head-to-head',
            LeagueTable::ORDERING_POINTS_FOR => 'points for',
            LeagueTable::ORDERING_POINTS_AGAINST => 'points against',
            LeagueTable::ORDERING_POINTS_DIFFERENCE => 'points difference',
            LeagueTable::ORDERING_SETS_FOR => 'sets for',
            LeagueTable::ORDERING_SETS_AGAINST => 'sets against',
            LeagueTable::ORDERING_SETS_DIFFERENCE => 'sets difference'
        };
    }

    /**
     * Get the text representation of the scoring system used in the league.
     *
     * @return string The text representation of the league's scoring system
     */
    public function getScoringText() : string
    {
        $textBuilder = function (int $points, string $action) : string
        {
            if ($points === 1) {
                return '1 point per '.$action.', ';
            } else {
                return $points.' points per '.$action.', ';
            }
        };

        $league_config = $this->league->getLeagueConfig()->getPoints();
        $scoring_text = 'Teams win ';
        if ($league_config->getPlayed() !== 0) {
            $scoring_text .= $textBuilder($league_config->getPlayed(), 'played');
        }
        if ($league_config->getWin() !== 0) {
            $scoring_text .= $textBuilder($league_config->getWin(), 'win');
        }
        if ($league_config->getPerSet() !== 0) {
            $scoring_text .= $textBuilder($league_config->getPerSet(), 'set');
        }
        if ($league_config->getWinByOne() !== 0 && $league_config->getWin() !== $league_config->getWinByOne()) {
            $scoring_text .= $textBuilder($league_config->getWinByOne(), 'win by one set');
        }
        if ($league_config->getLose() !== 0) {
            $scoring_text .= $textBuilder($league_config->getLose(), 'loss');
        }
        if ($league_config->getLoseByOne() !== 0 && $league_config->getWin() !== $league_config->getLoseByOne()) {
            $scoring_text .= $textBuilder($league_config->getLoseByOne(), 'loss by one set');
        }
        if ($league_config->getForfeit() !== 0) {
            $scoring_text .= $textBuilder($league_config->getForfeit(), 'forfeited match');
        }
        if (strlen($scoring_text) < 12) {
            // Everything is zero; weird but possible
            return '';
        }
        //
        return preg_replace('/(.*), ([^,]*)/', "$1 and $2", substr($scoring_text, 0, -2));
    }

    /**
     * Checks if draws are allowed in the league.
     *
     * @return bool True if draws are allowed, false otherwise
     */
    public function hasDraws() : bool
    {
        return $this->has_draws;
    }

    /**
     * Checks if the league uses sets.
     *
     * @return bool True if the league uses sets, false otherwise
     */
    public function hasSets() : bool
    {
        return $this->has_sets;
    }

    /**
     * Get the group ID associated with this league table.
     *
     * @return string The group ID associated with this league table
     */
    public function getGroupID() : string
    {
        return $this->league->getID();
    }
}
