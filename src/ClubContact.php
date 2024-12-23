<?php

namespace VBCompetitions\Competitions;

use Exception;
use stdClass;

/**
 * A single contact for a club
 */
final class ClubContact extends Contact
{
    /** @var Club The club this contact belongs to */
    private Club $club;

    /**
     * Defines a Club Contact
     *
     * @param Club $club The club this contact belongs to
     * @param string $id The unique ID for this contact
     * @param array $roles The roles of this contact within the club
     */
    function __construct(Club $club, string $id, array $roles)
    {
        if ($club->hasContact($id)) {
            throw new Exception('Contact with ID "'.$id.'" already exists in the club');
        }

        $this->club = $club;
        $this->validRoles = ClubContactRole::$valid_roles;
        parent::__construct($id, $roles);
    }

    /**
     * Loads contact data from an object
     *
     * @param object $contact_data The data defining this Contact
     *
     * @return ClubContact The updated Contact instance
     */
    public function loadFromData(object $contact_data) : ClubContact
    {
        parent::loadFromData($contact_data);
        return $this;
    }

    /**
     * Get the club this contact belongs to
     *
     * @return Club The club this contact belongs to
     */
    public function getClub() : Club
    {
        return $this->club;
    }
}
