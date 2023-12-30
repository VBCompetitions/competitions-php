<?php

namespace VBCompetitions\Competitions;

use JsonSerializable;
use stdClass;

/**
 * A break in play, possibly while other matches are going on in other competitions running in parallel
 */
final class IfUnknownBreak implements JsonSerializable
{
    /** The type of match, i.e. 'break' */
    // public string $type;

    /** The start time for the break */
    public ?string $start = null;

    /** The date of the break */
    public ?string $date = null;

    /** The duration of the break */
    public ?string $duration = null;

    /** The name for the break, e.g. 'Lunch break' */
    public ?string $name = null;

    public IfUnknown $if_unknown;

    /**
     * Contains the match break data
     *
     * @param IfUnknown $if_unknown The IfUnknown this break is in
     * @param object $break_data The data defining this break
     */
    function __construct($if_unknown, $break_data)
    {
        $this->if_unknown = $if_unknown;
        if (property_exists($break_data, 'start')) {
            $this->start = $break_data->start;
        }
        if (property_exists($break_data, 'date')) {
            $this->date = $break_data->date;
        }
        if (property_exists($break_data, 'duration')) {
            $this->duration = $break_data->duration;
        }
        if (property_exists($break_data, 'name')) {
            $this->name = $break_data->name;
        }
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
     * Get the date for this break
     *
     * @return string the date for this break
     */
    public function getDate() : ?string
    {
        return $this->date;
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
     * Get the name for this break
     *
     * @return string the name for this break
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Get the IfUnknown this break is in
     *
     * @return IfUnknown the IfUnknown this break is in
     */
    public function getIfUnknown() : IfUnknown
    {
        return $this->if_unknown;
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
}
