<?php

namespace VBCompetitions\Competitions;

use Exception;
use stdClass;

/**
 * A single contact for a team
 */
final class TeamContact extends Contact
{
    /** @var CompetitionTeam The team this contact belongs to */
    private CompetitionTeam $team;

    /**
     * Defines a Team Contact
     *
     * @param CompetitionTeam $team The team this contact belongs to
     * @param string $id The unique ID for this contact
     * @param array $roles The roles of this contact within the team
     */
    function __construct(CompetitionTeam $team, string $id, array $roles)
    {
        if ($team->hasContact($id)) {
            throw new Exception('Contact with ID "'.$id.'" already exists in the team');
        }

        $this->team = $team;
        $this->validRoles = TeamContactRole::$valid_roles;
        parent::__construct($id, $roles);
    }

    /**
     * Loads contact data from an object
     *
     * @param object $contact_data The data defining this Contact
     *
     * @return TeamContact The updated Contact instance
     */
    public function loadFromData(object $contact_data) : Contact
    {
        parent::loadFromData($contact_data);
        return $this;
    }

    /**
     * Get the team this contact belongs to
     *
     * @return CompetitionTeam The team this contact belongs to
     */
    public function getTeam() : CompetitionTeam
    {
        return $this->team;
    }
}
