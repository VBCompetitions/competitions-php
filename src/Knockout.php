<?php

namespace VBCompetitions\Competitions;

use stdClass;

/**
 * A group within this stage of the competition.  A knockout expects to generate an order of teams based on team elimination
 */
final class Knockout extends Group
{
    /** Configuration for the knockout matches */
    protected ?object $knockout = null;

    /** Whether this group is complete, i.e. have all matches been played */
    protected bool $is_complete = false;

    /**
     * Contains the group data of a stage, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this Group is in
     * @param object $knockout_data The data defining this Group
     */
    function __construct(Stage $stage, object $knockout_data)
    {
        parent::__construct($stage, $knockout_data);

        $this->draws_allowed = false;

        if (property_exists($knockout_data, 'knockout')) {
            $this->knockout = new stdClass();
            $this->knockout->standing = $knockout_data->knockout->standing;
        }

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
     * Returns whether the Knockout group is complete, i.e. all matches in the group are complete.
     *
     * @return bool whether the Knockout group is complete
     */
    public function isComplete() : bool
    {
        return $this->is_complete;
    }

    /**
     * Return this group type
     * @return GroupType the type for this Group
     */
    public function getType() : GroupType
    {
        return GroupType::KNOCKOUT;
    }

    public function getKnockoutConfig() : object|null
    {
        return $this->knockout;
    }
}
