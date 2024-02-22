<?php

namespace VBCompetitions\Competitions;

use DateTime;
use Exception;
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
    private ?string $start = null;

    /** The date of the break */
    private ?string $date = null;

    /** The duration of the break */
    private ?string $duration = null;

    /** The name for the break, e.g. 'Lunch break' */
    private ?string $name = null;

    private IfUnknown $if_unknown;

    /**
     * Contains the match break data
     *
     * @param IfUnknown $if_unknown The IfUnknown this break is in
     * @param object $break_data The data defining this break
     */
    function __construct($if_unknown)
    {
        $this->if_unknown = $if_unknown;
    }

    public function loadFromData(object $if_unknown_break_data) : IfUnknownBreak
    {
        if (property_exists($if_unknown_break_data, 'start')) {
            $this->setStart($if_unknown_break_data->start);
        }
        if (property_exists($if_unknown_break_data, 'date')) {
            $this->setDate($if_unknown_break_data->date);
        }
        if (property_exists($if_unknown_break_data, 'duration')) {
            $this->setDuration($if_unknown_break_data->duration);
        }
        if (property_exists($if_unknown_break_data, 'name')) {
            $this->setName($if_unknown_break_data->name);
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
     * Get the IfUnknown this break is in
     *
     * @return IfUnknown the IfUnknown this break is in
     */
    public function getIfUnknown() : IfUnknown
    {
        return $this->if_unknown;
    }

    /**
     * Set the start time for this break
     *
     * @param string $start the start time for this break
     *
     * @return IfUnknownBreak the updated break
     */
    public function setStart($start) : IfUnknownBreak
    {
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $start)) {
            throw new Exception('Invalid start time "'.$start.'": must contain a value of the form "HH:mm" using a 24 hour clock');
        }
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
     * @return IfUnknownBreak the updated break
     */
    public function setDate($date) : IfUnknownBreak
    {
        if (!preg_match('/^[0-9]{4}-(0[0-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/', $date)) {
            throw new Exception('Invalid date "'.$date.'": must contain a value of the form "YYYY-MM-DD"');
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d === false || $d->format('Y-m-d') !== $date) {
            throw new Exception('Invalid date "'.$date.'": date does not exist');
        }

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
     * @param string the duration for this break
     *
     * @return IfUnknownBreak the updated break
     */
    public function setDuration($duration) : IfUnknownBreak
    {
        if (!preg_match('/^[0-9]+:[0-5][0-9]$/', $duration)) {
            throw new Exception('Invalid duration "'.$duration.'": must contain a value of the form "HH:mm"');
        }
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
     * @param string the name for this break
     *
     * @return IfUnknownBreak the updated break
     */
    public function setName($name) : IfUnknownBreak
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid break name: must be between 1 and 1000 characters long');
        }
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
