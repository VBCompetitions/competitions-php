<?php

namespace VBCompetitions\Competitions;

/**
 * A group within this stage of the competition.  There is no implied league table or team order, just match winners and losers
 */
final class Crossover extends Group
{
    /** Whether this group is complete, i.e. have all matches been played */
    protected bool $is_complete = false;

    /**
     * Contains the group data of a stage, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this Group is in
     * @param object $crossover_data The data defining this Group
     */
    function __construct(Stage $stage, object $crossover_data)
    {
        parent::__construct($stage, $crossover_data);

        $this->draws_allowed = false;

        $this->processMatches();
    }

    /**
     *
     */
    protected function processMatches() : void
    {
        parent::processMatches();

        $completed_matches = 0;
        $matches_in_this_pool = 0;

        foreach($this->getMatches() as $match) {
            if ($match instanceof GroupBreak) {
                continue;
            }

            $matches_in_this_pool++;

            if ($match->isComplete()) {
                $completed_matches++;
            }
        }
        $this->is_complete = $completed_matches === $matches_in_this_pool;
    }

    /**
     * Returns whether the Crossover group is complete, i.e. all matches in the group are complete.
     *
     * @return bool whether the Crossover group is complete
     */
    public function isComplete() : bool
    {
        return $this->is_complete;
    }

    /**
     * Return this group type
     *
     * @return GroupType the type for this Group
     */
    public function getType() : GroupType
    {
        return GroupType::CROSSOVER;
    }
}
