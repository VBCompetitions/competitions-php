<?php

namespace VBCompetitions\Competitions;

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

    public array $entries = [];

    public League $league;

    private bool $has_draws;
    private bool $has_sets;

    private array $ordering;

    function __construct(League $league)
    {
        $this->league = $league;
        $this->has_draws = $league->getDrawsAllowed();
        $this->has_sets = $league->getMatchType() === MatchType::SETS;
        $this->ordering = $league->getLeagueConfig()->ordering;
    }

    public function getOrderingText() : string
    {
        $ordering_text = 'Position is decided by '.LeagueTable::mapOrderingToString($this->ordering[0]);
        for ($i=1; $i < count($this->ordering); $i++) {
            $ordering_text .= ', then '.LeagueTable::mapOrderingToString($this->ordering[$i]);
        }
        return $ordering_text;
    }

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

        $league_config = $this->league->getLeagueConfig()->points;
        $scoring_text = 'Teams win ';
        if ($league_config->played !== 0) {
            $scoring_text .= $textBuilder($league_config->played, 'played');
        }
        if ($league_config->win !== 0) {
            $scoring_text .= $textBuilder($league_config->win, 'win');
        }
        if ($league_config->perSet !== 0) {
            $scoring_text .= $textBuilder($league_config->perSet, 'set');
        }
        if ($league_config->winByOne !== 0 && $league_config->win !== $league_config->winByOne) {
            $scoring_text .= $textBuilder($league_config->winByOne, 'win by one set');
        }
        if ($league_config->lose !== 0) {
            $scoring_text .= $textBuilder($league_config->lose, 'loss');
        }
        if ($league_config->loseByOne !== 0 && $league_config->win !== $league_config->loseByOne) {
            $scoring_text .= $textBuilder($league_config->loseByOne, 'loss by one set');
        }
        if ($league_config->forfeit !== 0) {
            $scoring_text .= $textBuilder($league_config->forfeit, 'forfeited match');
        }
        if (strlen($scoring_text) < 12) {
            // Everything is zero; weird but possible
            return '';
        }
        //
        return preg_replace('/(.*), ([^,]*)/', "$1 and $2", substr($scoring_text, 0, -2));
    }

    public function hasDraws() : bool
    {
        return $this->has_draws;
    }

    public function hasSets() : bool
    {
        return $this->has_sets;
    }

    public function getGroupID() : string
    {
        return $this->league->getID();
    }
}
