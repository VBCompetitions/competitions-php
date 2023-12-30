<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/** Represents a team that plays in a match */
final class MatchTeam implements JsonSerializable
{
    /** The identifier for the team.  This may be an exact identifier or a team reference (see the documentation) */
    private string $id;

    /** This team's most valuable player award */
    private ?string $mvp = null;

    /** Did this team forfeit the match */
    private bool $forfeit;

    /** Does this team get any bonus points in the league. This is separate from any league points calculated from the match result, and is added to their league points */
    private int $bonus_points;

    /** Does this team receive any penalty points in the league. This is separate from any league points calculated from the match result, and is subtracted from their league points */
    private int $penalty_points;

    /** Free form string to add notes about the team relating to this match.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** The list of players from this team that played in this match */
    private array $players = [];

    /** The match this team is playing in */
    private MatchInterface $match;

    /**
     *
     * Contains the team data of a match, creating any metadata needed
     *
     * @param object $team_data The data defining this Team
     */
    function __construct(object $team_data, MatchInterface $match)
    {
        $this->match = $match;
        $this->id = $team_data->id;
        if (property_exists($team_data, 'mvp')) {
            $this->mvp = $team_data->mvp;
        }
        $this->forfeit = $team_data->forfeit;
        $this->bonus_points = $team_data->bonusPoints;
        $this->penalty_points = $team_data->penaltyPoints;
        if (property_exists($team_data, 'notes')) {
            $this->notes = $team_data->notes;
        }
        if (property_exists($team_data, 'players')) {
            $this->players = $team_data->players;
        }
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getForfeit() : bool
    {
        return $this->forfeit;
    }

    /**
     * Get the array of scores for this team in this match
     *
     * @return array<int> the team's scores
     */
    public function getScores() : array
    {
        if ($this->match->getHomeTeam()->getID() === $this->id) {
            return $this->match->getHomeTeamScores();
        }
        return $this->match->getAwayTeamScores();
    }

    public function getBonusPoints() : int
    {
        return $this->bonus_points;
    }

    public function getPenaltyPoints() : int
    {
        return $this->penalty_points;
    }

    public function getMVP() : string|null
    {
        return $this->mvp;
    }

    public function getNotes() : string
    {
        return $this->notes;
    }

    /**
     * The players in this match
     *
     * @return array<Player> the players in this match
     */
    public function getPlayers() : array
    {
        return $this->players;
    }

    /**
     * Get the match the team is playing in
     *
     * @return MatchInterface the match this team plays in
     */
    public function getMatch() : MatchInterface
    {
        return $this->match;
    }

    /**
     * Return the team data suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $match_team = new stdClass();

        $match_team->id = $this->id;
        $match_team->scores = $this->getScores();
        if ($this->mvp !== null) {
            $match_team->mvp = $this->mvp;
        }
        $match_team->forfeit = $this->forfeit;
        $match_team->bonusPoints = $this->bonus_points;
        $match_team->penaltyPoints = $this->penalty_points;
        if ($this->notes !== null) {
            $match_team->notes = $this->notes;
        }
        $match_team->players = $this->players;

        return $match_team;
    }
}
