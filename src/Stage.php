<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;

/**
 * A single competition stage
 */
final class Stage implements JsonSerializable, MatchContainerInterface
{
    /** A unique ID for this stage, e.g. 'LG' */
    private string $id;

    /** Descriptive title for the stage, e.g. 'Pools' */
    private ?string $name = null;

    /** Free form string to add notes about this stage.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** Verbose text describing the nature of the stage, e.g. 'The first stage of the competition will consist of separate pools, where....' */
    private ?array $description = null;

    /** The groups within a stage of the competition. There may be only one group (e.g. for a flat league) or multiple in parallel (e.g. pool 1, pool 2) */
    private array $groups = [];

    /** It can be useful to still present something to the user about the later stages of a competition, even if the teams playing in that stage is not yet known. This defines what should be presented in any application handling this competition's data in such cases */
    private ?object $ifUnknown = null;

    /** The Competition this Stage is in */
    private Competition $competition;

    /** @param array<MatchInterface|BreakInterface> all of the matches in all of the group in this stage */
    private array $all_matches;

    /** A Lookup table from group IDs to the group */
    private object $group_lookup;
    private array $team_stg_grp_lookup;

    /**
     * Contains the stage data of a competition, creating any metadata needed
     *
     * @param Competition $competition A link back to the Competition this Stage is in
     * @param object $stage_data The data defining this Stage
     */
    function __construct($competition, $stage_data)
    {
        $this->id = $stage_data->id;
        $this->competition = $competition;
        $this->competition->appendStage($this);

        if (property_exists($stage_data, 'name')) {
            $this->name = $stage_data->name;
        }

        if (property_exists($stage_data, 'notes')) {
            $this->notes = $stage_data->notes;
        }

        if (property_exists($stage_data, 'description')) {
            $this->description = $stage_data->description;
        }

        $this->group_lookup = new stdClass();

        foreach ($stage_data->groups as $group_data) {
            match ($group_data->type) {
                'league' => new League($this, $group_data),
                'crossover' => new Crossover($this, $group_data),
                'knockout' => new Knockout($this, $group_data),
            };
        }

        if (property_exists($stage_data, 'ifUnknown')) {
            $this->ifUnknown = new IfUnknown($this, $stage_data->ifUnknown);
        }

        $this->checkMatches();
    }

    public function appendGroup(Group $new_group) : void
    {
        array_push($this->groups, $new_group);
        if (property_exists($this->group_lookup, $new_group->getID())) {
            throw new Exception('Competition data failed validation. Groups in a Stage with duplicate IDs not allowed: {'.$this->id.':'.$new_group->getID().'}');
        }
        $this->group_lookup->{$new_group->getID()} = $new_group;
    }

    /**
     * Get the ID for this stage
     *
     * @return string the id for this stage
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Get the groups as an array
     *
     * @return array<Group> the array of Groups
     */
    public function getGroups() : array
    {
        return $this->groups;
    }

    /**
     * Get the name for this group
     *
     * @return string|null the name for this group
     */
    public function getName() : string|null
    {
        return $this->name;
    }

    /**
     * Get the notes for this group
     *
     * @return string|null the notes for this group
     */
    public function getNotes() : string|null
    {
        return $this->notes;
    }

    /**
     * Get the description for this group
     *
     * @return array<string>|null the description for this group
     */
    public function getDescription() : array|null
    {
        return $this->description;
    }

    public function getIfUnknown() : ?IfUnknown
    {
        return $this->ifUnknown;
    }

    /**
     * Return the list of stages suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $stage = new stdClass();
        $stage->id = $this->id;

        if ($this->name !== null) {
            $stage->name = $this->name;
        }

        if ($this->notes !== null) {
            $stage->notes = $this->notes;
        }

        if ($this->description !== null) {
            $stage->description = $this->description;
        }

        $stage->groups = $this->groups;

        if ($this->ifUnknown !== null) {
            $stage->ifUnknown = $this->ifUnknown;
        }

        return $stage;
    }

    private function checkMatches() : void
    {
        for ($i = 0 ; $i < count($this->groups) - 1; $i++) {
            $this_groups_team_ids = $this->groups[$i]->getTeamIDs(VBC_TEAMS_PLAYING);
            for ($j = $i+1 ; $j < count($this->groups); $j++) {
                $that_groups_team_ids = $this->groups[$j]->getTeamIDs(VBC_TEAMS_PLAYING);
                $intersecting_ids = array_intersect($this_groups_team_ids, $that_groups_team_ids);
                if (count($intersecting_ids) > 0) {
                    throw new Exception('Groups in the same stage cannot contain the same team. Groups {'.
                        $this->id.':'.$this->groups[$i]->getID().'} and {'.$this->id.':'.$this->groups[$j]->getID().
                        '} both contain the following team IDs: "'.join('", "', $intersecting_ids).'"');
                }
            }
        }
    }

    public function getCompetition() : Competition
    {
        return $this->competition;
    }

    /**
     * Returns a list of matches from this Stage, where the list depends on the input parameters and on the type of the MatchContainer
     *
     *
     * @param string $team_id When provided, return the matches where this team is playing, otherwise all matches are returned
     *                        (and $flags is ignored).  This must be a resolved team ID and not a reference.
     *                        A team ID of CompetitionTeam::UNKNOWN_TEAM_ID is interpreted as null
     * @param int $flags Controls what gets returned
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL_IN_GROUP</code> - If a team plays any matches in a group then include all matches from that group</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> - Include matches that a team plays in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> - Include matches that a team officiates in</li>
     *                   </ul>
     * @return array<MatchInterface>
     */
    public function getMatches(string $team_id = null, int $flags = 0) : array
    {
        /*
            TODO when should we include breaks?
            TODO how do we handle duplicate breaks?
        */

        if (is_null($team_id) || $team_id === CompetitionTeam::UNKNOWN_TEAM_ID || strncmp($team_id, '{', 1) === 0) {
            return $this->getAllMatchesInStage();
        }

        return $this->getMatchesForTeam($team_id, $flags);
    }

    /**
     * Returns a list of teams from this match container
     *
     * @param int $flags Controls what gets returned (MAYBE overrides KNOWN overrides FIXED_ID)
     *                   <ul>
     *                     <li><code>VBC_TEAMS_FIXED_ID</code> (default) returns teams with a defined team ID (no references)</li>
     *                     <li><code>VBC_TEAMS_KNOWN</code> returns teams that are known (references that are resolved)</li>
     *                     <li><code>VBC_TEAMS_MAYBE</code> returns teams that might be in this container (references that may resolve to a team)</li>
     *                     <li><code>VBC_TEAMS_ALL</code> returns all team IDs, including unresolved references</li>
     *                   </ul>
     * @return array<string>
     */
    public function getTeamIDs(int $flags = VBC_TEAMS_FIXED_ID) : array
    {
        $team_ids = [];

        foreach ($this->groups as $group) {
            $group_teams = $group->getTeamIDs($flags);
            $team_ids = array_unique(array_merge($team_ids, $group_teams));
        }

        return $team_ids;
    }

    /**
     * @return array<MatchInterface|BreakInterface>
     */
    private function getAllMatchesInStage() : array
    {
        if (isset($this->all_matches)) {
            return $this->all_matches;
        }

        $this->all_matches = [];
        foreach ($this->groups as $group) {
            $this->all_matches = array_merge($this->all_matches, $group->getMatches());
        }
        usort($this->all_matches, function ($a, $b) {
            // Both GroupBreak and GroupMatch may have "start" and may have "date", or may have neither
            $a_date = $a->getDate() === '' ? '2023-02-12' : $a->getDate();
            $b_date = $a->getDate() === '' ? '2023-02-12' : $b->getDate();
            $a_start = $a->getStart() === '' ? '10:00' : $a->getStart();
            $b_start = $a->getStart() === '' ? '10:00' : $b->getStart();

            return strcmp($a_date.$a_start, $b_date.$b_start);
        });

        return $this->all_matches;
    }

    /**
     * Return the group in this stage with the given ID
     *
     * @param string $group_id the ID of the group
     *
     * @return Group
     */
    public function getGroupById(string $group_id) : Group
    {
        if (!property_exists($this->group_lookup, $group_id)) {
            throw new OutOfBoundsException('Group with ID '.$group_id.' not found in stage with ID '.$this->id);
        }
        return $this->group_lookup->$group_id;
    }

    /**
     * Summary of isComplete
     * @return bool
     */
    public function isComplete() : bool
    {
        foreach ($this->groups as $group) {
            if (!$group->isComplete()) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     */
    private function getMatchesForTeam(string $team_id, int $flags) : array
    {
        $matches = [];

        foreach ($this->groups as $group) {
            if ($group->teamHasMatches($team_id)) {
                // $matches = array_merge($matches, $group->getMatches($team_id, $flags & (VBC_MATCH_ALL_IN_GROUP | VBC_MATCH_PLAYING)));
                $matches = array_merge($matches, $group->getMatches($team_id, $flags));
            } else if ($flags & VBC_MATCH_OFFICIATING && $group->teamHasOfficiating($team_id)) {
                $matches = array_merge($matches, $group->getMatches($team_id, VBC_MATCH_OFFICIATING));
            }
        }

        usort($matches, function ($a, $b) {
            // Both GroupBreak and GroupMatch may have "start" and may have "date", or may have neither
            // so give them some defaults to make them sortable
            $a_date = is_null($a->getDate()) ? '2023-01-01' : $a->getDate();
            $b_date = is_null($a->getDate()) ? '2023-01-01' : $b->getDate();
            $a_start = is_null($a->getStart()) ? '10:00' : $a->getStart();
            $b_start = is_null($a->getStart()) ? '10:00' : $b->getStart();

            return strcmp($a_date.$a_start, $b_date.$b_start);
        });

        return $matches;
    }

    public function matchesHaveCourts() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveCourts()) {
                return true;
            }
        }
        return false;
    }

    public function matchesHaveDates() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveDates()) {
                return true;
            }
        }
        return false;
    }

    public function matchesHaveDurations() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveDurations()) {
                return true;
            }
        }
        return false;
    }

    public function matchesHaveMVPs() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveMvps()) {
                return true;
            }
        }
        return false;
    }

    public function matchesHaveManagers() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveManagers()) {
                return true;
            }
        }
        return false;
    }

    public function matchesHaveNotes() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveNotes()) {
                return true;
            }
        }
        return false;
    }

    public function matchesHaveOfficials() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveOfficials()) {
                return true;
            }
        }
        return false;
    }

    public function matchesHaveStarts() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveStarts()) {
                return true;
            }
        }
        return false;
    }


    public function matchesHaveVenues() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveVenues()) {
                return true;
            }
        }
        return false;
    }

    public function matchesHaveWarmups() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveWarmups()) {
                return true;
            }
        }
        return false;
    }

    public function teamHasMatches(string $team_id) : bool
    {
        foreach ($this->groups as $group) {
            if ($group->teamHasMatches($team_id)) {
                return true;
            }
        }
        return false;
    }

    public function teamHasOfficiating(string $team_id) : bool
    {
        foreach ($this->groups as $group) {
            if ($group->teamHasOfficiating($team_id)) {
                return true;
            }
        }
        return false;
    }

    public function teamMayHaveMatches(string $team_id) : bool
    {
        // Need to rewrite this

        /*
        assert - we only call this after calling "teamHasMatches()".  In other words, this has ?undefined return? if you definitely have matches?
        maybe consider when we know they _don't_ have matches?

        Need to be able to say STG1:GRP1:league:#
         - if STG1:GRP1 complete then we know all league posn and all references to STG1:GRP1:* so result is defined and "hasMatches()" should catch it
         - else then league:# is not defined, so we're down to "does team_id have any matches in STG1:GRP1?"  Or even "could the have any" (remember STG1:GRP1 might also have references to earlier stages)

        */

        //     (*) may have matches in stage:

        if ($this->isComplete()) {
            // If the stage is complete then there are no "maybes"; everything is known so you should call teamHasMatches()
            return false;
        }

        // Get all unresolved references {STG:GRP:...}
        if (!isset($this->team_stg_grp_lookup)) {
            $this->team_stg_grp_lookup = [];
            foreach ($this->groups as $group) {
                foreach ($group->getMatches() as $match) {
                    if ($match instanceof GroupMatch && $this->competition->getTeamByID($team_id)->getID() !== CompetitionTeam::UNKNOWN_TEAM_ID) {
                        $home_team_parts = explode(':', substr($match->getHomeTeam()->getID(), 1), 3);
                        if (count($home_team_parts) > 2) {
                            $key = $home_team_parts[0].':'.$home_team_parts[1];
                            if (!key_exists($key, $this->team_stg_grp_lookup)) {
                                $stage_and_group = new stdClass();
                                $stage_and_group->stage = $home_team_parts[0];
                                $stage_and_group->group = $home_team_parts[1];
                                $this->team_stg_grp_lookup[$key] = $stage_and_group;
                            }
                        }
                        $away_team_parts = explode(':', substr($match->getAwayTeam()->getID(), 1), 3);
                        if (count($away_team_parts) > 2) {
                            $key = $away_team_parts[0].':'.$away_team_parts[1];
                            if (!key_exists($key, $this->team_stg_grp_lookup)) {
                                $stage_and_group = new stdClass();
                                $stage_and_group->stage = $away_team_parts[0];
                                $stage_and_group->group = $away_team_parts[1];
                                $this->team_stg_grp_lookup[$key] = $stage_and_group;
                            }
                        }
                        if (!is_null($match->getOfficials()) && property_exists($match->getOfficials(), 'team')) {
                            $referee_team_parts = explode(':', substr($match->getOfficials()->team, 1), 3);
                            if (count($referee_team_parts) > 2) {
                                $key = $referee_team_parts[0].':'.$referee_team_parts[1];
                                if (!key_exists($key, $this->team_stg_grp_lookup)) {
                                    $stage_and_group = new stdClass();
                                    $stage_and_group->stage = $referee_team_parts[0];
                                    $stage_and_group->group = $referee_team_parts[1];
                                    $this->team_stg_grp_lookup[$key] = $stage_and_group;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Look up each reference to see if it leads back to this team
        foreach ($this->team_stg_grp_lookup as $key => $stage_and_group) {
            $group = $this->competition->getStageById($stage_and_group->stage)->getGroupById($stage_and_group->group);
            if ((!$group->isComplete() && $group->teamHasMatches($team_id)) || $group->teamMayHaveMatches($team_id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a list of match dates in this Stage.  If a team ID is given then return dates for just that team.
     *
     * @param string $team_id This must be a resolved team ID and not a reference
     * @param int $flags Controls what gets returned
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL</code> - Include all matches</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> - Include matches that a team plays in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> - Include matches that a team officiates in</li>
     *                   </ul>
     * @return array<string>
     */
    public function getMatchDates(string $team_id = null, int $flags = VBC_MATCH_PLAYING) : array
    {
        $match_dates = [];
        foreach ($this->groups as $group) {
            $group_match_dates = $group->getMatchDates($team_id, $flags);
            $match_dates = array_merge($match_dates, $group_match_dates);
        }
        sort($match_dates);
        return $match_dates;
    }

    /**
     * Returns a list of matches on the specified date in this Stage.  If a team ID is given then return matches for just that team.
     * The returned list includes breaks when that break has a date value
     *
     * @param string $team_id This must be a resolved team ID and not a reference
     * @param int $flags Controls what gets returned
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL</code> - Include all matches</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> - Include matches that a team plays in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> - Include matches that a team officiates in</li>
     *                   </ul>
     * @return array<MatchInterface>
     */
    public function getMatchesOnDate(string $date, string $team_id = null, int $flags = VBC_MATCH_ALL) : array
    {
        $matches = [];
        foreach ($this->groups as $group) {
            $group_matches = $group->getMatchesOnDate($date, $team_id, $flags);
            $matches = array_merge($matches, $group_matches);
        }
        usort($matches, function ($a, $b) {
            // matches may have "start" and may have "date", or may have neither
            $a_date = $a->getDate() === '' ? '2023-02-12' : $a->getDate();
            $b_date = $a->getDate() === '' ? '2023-02-12' : $b->getDate();
            $a_start = $a->getStart() === '' ? '10:00' : $a->getStart();
            $b_start = $a->getStart() === '' ? '10:00' : $b->getStart();

            return strcmp($a_date.$a_start, $b_date.$b_start);
        });
        return $matches;
    }
}
