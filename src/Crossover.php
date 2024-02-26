<?php

namespace VBCompetitions\Competitions;

/**
 * A group within this stage of the competition.  There is no implied league table or team order, just match winners and losers
 */
final class Crossover extends Group
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
        $this->type = GroupType::CROSSOVER;
        $this->draws_allowed = false;
    }
}
