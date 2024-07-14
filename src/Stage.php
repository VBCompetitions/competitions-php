<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;

/**
 * A single competition stage.
 */
final class Stage implements JsonSerializable, MatchContainerInterface
{
    /** @var string A unique ID for this stage, e.g., 'LG' */
    private string $id;

    /** @var ?string Descriptive title for the stage, e.g., 'Pools' */
    private ?string $name = null;

    /** @var ?string Free form string to add notes about this stage. */
    private ?string $notes = null;

    /** @var ?array Verbose text describing the nature of the stage. */
    private ?array $description = null;

    /** @var array<Group> The groups within a stage of the competition. */
    private array $groups = [];

    /** @var ?object It can be useful to still present something to the user about the later stages of a competition, even if the teams playing in that stage are not yet known. */
    private ?object $if_unknown = null;

    /** @var Competition The Competition this Stage is in */
    private Competition $competition;

    /** @var array<MatchInterface|BreakInterface> $all_matches All of the matches in all of the group in this stage */
    private array $all_matches;

    /** @var object A Lookup table from group IDs to the group */
    private object $group_lookup;

    /** @var array $team_stg_grp_lookup */
    private array $team_stg_grp_lookup;

    /**
     * Contains the stage data of a competition, creating any metadata needed.
     *
     * @param Competition $competition A link back to the Competition this Stage is in
     * @param string $id The unique ID of this Stage
     * @throws Exception If the stage ID is invalid or already exists in the competition
     */
    function __construct(Competition $competition, string $id)
    {
        $stage_id_length = strlen($id);
        if ($stage_id_length > 100 || $stage_id_length < 1) {
            throw new Exception('Invalid stage ID: must be between 1 and 100 characters long');
        }

        if (!preg_match('/^((?![":{}?=])[\x20-\x7F])+$/', $id)) {
            throw new Exception('Invalid stage ID: must contain only ASCII printable characters excluding " : { } ? =');
        }

        if ($competition->hasStage($id)) {
            throw new Exception('Stage with ID "'.$id.'" already exists in the competition');
        }

        $this->id = $id;
        $this->competition = $competition;
        $this->group_lookup = new stdClass();
    }

    /**
     * Load stage data from an object.
     *
     * @param object $stage_data The stage data
     * @return Stage The updated Stage object
     */
    public function loadFromData(object $stage_data) : Stage
    {
        if (property_exists($stage_data, 'name')) {
            $this->setName($stage_data->name);
        }

        if (property_exists($stage_data, 'notes')) {
            $this->setNotes($stage_data->notes);
        }

        if (property_exists($stage_data, 'description')) {
            $this->setDescription($stage_data->description);
        }

        foreach ($stage_data->groups as $group_data) {
            $group = match ($group_data->type) {
                'crossover' => new Crossover($this, $group_data->id, $group_data->matchType === 'continuous' ? MatchType::CONTINUOUS : MatchType::SETS),
                'knockout' => new Knockout($this, $group_data->id, $group_data->matchType === 'continuous' ? MatchType::CONTINUOUS : MatchType::SETS),
                'league' => new League($this, $group_data->id, $group_data->matchType === 'continuous' ? MatchType::CONTINUOUS : MatchType::SETS, $group_data->drawsAllowed),
                default => throw new Exception('Unknown group type')
            };
            $this->addGroup($group);
            $group->loadFromData($group_data);
        }

        if (property_exists($stage_data, 'ifUnknown')) {
            $this->setIfUnknown(new IfUnknown($this, $stage_data->ifUnknown->description))->loadFromData($stage_data->ifUnknown);
        }

        $this->checkMatches();

        return $this;
    }

    /**
     * Return the list of stages suitable for saving into a competition file.
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

        if ($this->if_unknown !== null) {
            $stage->ifUnknown = $this->if_unknown;
        }

        return $stage;
    }

    /**
     * Get the competition this stage is in.
     *
     * @return Competition The competition this stage is in
     */
    public function getCompetition() : Competition
    {
        return $this->competition;
    }

    /**
     * Get the ID for this stage.
     *
     * @return string The ID for this stage
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Add a group to the stage.
     *
     * @param Group $group The group to add
     * @return Stage The updated Stage object
     * @throws Exception If the group ID already exists in the stage or the group was initialized with a different Stage
     */
    public function addGroup(Group $group) : Stage
    {
        if ($group->getStage() !== $this) {
            throw new Exception('Group was initialised with a different Stage');
        }
        if ($this->hasGroup($group->getID())) {
            throw new Exception('Groups in a Stage with duplicate IDs not allowed: {'.$this->id.':'.$group->getID().'}');
        }
        array_push($this->groups, $group);
        $this->group_lookup->{$group->getID()} = $group;
        return $this;
    }

    /**
     * Get the groups as an array.
     *
     * @return array<Group> The array of Groups
     */
    public function getGroups() : array
    {
        return $this->groups;
    }

    /**
     * Get the name for this group.
     *
     * @return ?string The name for this group
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Set the stage name.
     *
     * @param string $name The new name for the stage
     */
    public function setName(?string $name) : void
    {
        $this->name = $name;
    }

    /**
     * Get the notes for this group.
     *
     * @return ?string The notes for this group
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Set the notes for this stage.
     *
     * @param ?string $notes The notes for this stage
     */
    public function setNotes(?string $notes) : void
    {
        $this->notes = $notes;
    }

    /**
     * Get the description for this stage.
     *
     * @return array<string>|null The description for this stage
     */
    public function getDescription() : ?array
    {
        return $this->description;
    }

    /**
     * Set the description for this stage.
     *
     * @param array<string>|null $description The description for this stage
     */
    public function setDescription(?array $description) : void
    {
        $this->description = $description;
    }

    /**
     * Get the IfUnknown object for this stage.
     *
     * @return ?IfUnknown The IfUnknown object for this stage
     */
    public function getIfUnknown() : ?IfUnknown
    {
        return $this->if_unknown;
    }

    /**
     * Set the IfUnknown object for this stage.
     *
     * @param ?IfUnknown $if_unknown The IfUnknown object for this stage
     */
    public function setIfUnknown(?IfUnknown $if_unknown) : IfUnknown
    {
        $this->if_unknown = $if_unknown;
        return $if_unknown;
    }

    /**
     * Check if matches in groups of the same stage contain duplicate teams.
     *
     * @throws Exception If duplicate teams are found in groups of the same stage
     */
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

    /**
     * Returns a list of matches from this Stage, where the list depends on the input parameters and on the type of the MatchContainer.
     *
     * @param string $id When provided, return the matches where this team is playing, otherwise all matches are returned
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
    public function getMatches(string $id = null, int $flags = 0) : array
    {
        /*
            TODO when should we include breaks?
            TODO how do we handle duplicate breaks?
        */

        if ($id === null || $id === CompetitionTeam::UNKNOWN_TEAM_ID || strncmp($id, '{', 1) === 0) {
            return $this->getAllMatchesInStage();
        }

        return $this->getMatchesForTeam($id, $flags);
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
     * Get all matches in this stage.
     *
     * @return array<MatchInterface|BreakInterface> All matches in this stage
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
            $a_date = $a->getDate() === null ? '2023-02-12' : $a->getDate();
            $b_date = $b->getDate() === null ? '2023-02-12' : $b->getDate();
            $a_start = $a->getStart() === null ? '10:00' : $a->getStart();
            $b_start = $b->getStart() === null ? '10:00' : $b->getStart();

            return strcmp($a_date.$a_start, $b_date.$b_start);
        });

        return $this->all_matches;
    }

    /**
     * Return the group in this stage with the given ID.
     *
     * @param string $id The ID of the group
     * @return Group The group with the given ID
     * @throws OutOfBoundsException If the group with the given ID is not found
     */
    public function getGroup(string $id) : Group
    {
        if (!property_exists($this->group_lookup, $id)) {
            throw new OutOfBoundsException('Group with ID '.$id.' not found in stage with ID '.$this->id);
        }
        return $this->group_lookup->$id;
    }

    /**
     * Check if the stage contains a group with the given ID.
     *
     * @param string $id The ID of the group
     * @return bool True if the stage contains a group with the given ID, false otherwise
     */
    public function hasGroup(string $id) : bool
    {
        return property_exists($this->group_lookup, $id);
    }

    /**
     * Check if all matches in the stage are complete.
     *
     * @return bool True if all matches in the stage are complete, false otherwise
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
     * Get matches for a specific team in this stage.
     *
     * @param string $id The ID of the team
     * @param int $flags Flags to filter the matches
     * @return array An array of matches for the specified team
     */
    private function getMatchesForTeam(string $id, int $flags) : array
    {
        $matches = [];

        foreach ($this->groups as $group) {
            if ($group->teamHasMatches($id)) {
                // $matches = array_merge($matches, $group->getMatches($team_id, $flags & (VBC_MATCH_ALL_IN_GROUP | VBC_MATCH_PLAYING)));
                $matches = array_merge($matches, $group->getMatches($id, $flags));
            } else if ($flags & VBC_MATCH_OFFICIATING && $group->teamHasOfficiating($id)) {
                $matches = array_merge($matches, $group->getMatches($id, VBC_MATCH_OFFICIATING));
            }
        }

        usort($matches, function ($a, $b) {
            // Both GroupBreak and GroupMatch may have "start" and may have "date", or may have neither
            // so give them some defaults to make them sortable
            $a_date = $a->getDate() === null ? '2023-01-01' : $a->getDate();
            $b_date = $b->getDate() === null ? '2023-01-01' : $b->getDate();
            $a_start = $a->getStart() === null ? '10:00' : $a->getStart();
            $b_start = $b->getStart() === null ? '10:00' : $b->getStart();

            return strcmp($a_date.$a_start, $b_date.$b_start);
        });

        return $matches;
    }

    /**
     * Check if matches in any group within this stage have courts assigned.
     *
     * @return bool True if matches have courts assigned, false otherwise
     */
    public function matchesHaveCourts() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveCourts()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have dates assigned.
     *
     * @return bool True if matches have dates assigned, false otherwise
     */
    public function matchesHaveDates() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveDates()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have durations assigned.
     *
     * @return bool True if matches have durations assigned, false otherwise
     */
    public function matchesHaveDurations() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveDurations()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have MVPs assigned.
     *
     * @return bool True if matches have MVPs assigned, false otherwise
     */
    public function matchesHaveMVPs() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveMVPs()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have managers assigned.
     *
     * @return bool True if matches have managers assigned, false otherwise
     */
    public function matchesHaveManagers() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveManagers()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have notes assigned.
     *
     * @return bool True if matches have notes assigned, false otherwise
     */
    public function matchesHaveNotes() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveNotes()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have officials assigned.
     *
     * @return bool True if matches have officials assigned, false otherwise
     */
    public function matchesHaveOfficials() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveOfficials()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have start times assigned.
     *
     * @return bool True if matches have start times assigned, false otherwise
     */
    public function matchesHaveStarts() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveStarts()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have venues assigned.
     *
     * @return bool True if matches have venues assigned, false otherwise
     */
    public function matchesHaveVenues() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveVenues()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if matches in any group within this stage have warmup information assigned.
     *
     * @return bool True if matches have warmup information assigned, false otherwise
     */
    public function matchesHaveWarmups() : bool
    {
        foreach ($this->groups as $group) {
            if ($group->matchesHaveWarmups()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a team has matches scheduled in any group within this stage.
     *
     * @param string $id The ID of the team
     * @return bool True if the team has matches scheduled, false otherwise
     */
    public function teamHasMatches(string $id) : bool
    {
        foreach ($this->groups as $group) {
            if ($group->teamHasMatches($id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a team has officiating duties assigned in any group within this stage.
     *
     * @param string $id The ID of the team
     * @return bool True if the team has officiating duties assigned, false otherwise
     */
    public function teamHasOfficiating(string $id) : bool
    {
        foreach ($this->groups as $group) {
            if ($group->teamHasOfficiating($id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a team may have matches scheduled in any group within this stage. Note that this has undefined behaviour if a team definitely has matches,
     * i.e. if you want to know if a team definitely has matches in this stage then call teamHasMatches(), but if you want to know if there are any
     * references that might point to this team (e.g. a reference to a team in a league position in an incomplete league) then call this function.  This
     * is essentially so we know whether we still need to consider displaying a stage to the competitors as they <i>might</i> still be able to reach that stage.
     *
     * @param string $id The ID of the team to check
     * @return bool True if the team may have matches scheduled, false otherwise
     */
    public function teamMayHaveMatches(string $id) : bool
    {
        /* TODO Do we need to rewrite this?

        assert - we only call this after calling "teamHasMatches()".  In other words, this has ?undefined return? if you definitely have matches?
        maybe consider when we know they _don't_ have matches?

        Need to be able to say STG1:GRP1:league:#
         - if STG1:GRP1 complete then we know all league positions and all references to STG1:GRP1:* so result is defined and "teamHasMatches()" should catch it
         - else then league:# is not defined, so we're down to "does team_id have any matches in STG1:GRP1?"  Or even "could the have any" (remember STG1:GRP1 might also have references to earlier stages)
        */

        if ($this->isComplete()) {
            // If the stage is complete then there are no "maybes"; everything is known so you should call teamHasMatches()
            return false;
        }

        // Get all unresolved references {STG:GRP:...}
        if (!isset($this->team_stg_grp_lookup)) {
            $this->team_stg_grp_lookup = [];
            foreach ($this->groups as $group) {
                foreach ($group->getMatches() as $match) {
                    if ($match instanceof GroupMatch && $this->competition->getTeam($id)->getID() !== CompetitionTeam::UNKNOWN_TEAM_ID) {
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
                        if ($match->getOfficials() !== null && $match->getOfficials()->isTeam()) {
                            $referee_team_parts = explode(':', substr($match->getOfficials()->getTeamID(), 1), 3);
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
            $group = $this->competition->getStage($stage_and_group->stage)->getGroup($stage_and_group->group);
            if ((!$group->isComplete() && $group->teamHasMatches($id)) || $group->teamMayHaveMatches($id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a list of match dates in this Stage.  If a team ID is given then return dates for just that team.
     *
     * @param string $id This must be a resolved team ID and not a reference
     * @param int $flags Controls what gets returned
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL</code> - Include all matches</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> - Include matches that a team plays in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> - Include matches that a team officiates in</li>
     *                   </ul>
     * @return array<string>
     */
    public function getMatchDates(string $id = null, int $flags = VBC_MATCH_PLAYING) : array
    {
        $match_dates = [];
        foreach ($this->groups as $group) {
            $group_match_dates = $group->getMatchDates($id, $flags);
            $match_dates = array_merge($match_dates, $group_match_dates);
        }
        sort($match_dates);
        return $match_dates;
    }

    /**
     * Returns a list of matches on the specified date in this Stage.  If a team ID is given then return matches for just that team.
     * The returned list includes breaks when that break has a date value
     *
     * @param string $date The requested date in the format YYYY-MM-DD
     * @param string $id This must be a resolved team ID and not a reference
     * @param int $flags Controls what gets returned
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL</code> - Include all matches</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> - Include matches that a team plays in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> - Include matches that a team officiates in</li>
     *                   </ul>
     * @return array<MatchInterface>
     */
    public function getMatchesOnDate(string $date, string $id = null, int $flags = VBC_MATCH_ALL) : array
    {
        $matches = [];
        foreach ($this->groups as $group) {
            $group_matches = $group->getMatchesOnDate($date, $id, $flags);
            $matches = array_merge($matches, $group_matches);
        }
        usort($matches, function ($a, $b) {
            // matches may have "start" or may not
            $a_start = $a->getStart() === null ? '10:00' : $a->getStart();
            $b_start = $b->getStart() === null ? '10:00' : $b->getStart();

            return strcmp($a_start, $b_start);
        });
        return $matches;
    }
}
