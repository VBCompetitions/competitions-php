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
     * @param MatchInterface $match the match this team is playing in
     * @param object $team_data The data defining this Team
     */
    function __construct(MatchInterface $match, string $id)
    {
        $this->match = $match;
        $this->id = $id;
    }

    public static function loadFromData(MatchInterface $match, object $team_data) : MatchTeam
    {
        $team = new MatchTeam($match, $team_data->id);
        if (property_exists($team_data, 'mvp')) {
            $team->setMVP($team_data->mvp);
        }
        $team->setForfeit($team_data->forfeit);
        $team->setBonusPoints($team_data->bonusPoints);
        $team->setPenaltyPoints($team_data->penaltyPoints);
        if (property_exists($team_data, 'notes')) {
            $team->setNotes($team_data->notes);
        }
        if (property_exists($team_data, 'players')) {
            $team->setPlayers($team_data->players);
        }
        return $team;
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function setForfeit(bool $forfeit) : MatchTeam
    {
        $this->forfeit = $forfeit;
        return $this;
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

    public function setBonusPoints(int $bonus_points) : MatchTeam
    {
        $this->bonus_points = $bonus_points;
        return $this;
    }

    public function getBonusPoints() : int
    {
        return $this->bonus_points;
    }

    public function setPenaltyPoints(int $penalty_points) : MatchTeam
    {
        $this->penalty_points = $penalty_points;
        return $this;
    }

    public function getPenaltyPoints() : int
    {
        return $this->penalty_points;
    }

    public function setMVP(?string $mvp) : MatchTeam
    {
        $this->mvp = $mvp;
        return $this;
    }

    public function getMVP() : string|null
    {
        return $this->mvp;
    }

    public function setNotes(?string $notes) : MatchTeam
    {
        $this->notes = $notes;
        return $this;
    }

    public function getNotes() : string
    {
        return $this->notes;
    }

    public function setPlayers(array $players) : MatchTeam
    {
        $this->players = $players;
        return $this;
    }

    /**
     * The IDs of the players in this match
     *
     * @return array<string> the IDs of the players in this match
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
