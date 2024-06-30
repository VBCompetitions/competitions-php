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
    private ?Player $mvp = null;

    /** Did this team forfeit the match */
    private bool $forfeit = false;

    /** Does this team get any bonus points in the league. This is separate from any league points calculated from the match result, and is added to their league points */
    private int $bonus_points = 0;

    /** Does this team receive any penalty points in the league. This is separate from any league points calculated from the match result, and is subtracted from their league points */
    private int $penalty_points = 0;

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
     * @param string $id The identifier for this team
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
            if (preg_match('/^{(.*)}$/', $team_data->mvp, $mvp_match)) {
                $team->setMVP($match->getGroup()->getStage()->getCompetition()->getPlayer($mvp_match[1]));
            } else {
                $team->setMVP(new Player($match->getGroup()->getStage()->getCompetition(), Player::UNREGISTERED_PLAYER_ID, $team_data->mvp));
            }
        }
        $team->setForfeit($team_data->forfeit);
        $team->setBonusPoints($team_data->bonusPoints);
        $team->setPenaltyPoints($team_data->penaltyPoints);
        if (property_exists($team_data, 'notes')) {
            $team->setNotes($team_data->notes);
        }
        if (property_exists($team_data, 'players')) {
            $players = [];
            foreach ($team_data->players as $player_data) {
                if (preg_match('/^{(.*)}$/', $player_data, $player_ref_match)) {
                    array_push($players, $match->getGroup()->getStage()->getCompetition()->getPlayer($player_ref_match[1]));
                } else {
                    array_push($players, new Player($match->getGroup()->getStage()->getCompetition(), Player::UNREGISTERED_PLAYER_ID, $player_data));
                }
            }
            $team->setPlayers($players);
        }
        return $team;
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
            if ($this->mvp->getID() === Player::UNREGISTERED_PLAYER_ID) {
                $match_team->mvp = $this->mvp->getName();
            } else {
                $match_team->mvp = '{'.$this->mvp->getID().'}';
            }
        }

        $match_team->forfeit = $this->forfeit;
        $match_team->bonusPoints = $this->bonus_points;
        $match_team->penaltyPoints = $this->penalty_points;

        if (count($this->players) > 0) {
            $players = [];
            foreach ($this->players as $player) {
                if ($player->getID() === Player::UNREGISTERED_PLAYER_ID) {
                    array_push($players, $player->getName());
                } else {
                    array_push($players, '{'.$player->getID().'}');
                }
            }
            $match_team->players = $players;
        }

        if ($this->notes !== null) {
            $match_team->notes = $this->notes;
        }

        return $match_team;
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
     * Get the ID of the team
     *
     * @return string The team ID
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Set whether the team forfeited the match
     *
     * @param bool $forfeit Whether the team forfeited the match
     *
     * @return MatchTeam The MatchTeam instance
     */
    public function setForfeit(bool $forfeit) : MatchTeam
    {
        $this->forfeit = $forfeit;
        return $this;
    }

    /**
     * Get whether the team forfeited the match
     *
     * @return bool Whether the team forfeited the match
     */
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

    /**
     * Set the bonus points for the team
     *
     * @param int $bonus_points The bonus points for the team
     *
     * @return MatchTeam The MatchTeam instance
     */
    public function setBonusPoints(int $bonus_points) : MatchTeam
    {
        $this->bonus_points = $bonus_points;
        return $this;
    }

    /**
     * Get the bonus points for the team
     *
     * @return int The bonus points for the team
     */
    public function getBonusPoints() : int
    {
        return $this->bonus_points;
    }

    /**
     * Set the penalty points for the team
     *
     * @param int $penalty_points The penalty points for the team
     *
     * @return MatchTeam The MatchTeam instance
     */
    public function setPenaltyPoints(int $penalty_points) : MatchTeam
    {
        $this->penalty_points = $penalty_points;
        return $this;
    }

    /**
     * Get the penalty points for the team
     *
     * @return int The penalty points for the team
     */
    public function getPenaltyPoints() : int
    {
        return $this->penalty_points;
    }

    /**
     * Set the most valuable player for the team
     *
     * @param Player|null $mvp The most valuable player for the team
     *
     * @return MatchTeam The MatchTeam instance
     */
    public function setMVP(?Player $mvp) : MatchTeam
    {
        $this->mvp = $mvp;
        return $this;
    }

    /**
     * Get the most valuable player for the team
     *
     * @return Player|null The most valuable player for the team
     */
    public function getMVP() : ?Player
    {
        return $this->mvp;
    }

    /**
     * Set notes for the team
     *
     * @param string|null $notes The notes for the team
     *
     * @return MatchTeam The MatchTeam instance
     */
    public function setNotes(?string $notes) : MatchTeam
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Get notes for the team
     *
     * @return string|null The notes for the team
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Set the players for the team
     *
     * @param array<Player> $players The players for the team
     *
     * @return MatchTeam The MatchTeam instance
     */
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
}
