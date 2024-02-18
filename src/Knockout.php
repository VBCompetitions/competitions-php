<?php

namespace VBCompetitions\Competitions;

use stdClass;

/**
 * A group within this stage of the competition.  A knockout expects to generate an order of teams based on team elimination
 */
final class Knockout extends Group
{
    /**
     * Contains the group data of a stage, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this Group is in
     * @param object $knockout_data The data defining this Group
     */
    function __construct(Stage $stage, string $id, string $match_type)
    {
        parent::__construct($stage, $id, $match_type);
        $this->type = GroupType::KNOCKOUT;
        $this->draws_allowed = false;
    }

    public function getKnockoutConfig() : object|null
    {
        return $this->knockout_config;
    }
}
