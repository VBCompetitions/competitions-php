<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * Represents the officials for a match in a competition.
 */
final class MatchOfficials implements JsonSerializable
{
    /** @var ?string The first referee */
    private ?string $first = null;

    /** @var ?string The second referee */
    private ?string $second = null;

    /** @var ?string The challenge referee, responsible for resolving challenges from the teams */
    private ?string $challenge = null;

    /** @var ?string The assistant challenge referee, who assists the challenge referee */
    private ?string $assistant_challenge = null;

    /** @var ?string The reserve referee */
    private ?string $reserve = null;

    /** @var ?string The scorer */
    private ?string $scorer = null;

    /** @var ?string The assistant scorer */
    private ?string $assistant_scorer = null;

    /** @var array The list of linespersons */
    private array $linespersons = [];

    /** @var array The list of people in charge of managing the game balls */
    private array $ball_crew = [];

    /** @var ?string The team assigned to referee the match. This can either be a team ID or a team reference */
    private ?string $officials_team = null;

    /** @var MatchInterface The match these Officials are in */
    private MatchInterface $match;

    /**
     * Contains the officials data.
     *
     * @param MatchInterface $match The Match these Officials are in
     * @param ?string $team_id The ID of the team officiating the match
     * @param ?string $first The name of the first referee
     * @param ?bool $is_unknown Whether the team ID is unknown
     * @throws Exception If Match Officials must be either a team or a person
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

    /**
     * Load match officials data from a given object.
     *
     * @param MatchInterface $match The match these Officials are in
     * @param object $officials_data The data defining this match's Officials
     * @return MatchOfficials The match officials instance
     */
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
     * Return the match officials definition suitable for saving into a competition file.
     *
     * @return mixed The serialized match officials data
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
     * Get the match this manager is managing.
     *
     * @return MatchInterface The match being managed
     */
    public function getMatch() : MatchInterface
    {
        return $this->match;
    }

    /**
     * Get whether the match official is a team or not.
     *
     * @return bool Whether the official is a team or not
     */
    public function isTeam() : bool
    {
        return $this->officials_team !== null;
    }

    /**
     * Get the ID of the team officiating the match.
     *
     * @return ?string The team ID
     */
    public function getTeamID() : ?string
    {
        return $this->officials_team;
    }

    /**
     * Set the officiating team.
     *
     * @param ?string $officials_team The ID of the officiating team
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
     * Set the first referee.
     *
     * @param ?string $first The name of the first referee
     */
    public function setFirstRef(?string $first) : void
    {
        $this->first = $first;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has a second referee.
     *
     * @return bool Whether the match has a second referee
     */
    public function hasSecondRef() : bool
    {
        return $this->second !== null;
    }

    /**
     * Get the second referee.
     *
     * @return ?string The name of the second referee
     */
    public function getSecondRef() : ?string
    {
        return $this->second;
    }

    /**
     * Set the second referee.
     *
     * @param ?string $second The name of the second referee
     */
    public function setSecondRef(?string $second) : void
    {
        $this->second = $second;
        $this->officials_team = null;
    }

    /**
     * Get whether the match has a challenge referee.
     *
     * @return bool Whether the match has a challenge referee
     */
    public function hasChallengeRef() : bool
    {
        return $this->challenge !== null;
    }

    /**
     * Get the challenge referee's name.
     *
     * @return ?string The name of the challenge referee
     */
    public function getChallengeRef() : ?string
    {
        return $this->challenge;
    }

    /**
     * Set the challenge referee's name.
     *
     * @param ?string $challenge The name of the challenge referee
     */
    public function setChallengeRef(?string $challenge) : void
    {
        $this->challenge = $challenge;
        $this->officials_team = null;
    }

    /**
     * Check if the match has an assistant challenge referee.
     *
     * @return bool Whether the match has an assistant challenge referee
     */
    public function hasAssistantChallengeRef() : bool
    {
        return $this->assistant_challenge !== null;
    }

    /**
     * Get the name of the assistant challenge referee.
     *
     * @return ?string The name of the assistant challenge referee
     */
    public function getAssistantChallengeRef() : ?string
    {
        return $this->assistant_challenge;
    }

    /**
     * Set the name of the assistant challenge referee.
     *
     * @param ?string $assistant_challenge The name of the assistant challenge referee
     */
    public function setAssistantChallengeRef(?string $assistant_challenge) : void
    {
        $this->assistant_challenge = $assistant_challenge;
        $this->officials_team = null;
    }

    /**
     * Check if the match has a reserve referee.
     *
     * @return bool Whether the match has a reserve referee
     */
    public function hasReserveRef() : bool
    {
        return $this->reserve !== null;
    }

    /**
     * Get the name of the reserve referee.
     *
     * @return ?string The name of the reserve referee
     */
    public function getReserveRef() : ?string
    {
        return $this->reserve;
    }

    /**
     * Set the name of the reserve referee.
     *
     * @param ?string $reserve The name of the reserve referee
     */
    public function setReserveRef(?string $reserve) : void
    {
        $this->reserve = $reserve;
        $this->officials_team = null;
    }

    /**
     * Check if the match has a scorer.
     *
     * @return bool Whether the match has a scorer
     */
    public function hasScorer() : bool
    {
        return $this->scorer !== null;
    }

    /**
     * Get the name of the scorer.
     *
     * @return ?string The name of the scorer
     */
    public function getScorer() : ?string
    {
        return $this->scorer;
    }

    /**
     * Set the name of the scorer.
     *
     * @param ?string $scorer The name of the scorer
     */
    public function setScorer(?string $scorer) : void
    {
        $this->scorer = $scorer;
        $this->officials_team = null;
    }

    /**
     * Check if the match has an assistant scorer.
     *
     * @return bool Whether the match has an assistant scorer
     */
    public function hasAssistantScorer() : bool
    {
        return $this->assistant_scorer !== null;
    }

    /**
     * Get the name of the assistant scorer.
     *
     * @return ?string The name of the assistant scorer
     */
    public function getAssistantScorer() : ?string
    {
        return $this->assistant_scorer;
    }

    /**
     * Set the name of the assistant scorer.
     *
     * @param ?string $assistant_scorer The name of the assistant scorer
     */
    public function setAssistantScorer(?string $assistant_scorer) : void
    {
        $this->assistant_scorer = $assistant_scorer;
        $this->officials_team = null;
    }

    /**
     * Check if the match has any linespersons.
     *
     * @return bool Whether the match has any linespersons
     */
    public function hasLinespersons() : bool
    {
        return count($this->linespersons) > 0;
    }

    /**
     * Get the list of linespersons.
     *
     * @return array<string> The list of linespersons
     */
    public function getLinespersons() : array
    {
        return $this->linespersons;
    }

    /**
     * Set the list of linespersons.
     *
     * @param array<string> $linespersons The list of linespersons
     */
    public function setLinespersons(array $linespersons) : void
    {
        $this->linespersons = $linespersons;
        $this->officials_team = null;
    }

    /**
     * Check if the match has a ball crew.
     *
     * @return bool Whether the match has a ball crew
     */
    public function hasBallCrew() : bool
    {
        return count($this->ball_crew) > 0;
    }

    /**
     * Get the list of ball crew members.
     *
     * @return array<string> The list of ball crew members
     */
    public function getBallCrew() : array
    {
        return $this->ball_crew;
    }

    /**
     * Set the list of ball crew members.
     *
     * @param array<string> $ball_crew The list of ball crew members
     */
    public function setBallCrew(array $ball_crew) : void
    {
        $this->ball_crew = $ball_crew;
        $this->officials_team = null;
    }
}
