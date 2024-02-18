<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * A team definition
 */
final class MatchOfficials implements JsonSerializable
{

    /** The first referee*/
    private ?string $first = null;

    /** The second referee*/
    private ?string $second = null;

    /** The challenge referee, responsible for resolving challenges from the teams*/
    private ?string $challenge = null;

    /** The assistant challenge referee, who assists the challenge referee*/
    private ?string $assistant_challenge = null;

    /** The reserve referee*/
    private ?string $reserve = null;

    /** The scorer*/
    private ?string $scorer = null;

    /** The assistant scorer*/
    private ?string $assistant_scorer = null;

    /** The list of linespersons*/
    private array $linespersons = [];

    /** The list of people in charge of managing the game balls*/
    private array $ball_crew = [];

    /** The team assigned to referee the match.  This can either be a team ID or a team reference */
    private ?string $officials_team = null;

    /** The match this Manager is managing */
    private MatchInterface $match;

    /**
     * Contains the officials data
     *
     * @param MatchInterface $match The Match these Officials are in
     * @param object $officials_data The data defining this match's Officials
     *
     * @throws Exception If the two teams have scores arrays of different lengths
     */
    function __construct(MatchInterface $match, ?string $team_id, ?string $first = null, ?bool $is_unknown = false)
    {
        $this->match = $match;
        if ($team_id !== null) {
            $this->setTeamID($team_id, $is_unknown === true);
        } else if ($first !== null) {
            $this->setFirstRef($first);
        } else {
            throw new Exception('Match Officials must be either a team or a person');
        }
    }

    public static function loadFromData(MatchInterface $match, object $officials_data) : MatchOfficials
    {
        if (property_exists($officials_data, 'team')) {
            $officials = new MatchOfficials($match, $officials_data->team, null, $match instanceof IfUnknownMatch);
        } else {
            $officials = new MatchOfficials($match, null, $officials_data->first, $match instanceof IfUnknownMatch);
            if (property_exists($officials_data, 'second')) {
                $officials->setSecondRef($officials_data->second);
            }
            if (property_exists($officials_data, 'challenge')) {
                $officials->setChallengeRef($officials_data->challenge);
            }
            if (property_exists($officials_data, 'assistantChallenge')) {
                $officials->setAssistantChallengeRef($officials_data->assistantChallenge);
            }
            if (property_exists($officials_data, 'reserve')) {
                $officials->setReserveRef($officials_data->reserve);
            }
            if (property_exists($officials_data, 'scorer')) {
                $officials->setScorer($officials_data->scorer);
            }
            if (property_exists($officials_data, 'assistantScorer')) {
                $officials->setAssistantScorer($officials_data->assistantScorer);
            }
            if (property_exists($officials_data, 'linespersons')) {
                $officials->setLinespersons($officials_data->linespersons);
            }
            if (property_exists($officials_data, 'ballCrew')) {
                $officials->setBallCrew($officials_data->ballCrew);
            }
        }

        return $officials;
    }

    /**
     * Return the match officials definition suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $officials = new stdClass();
        if ($this->officials_team !== null) {
            $officials->team = $this->officials_team;
            return $officials;
        }

        $officials->first = $this->first;
        if ($this->second !== null) {
            $officials->second = $this->second;
        }
        if ($this->challenge !== null) {
            $officials->challenge = $this->challenge;
        }
        if ($this->assistant_challenge !== null) {
            $officials->assistantChallenge = $this->assistant_challenge;
        }
        if ($this->reserve !== null) {
            $officials->reserve = $this->reserve;
        }
        if ($this->scorer !== null) {
            $officials->scorer = $this->scorer;
        }
        if ($this->assistant_scorer !== null) {
            $officials->assistantScorer = $this->assistant_scorer;
        }
        if ($this->linespersons !== null) {
            $officials->linespersons = $this->linespersons;
        }
        if ($this->ball_crew !== null) {
            $officials->ballCrew = $this->ball_crew;
        }

        return $officials;
    }

    /**
     * Get the match this manager is managing
     *
     * @return MatchInterface the match being managed
     */
    public function getMatch() : MatchInterface
    {
        return $this->match;
    }

    /**
     * Get whether the match official is a team or not
     *
     * @return bool whether the official is a team or not
     */
    public function isTeam() : bool
    {
        return $this->officials_team !== null;
    }

    /**
     * Get the ID of the team officiating the match
     *
     * @return ?string the team ID
     */
    public function getTeamID() : ?string
    {
        return $this->officials_team;
    }

    /**
     * Set the officiating team
     *
     * @param ?string $officials_team the ID of the officiating team
     */
    public function setTeamID(?string $officials_team, bool $is_unknown = false) : void
    {
        if (!$is_unknown) {
            $this->match->getGroup()->getCompetition()->validateTeamID($officials_team, $this->match->getID(), 'officials');
        }
        $this->officials_team = $officials_team;
        $this->first = null;
        $this->second = null;
        $this->challenge = null;
        $this->assistant_challenge = null;
        $this->reserve = null;
        $this->scorer = null;
        $this->assistant_scorer = null;
        $this->linespersons = [];
        $this->ball_crew = [];
    }

    /**
     * Get the first referee
     *
     * @return ?string the name of the first referee
     */
    public function getFirstRef() : ?string
    {
        return $this->first;
    }

    /**
     * Set the first referee
     *
     * @param ?string $first the name of the first referee
     */
    public function setFirstRef(?string $first) : void
    {
        $this->first = $first;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has a second referee
     *
     * @return bool whether the match has a second referee
     */
    public function hasSecondRef() : bool
    {
        return $this->second !== null;
    }

    /**
     * Get the second referee
     *
     * @return ?string the name of the second referee
     */
    public function getSecondRef() : ?string
    {
        return $this->second;
    }

    /**
     * Set the second referee
     *
     * @param ?string $second the name of the second referee
     */
    public function setSecondRef(?string $second) : void
    {
        $this->second = $second;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has a challenge referee
     *
     * @return bool whether the match has a challenge referee
     */
    public function hasChallengeRef() : bool
    {
        return $this->challenge !== null;
    }

    /**
     * Get the challenge referee
     *
     * @return ?string the name of the challenge referee
     */
    public function getChallengeRef() : ?string
    {
        return $this->challenge;
    }

    /**
     * Set the challenge referee
     *
     * @param ?string $challenge the name of the challenge referee
     */
    public function setChallengeRef(?string $challenge) : void
    {
        $this->challenge = $challenge;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has an assistant challenge referee
     *
     * @return bool whether the match has an assistant challenge referee
     */
    public function hasAssistantChallengeRef() : bool
    {
        return $this->assistant_challenge !== null;
    }

    /**
     * Get the assistant challenge referee
     *
     * @return ?string the name of the assistant challenge referee
     */
    public function getAssistantChallengeRef() : ?string
    {
        return $this->assistant_challenge;
    }

    /**
     * Set the assistant challenge referee
     *
     * @param ?string $assistant_challenge the name of the assistant challenge referee
     */
    public function setAssistantChallengeRef(?string $assistant_challenge) : void
    {
        $this->assistant_challenge = $assistant_challenge;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has a reserve referee
     *
     * @return bool whether the match has a reserve referee
     */
    public function hasReserveRef() : bool
    {
        return $this->reserve !== null;
    }

    /**
     * Get the reserve referee
     *
     * @return ?string the name of the reserve referee
     */
    public function getReserveRef() : ?string
    {
        return $this->reserve;
    }

    /**
     * Set the reserve referee
     *
     * @param ?string $reserve the name of the reserve referee
     */
    public function setReserveRef(?string $reserve) : void
    {
        $this->reserve = $reserve;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has a scorer
     *
     * @return bool whether the match has a scorer
     */
    public function hasScorer() : bool
    {
        return $this->scorer !== null;
    }

    /**
     * Get the scorer
     *
     * @return ?string the name of the scorer
     */
    public function getScorer() : ?string
    {
        return $this->scorer;
    }

    /**
     * Set the scorer
     *
     * @param ?string $scorer the name of the scorer
     */
    public function setScorer(?string $scorer) : void
    {
        $this->scorer = $scorer;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has an assistant scorer
     *
     * @return bool whether the match has an assistant scorer
     */
    public function hasAssistantScorer() : bool
    {
        return $this->assistant_scorer !== null;
    }

    /**
     * Get the assistant scorer
     *
     * @return ?string the name of the assistant scorer
     */
    public function getAssistantScorer() : ?string
    {
        return $this->assistant_scorer;
    }

    /**
     * Set the assistant scorer
     *
     * @param ?string $assistant_scorer the name of the assistant scorer
     */
    public function setAssistantScorer(?string $assistant_scorer) : void
    {
        $this->assistant_scorer = $assistant_scorer;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has any linespersons
     *
     * @return bool whether the match has any linespersons
     */
    public function hasLinespersons() : bool
    {
        return count($this->linespersons) > 0;
    }

    /**
     * Get the list of linespersons
     *
     * @return array<string> the list of linespersons
     */
    public function getLinespersons() : array
    {
        return $this->linespersons;
    }

    /**
     * Set the linespersons
     *
     * @param array<string> $linespersons the name of the linespersons
     */
    public function setLinespersons(array $linespersons) : void
    {
        $this->linespersons = $linespersons;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has a ball crew
     *
     * @return bool whether the match has a ball crew
     */
    public function hasBallCrew() : bool
    {
        return count($this->ball_crew) > 0;
    }

    /**
     * Get the list of ball crew members
     *
     * @return array<string> the list of ball crew members
     */
    public function getBallCrew() : array
    {
        return $this->ball_crew;
    }

    /**
     * Set the ball crew
     *
     * @param array<string> $ball_crew the name of the linespersons
     */
    public function setBallCrew(array $ball_crew) : void
    {
        $this->ball_crew = $ball_crew;
        $this->officials_team = null;
    }
}
