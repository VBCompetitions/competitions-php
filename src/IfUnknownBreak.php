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

    /** @var string|null The start time for the break */
    private ?string $start = null;

    /** @var string|null The date of the break */
    private ?string $date = null;

    /** @var string|null The duration of the break */
    private ?string $duration = null;

    /** @var string|null The name for the break, e.g. 'Lunch break' */
    private ?string $name = null;

    /** @var IfUnknown The IfUnknown instance this break is associated with */
    private IfUnknown $if_unknown;

    /**
     * Initializes the IfUnknownBreak instance
     *
     * @param IfUnknown $if_unknown The IfUnknown instance this break is in
     */
    function __construct($if_unknown)
    {
        $this->if_unknown = $if_unknown;
    }

    /**
     * Loads data from an object into the IfUnknownBreak instance
     *
     * @param object $if_unknown_break_data The data defining this break
     * @return IfUnknownBreak The updated IfUnknownBreak instance
     */
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
     * Returns the match break data suitable for saving into a competition file
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
     * Retrieves the IfUnknown instance this break is associated with
     *
     * @return IfUnknown The IfUnknown instance this break is associated with
     */
    public function getIfUnknown() : IfUnknown
    {
        return $this->if_unknown;
    }

    /**
     * Sets the start time for this break
     *
     * @param string $start The start time for this break
     * @return IfUnknownBreak The updated IfUnknownBreak instance
     * @throws Exception When an invalid start time format is provided
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
     * Retrieves the start time for this break
     *
     * @return string|null The start time for this break
     */
    public function getStart() : ?string
    {
        return $this->start;
    }

    /**
     * Sets the date for this break
     *
     * @param string $date The date for this break
     * @return IfUnknownBreak The updated IfUnknownBreak instance
     * @throws Exception When an invalid date format is provided or the date does not exist
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
     * Retrieves the date for this break
     *
     * @return string|null The date for this break
     */
    public function getDate() : ?string
    {
        return $this->date;
    }

    /**
     * Sets the duration for this break
     *
     * @param string $duration The duration for this break
     * @return IfUnknownBreak The updated IfUnknownBreak instance
     * @throws Exception When an invalid duration format is provided
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
     * Retrieves the duration for this break
     *
     * @return string|null The duration for this break
     */
    public function getDuration() : ?string
    {
        return $this->duration;
    }

    /**
     * Sets the name for this break
     *
     * @param string $name The name for this break
     * @return IfUnknownBreak The updated IfUnknownBreak instance
     * @throws Exception When the provided name is invalid
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
     * Retrieves the name for this break
     *
     * @return string|null The name for this break
     */
    public function getName() : ?string
    {
        return $this->name;
    }
}
