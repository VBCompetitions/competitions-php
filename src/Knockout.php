<?php

namespace VBCompetitions\Competitions;

/**
 * A group within this stage of the competition.  A knockout expects to generate an order of teams based on team elimination
 */
final class Knockout extends Group
{
    /**
     * Contains the group data of a stage, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this Group is in
     * @param string $id The unique ID of this Group
     * @param MatchType $match_type Whether matches are continuous or played to sets
     */
    function __construct(Stage $stage, string $id, MatchType $match_type)
    {
        parent::__construct($stage, $id, $match_type);
        $this->type = GroupType::KNOCKOUT;
        $this->draws_allowed = false;
    }

    /**
     * Get the knockout config for this group
     *
     * @return ?KnockoutConfig the knockout config for this group
     */
    public function getKnockoutConfig() : ?KnockoutConfig
    {
        return $this->knockout_config;
    }
}
