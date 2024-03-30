<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * Represents a manager for a match in a competition.
 */
final class MatchManager implements JsonSerializable
{
    /** @var ?string The court manager in charge of this match */
    private ?string $manager_name = null;

    /** @var ?string The team assigned to manage the match. This can either be a team ID or a team reference */
    private ?string $manager_team = null;

    /** @var MatchInterface The match this Manager is managing */
    private MatchInterface $match;

    /**
     * Constructor.
     *
     * Defines the match/court manager of a match, which may be an individual or a team.
     *
     * @param MatchInterface $match The match this Manager is managing
     * @param ?string $team_id The ID of the team managing the match
     * @param ?string $manager The name of the manager managing the match
     * @throws Exception If Match Managers must be either a team or a person
     */
    function __construct(MatchInterface $match, ?string $team_id, ?string $manager = null)
    {
        $this->match = $match;
        if ($team_id !== null) {
            $this->setTeamID($team_id);
        } else if ($manager !== null) {
            $this->setManagerName($manager);
        } else {
            throw new Exception('Match Managers must be either a team or a person');
        }
    }

    /**
     * Load match manager data from a given object.
     *
     * @param MatchInterface $match The match this Manager is managing
     * @param string|object $manager_data The data for the match manager
     * @return MatchManager The match manager instance
     */
    public static function loadFromData(MatchInterface $match, string|object $manager_data) : MatchManager
    {
        if (is_object($manager_data) && property_exists($manager_data, 'team')) {
            $manager = new MatchManager($match, $manager_data->team, null);
        } else {
            $manager = new MatchManager($match, null, $manager_data);
        }
        return $manager;
    }

    /**
     * Return the match manager definition suitable for saving into a competition file.
     *
     * @return mixed The serialized match manager data
     */
    public function jsonSerialize() : mixed
    {
        if ($this->manager_team !== null) {
            $manager = new stdClass();
            $manager->team = $this->manager_team;
            return $manager;
        }
        return $this->manager_name;
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
     * Check whether the match manager is a team or not.
     *
     * @return bool True if the manager is a team, false otherwise
     */
    public function isTeam() : bool
    {
        return $this->manager_team !== null;
    }

    /**
     * Get the ID of the team managing the match.
     *
     * @return ?string The team ID
     */
    public function getTeamID() : ?string
    {
        return $this->manager_team;
    }

    /**
     * Set the ID for the team managing the match. Note that this unsets any manager name.
     *
     * @param string $manager_team The ID for the team managing the match
     * @throws Exception If the team ID is invalid
     */
    public function setTeamID($manager_team) : void
    {
        $this->match->getGroup()->getStage()->getCompetition()->validateTeamID($manager_team, $this->match->getID(), 'manager');
        $this->manager_team = $manager_team;
        $this->manager_name = null;
    }

    /**
     * Get the name of the manager.
     *
     * @return ?string The name of the manager
     */
    public function getManagerName() : ?string
    {
        return $this->manager_name;
    }

    /**
     * Set the name of the manager managing the match. Note that this unsets any team ID.
     *
     * @param string $name The name of the manager managing the match
     * @throws Exception If the manager name is invalid
     */
    public function setManagerName($name) : void
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid manager name: must be between 1 and 1000 characters long');
        }
        $this->manager_name = $name;
        $this->manager_team = null;
    }
}
