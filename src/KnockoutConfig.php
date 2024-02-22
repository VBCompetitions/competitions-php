<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * A team definition
 */
final class KnockoutConfig implements JsonSerializable
{
    /** An ordered mapping from a position to a team ID */
    private array $standing = [];

    /** The knockout group this config is for */
    private Group $group;

    /**
     *
     * Defined the match/court manager of a match, which may be an individual or a team
     *
     * @param MatchInterface $match The match this Manager is managing
     * @param string|object $manager_data The data for the match manager
     */
    function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function loadFromData(object $knockout_data) : KnockoutConfig
    {
        $this->setStanding($knockout_data->standing);
        return $this;
    }

    /**
     * Return the match manager definition suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $knockout = new stdClass();
        $knockout->standing = $this->standing;
        return $knockout;
    }

    public function getGroup() : Group
    {
        return $this->group;
    }

    /**
     * Set the array of standing maps for this config
     *
     * @param array<object> $standing The array of standing maps
     *
     * @return KnockoutConfig the knockout config being managed
     */
    public function setStanding(array $standing) : KnockoutConfig
    {
        $this->standing = $standing;
        return $this;
    }

    /**
     * Get the array of standing maps for this config
     *
     * @return array<object> the array of standing maps for this config
     */
    public function getStanding() : array
    {
        return $this->standing;
    }
}
