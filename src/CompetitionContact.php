<?php

namespace VBCompetitions\Competitions;

use Exception;
use stdClass;

/**
 * A single contact for a competition
 */
final class CompetitionContact extends Contact
{
    /** @var Competition The competition this contact belongs to */
    private Competition $competition;

    /**
     * Defines a Competition Contact
     *
     * @param Competition $competition The competition this contact belongs to
     * @param string $id The unique ID for this contact
     * @param array $roles The roles of this contact within the competition
     */
    function __construct(Competition $competition, string $id, array $roles)
    {
        if ($competition->hasContact($id)) {
            throw new Exception('Contact with ID "'.$id.'" already exists in the competition');
        }

        $this->competition = $competition;
        $this->validRoles = CompetitionContactRole::$valid_roles;
        parent::__construct($id, $roles);
    }

    /**
     * Loads contact data from an object
     *
     * @param object $contact_data The data defining this Contact
     *
     * @return CompetitionContact The updated Contact instance
     */
    public function loadFromData(object $contact_data) : CompetitionContact
    {
        parent::loadFromData($contact_data);
        return $this;
    }

    /**
     * Get the competition this contact belongs to
     *
     * @return Competition The competition this contact belongs to
     */
    public function getCompetition() : Competition
    {
        return $this->competition;
    }
}
