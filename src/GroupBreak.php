<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use stdClass;

/**
 * A break in play, possibly while other matches are going on in other competitions running in parallel
 */
final class GroupBreak implements JsonSerializable, BreakInterface
{
    /** The type of match, i.e. 'break' */
    // public string $type;

    /** The start time for the break */
    private ?string $start = null;

    /** The date of the break */
    private ?string $date = null;

    /** The duration of the break */
    private ?string $duration = null;

    /** The name for the break, e.g. 'Lunch break' */
    private ?string $name = null;

    private Group $group;

    /**
     * Contains the match break data
     *
     * @param Group $group The Group this break is in
     * @param object $break_data The data defining this Break
     */
    function __construct($group)
    {
        $this->group = $group;
    }

    public function loadFromData(object $break_data) : GroupBreak
    {
        if (property_exists($break_data, 'start')) {
            $this->setStart($break_data->start);
        }
        if (property_exists($break_data, 'date')) {
            $this->setDate($break_data->date);
        }
        if (property_exists($break_data, 'duration')) {
            $this->setDuration($break_data->duration);
        }
        if (property_exists($break_data, 'name')) {
            $this->setName($break_data->name);
        }
        return $this;
    }

    /**
     * Return the match break data suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $break = new stdClass();
        $break->type = 'break';

        if ($this->start !== null) {
            $break->start = $this->start;
        }
        if ($this->date !== null) {
            $break->date = $this->date;
        }
        if ($this->duration !== null) {
            $break->duration = $this->duration;
        }
        if ($this->name !== null) {
            $break->name = $this->name;
        }

        return $break;
    }

    /**
     * Get the Group this break is in
     *
     * @return Group the group this break is in
     */
    public function getGroup() : Group
    {
        return $this->group;
    }

    /**
     * Set the start time for this break
     *
     * @param string $start the start time for this break
     *
     * @return GroupBreak this break
     */
    public function setStart(null|string $start) : GroupBreak
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Get the start time for this break
     *
     * @return string the start time for this break
     */
    public function getStart() : ?string
    {
        return $this->start;
    }

    /**
     * Set the date for this break
     *
     * @param string $date the date for this break
     *
     * @return GroupBreak this break
     */
    public function setDate(null|string $date) : GroupBreak
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get the date for this break
     *
     * @return string the date for this break
     */
    public function getDate() : ?string
    {
        return $this->date;
    }

    /**
     * Set the duration for this break
     *
     * @param string $duration the duration for this break
     *
     * @return GroupBreak this break
     */
    public function setDuration(null|string $duration) : GroupBreak
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Get the duration for this break
     *
     * @return string the duration for this break
     */
    public function getDuration() : ?string
    {
        return $this->duration;
    }

    /**
     * Set the name for this break
     *
     * @param string $name the name for this break
     *
     * @return GroupBreak this break
     */
    public function setName(null|string $name) : GroupBreak
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name for this break
     *
     * @return string the name for this break
     */
    public function getName() : ?string
    {
        return $this->name;
    }
}
