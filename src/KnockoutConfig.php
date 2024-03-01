<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use stdClass;

/**
 * Represents a knockout configuration for a competition.
 */
final class KnockoutConfig implements JsonSerializable
{
    /** @var array An ordered mapping from a position to a team ID */
    private array $standing = [];

    /** @var Group The knockout group this configuration is associated with */
    private Group $group;

    /**
     * Constructs a new KnockoutConfig instance.
     *
     * @param Group $group The group associated with this knockout configuration.
     */
    function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * Loads knockout configuration data from an object.
     *
     * @param object $knockout_data The data object containing knockout configuration information.
     *
     * @return KnockoutConfig The updated KnockoutConfig instance.
     */
    public function loadFromData(object $knockout_data) : KnockoutConfig
    {
        $this->setStanding($knockout_data->standing);
        return $this;
    }

    /**
     * Serializes the knockout configuration data for storage.
     *
     * @return mixed The serialized knockout configuration data.
     */
    public function jsonSerialize() : mixed
    {
        $knockout = new stdClass();
        $knockout->standing = $this->standing;
        return $knockout;
    }

    /**
     * Gets the group associated with this knockout configuration.
     *
     * @return Group The group associated with this knockout configuration.
     */
    public function getGroup() : Group
    {
        return $this->group;
    }

    /**
     * Sets the standing array for this knockout configuration.
     *
     * @param array<object> $standing The array of standing maps.
     *
     * @return KnockoutConfig The KnockoutConfig instance with the updated standing array.
     */
    public function setStanding(array $standing) : KnockoutConfig
    {
        $this->standing = $standing;
        return $this;
    }

    /**
     * Gets the standing array for this knockout configuration.
     *
     * @return array<object> The array of standing maps for this knockout configuration.
     */
    public function getStanding() : array
    {
        return $this->standing;
    }
}
