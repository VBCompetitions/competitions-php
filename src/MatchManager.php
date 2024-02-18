<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * A team definition
 */
final class MatchManager implements JsonSerializable
{
    /** The court manager in charge of this match */
    private ?string $manager_name = null;

    /** The team assigned to manage the match. This can either be a team ID or a team reference */
    private ?string $manager_team = null;

    /** The match this Manager is managing */
    private MatchInterface $match;

    /**
     *
     * Defined the match/court manager of a match, which may be an individual or a team
     *
     * @param MatchInterface $match The match this Manager is managing
     * @param string|object $manager_data The data for the match manager
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

    public static function loadFromData(MatchInterface $match, string|object $manager_data) : MatchManager
    {
        if (property_exists($manager_data, 'team')) {
            $manager = new MatchManager($match, $manager_data->team, null);
        } else {
            $manager = new MatchManager($match, null, $manager_data);
        }
        return $manager;
    }

    /**
     * Return the match manager definition suitable for saving into a competition file
     *
     * @return mixed
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
     * Get the match this manager is managing
     *
     * @return MatchInterface the match being managed
     */
    public function getMatch() : MatchInterface
    {
        return $this->match;
    }

    /**
     * Get whether the match manager is a team or not
     *
     * @return bool whether the manager is a team or not
     */
    public function isTeam() : bool
    {
        return $this->manager_team !== null;
    }

    /**
     * Get the ID of the team managing the match
     *
     * @return ?string the team ID
     */
    public function getTeamID() : ?string
    {
        return $this->manager_team;
    }

    /**
     * Set the ID for the team managing the match.  Note that this un-sets any manager name
     *
     * @param string $manager_team the ID for the team managing the match
     */
    public function setTeamID($manager_team) : void
    {
        $this->match->getGroup()->getStage()->getCompetition()->validateTeamID($manager_team, $this->match->getID(), 'manager');
        $this->manager_team = $manager_team;
        $this->manager_name = null;
    }

    /**
     * Get the name of the manager
     *
     * @return ?string the name of the manager
     */
    public function getManagerName() : ?string
    {
        return $this->manager_name;
    }

    /**
     * Set the name of the manager managing the match.  Note that this un-sets any team ID
     *
     * @param string $manager_name the name of the manager managing the match
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
