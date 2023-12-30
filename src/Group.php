<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;

enum MatchType
{
    case CONTINUOUS;
    case SETS;
}

enum GroupType
{
    case LEAGUE;
    case CROSSOVER;
    case KNOCKOUT;
}

/**
 * A group within a stage of the competition
 */
abstract class Group implements JsonSerializable, MatchContainerInterface
{
    /** A unique ID for this group, e.g. 'P1' */
    protected string $id;

    /** Descriptive title for the group, e.g. 'Pool 1' */
    protected ?string $name = null;

    /** Free form string to add notes about this group.  This can be used for arbitrary content that various implementations can use */
    protected ?string $notes = null;

    /** An array of string values as a verbose description of the nature of the group, e.g. 'For the pool stage, teams will play each other once, with the top 2 teams going through to....' */
    protected ?array $description = null;

    /** The type of competition applying to this group, which may dictate how the results are processed. If this has the value 'league' then the property 'league' must be defined */
    protected GroupType $type;

    /** Are the matches played in sets or continuous points. If this has the value 'sets' then the property 'sets' must be defined */
    protected MatchType $match_type;

    /** Configuration defining the nature of a set */
    protected ?SetConfig $sets;

    /** Sets whether drawn matches are allowed */
    protected bool $draws_allowed;

    /** An array of matches in this group (or breaks in play) */
    protected array $matches = [];

    /** The Stage this Group is in */
    protected Stage $stage;

    /** The competition this Group is in */
    protected Competition $competition;

    /** Lookup table for whether the team has matches they're playing in.  The key is the team ID adn the value is a bool */
    private stdClass $team_has_matches_lookup;

    /** Lookup table for whether the team have matches to officiate.  The key is the team ID adn the value is a bool */
    private stdClass $team_has_officiating_lookup;

    /** An array of team references in this group */
    private array $team_references;

    /** An array of team IDs in this group */
    private array $team_ids;

    /** An array of team IDs in this group */
    private array $playing_team_ids;

    /** An array of team IDs in this group */
    private array $officiating_team_ids;

    /** A lookup table of references where the key is the string "{stage ID}:{group ID}" and the value is an object linking to the Stage and Group */
    private array $stg_grp_lookup;

    /** A cached list of the teams that might be in this group via a reference */
    private array $maybe_teams;

    private bool $matches_have_courts = false;
    private bool $matches_have_dates = false;
    private bool $matches_have_durations = false;
    private bool $matches_have_mvps = false;
    private bool $matches_have_managers = false;
    private bool $matches_have_notes = false;
    private bool $matches_have_officials = false;
    private bool $matches_have_starts = false;
    private bool $matches_have_venues = false;
    private bool $matches_have_warmups = false;

    /** an array of the original match data */
    private array $original_matches = [];

    /** A Lookup table from match IDs to that match */
    private object $match_lookup;

    /**
     * Contains the group data of a stage, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this Group is in
     * @param object $group_data The data defining this Group
     */
    function __construct(Stage $stage, object $group_data)
    {
        $this->id = $group_data->id;
        $this->competition = $stage->getCompetition();
        $this->stage = $stage;
        $this->stage->appendGroup($this);
        $this->original_matches = $group_data->matches;
        $this->match_lookup = new stdClass();

        if (property_exists($group_data, 'name')) {
            $this->name = $group_data->name;
        }

        if (property_exists($group_data, 'notes')) {
            $this->notes = $group_data->notes;
        }

        if (property_exists($group_data, 'description')) {
            $this->description = $group_data->description;
        }

        $this->type = match ($group_data->type) {
            'league' => GroupType::LEAGUE,
            'crossover' => GroupType::CROSSOVER,
            'knockout' => GroupType::KNOCKOUT,
        };

        $this->match_type = $group_data->matchType === 'continuous' ? MatchType::CONTINUOUS : MatchType::SETS;

        if (property_exists($group_data, 'sets')) {
            $this->sets = new SetConfig($group_data->sets);
        }

        $this->team_has_matches_lookup = new stdClass();
        $this->team_has_officiating_lookup = new stdClass();
        $this->team_references = [];
        $this->playing_team_ids = [];
        $this->officiating_team_ids = [];
        $this->team_ids = [];
    }

    /**
     * Get the ID for this group
     *
     * @return string the id for this group
     */
    public function getID() : string
    {
        return $this->id;
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
     * @return array|null the description for this group
     */
    public function getDescription() : array|null
    {
        return $this->description;
    }

    /**
     * Get the type for this group
     *
     * @return GroupType the type for this group
     */
    abstract public function getType() : GroupType;

    /**
     * Get the match type for the matches in this group
     *
     * @return MatchType the match type for the matches in this group
     */
    public function getMatchType() : MatchType
    {
        return $this->match_type;
    }

    /**
     * Returns the set config that defines a set for this group
     *
     * @return SetConfig the set config for this group
     */
    public function getSetConfig() : SetConfig
    {
        return $this->sets;
    }

    /**
     * Returns whether draws are allowed in this group
     *
     * @return bool are draws allowed
     */
    public function getDrawsAllowed() : bool
    {
        return $this->draws_allowed;
    }

    /**
     * Get the competition this group is in
     *
     * @return Competition the competition
     */
    public function getCompetition() : Competition
    {
        return $this->competition;
    }

    /**
     * Get the stage this group is in
     *
     * @return Stage the stage this group is in
     */
    public function getStage() : Stage
    {
        return $this->stage;
    }

    /**
     * Return the group data suitable for saving into a competition file
     *
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        $group = new stdClass();
        $group->id = $this->id;

        if ($this->name !== null) {
            $group->name = $this->name;
        }

        if ($this->notes !== null) {
            $group->notes = $this->notes;
        }

        if ($this->description !== null) {
            $group->description = $this->description;
        }

        $group->type = match ($this->type) {
            GroupType::LEAGUE => 'league',
            GroupType::CROSSOVER => 'crossover',
            GroupType::KNOCKOUT => 'knockout',
        };

        if ($this instanceof League) {
            $group->league = $this->league;
        } elseif ($this instanceof Knockout && !is_null($this->knockout)) {
            $group->knockout = $this->knockout;
        }

        $group->matchType = match ($this->match_type) {
            MatchType::CONTINUOUS => 'continuous',
            MatchType::SETS => 'sets'
        };

        if ($this->match_type === MatchType::SETS) {
            $group->sets = $this->sets;
        }

        if ($this instanceof League) {
            $group->drawsAllowed = $this->draws_allowed;
        }

        $group->matches = $this->matches;

        return $group;
    }

    /**
     * Returns a list of matches from this Group, where the list depends on the input parameters and on the type of the MatchContainer
     *
     * @param string $team_id When provided, return the matches where this team is playing, otherwise all matches are returned
     *                        (and subsequent parameters are ignored).  This must be a resolved team ID and not a reference.
     *                        A team ID of CompetitionTeam::UNKNOWN_TEAM_ID is interpreted as null
     * @param int $flags Controls what gets returned
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL_IN_GROUP</code> - Include all matches in the group</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> - Include matches that a team plays in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> - Include matches that a team officiates in</li>
     *                   </ul>
     * @return array<MatchInterface|BreakInterface>
     */
    public function getMatches(string $team_id = null, int $flags = 0) : array
    {
        if (is_null($team_id) ||
            $flags & VBC_MATCH_ALL_IN_GROUP ||
            $team_id === CompetitionTeam::UNKNOWN_TEAM_ID ||
            strncmp($team_id, '{', 1) === 0)
        {
            return $this->matches;
        }

        $matches = [];

        foreach ($this->matches as $match) {
            if ($match instanceof GroupBreak) {
                continue;
            } else if ($flags & VBC_MATCH_PLAYING &&
                ($this->competition->getTeamByID($match->getHomeTeam()->getID())->getID() === $team_id || $this->competition->getTeamByID($match->getAwayTeam()->getID())->getID() === $team_id))
            {
                array_push($matches, $match);
            } else if ($flags & VBC_MATCH_OFFICIATING &&
                !is_null($match->getOfficials()) &&
                property_exists($match->getOfficials(), 'team') &&
                $this->competition->getTeamByID($match->getOfficials()->team)->getID() === $team_id)
            {
                array_push($matches, $match);
            }
        }
        return $matches;
    }

    /**
     * Returns a list of teams from this match container.  If all teams are known then the list is sorted by team name
     *
     * @param int $flags Controls what gets returned (MAYBE overrides KNOWN overrides FIXED_ID)
     *                   <ul>
     *                     <li><code>VBC_TEAMS_FIXED_ID</code> (default) returns teams with a defined team ID (no references)</li>
     *                     <li><code>VBC_TEAMS_KNOWN</code> returns teams that are known (teams not defined by a reference plus references that are resolved)</li>
     *                     <li><code>VBC_TEAMS_MAYBE</code> returns teams that might be in this container (references that could resolve to a team but are not yet defined)</li>
     *                     <li><code>VBC_TEAMS_ALL</code> returns all team IDs including refereeing as defined in the group data, including unresolved references</li>
     *                     <li><code>VBC_TEAMS_PLAYING</code> returns all team IDs for teams that are playing, as defined in the group data, including unresolved references</li>
     *                     <li><code>VBC_TEAMS_OFFICIATING</code> returns all team IDs for teams that are OFFICIATING, as defined in the group data, including unresolved references</li>
     *                   </ul>
     * @return array<string>
     */
    public function getTeamIDs(int $flags = VBC_TEAMS_FIXED_ID) : array
    {
        $team_ids = [];

        if ($flags & VBC_TEAMS_ALL) {
            $team_ids = array_keys($this->team_ids);
        } else if ($flags & VBC_TEAMS_PLAYING) {
            $team_ids = array_keys($this->playing_team_ids);
        } else if ($flags & VBC_TEAMS_OFFICIATING) {
            $team_ids = array_keys($this->officiating_team_ids);
        } else if ($flags & VBC_TEAMS_MAYBE) {
            return $this->getMaybeTeamIDs();
        } else if ($flags & VBC_TEAMS_KNOWN) {
            $team_ids = array_unique(array_filter(array_keys($this->team_ids), function($k) {
                return $this->competition->getTeamByID($k)->getID() !== CompetitionTeam::UNKNOWN_TEAM_ID;
            }));
            usort($team_ids, function($a, $b) {
                return strcmp($this->competition->getTeamByID($a)->getName(), $this->competition->getTeamByID($b)->getName());
            });
        } else if ($flags & VBC_TEAMS_FIXED_ID) {
            $team_ids = array_filter(array_keys($this->team_ids), function($k) {
                return strncmp($k, '{', 1) !== 0;
            });
            usort($team_ids, function($a, $b) {
                return strcmp($this->competition->getTeamByID($a)->getName(), $this->competition->getTeamByID($b)->getName());
            });
        }

        return $team_ids;
    }

    /**
     * Go through the stages:groups referenced by this group and build a list of teams that could reach this group.
     * If the feeding group is complete then the reference can be resolved and the team is "known" and not a "maybe",
     * so we only consider incomplete groups.  We ask that group for all of its known and maybe teams and recurse back
     * to the end of each lookup chain
     *
     * @return array<string> the array of team IDs that may be in this group
     */
    private function getMaybeTeamIDs() : array
    {
        if ($this->isComplete()) {
            // If the group is complete then there are no "maybes"; everything is known so you should call getTeamIDs(VBC_TEAMS_KNOWN)
            return [];
        }

        if (!isset($this->maybe_teams)) {
            $this->maybe_teams = [];
            $this->buildStageGroupLookup();

            foreach ($this->stg_grp_lookup as $stage_and_group) {
                $group = $this->competition->getStageById($stage_and_group->stage)->getGroupById($stage_and_group->group);
                if (!$group->isComplete()) {
                    $this->maybe_teams = array_unique(array_merge($this->maybe_teams, $group->getTeamIDs(VBC_TEAMS_KNOWN), $group->getTeamIDs(VBC_TEAMS_MAYBE)));
                }
            }
        }

        return $this->maybe_teams;
    }

    /**
     * Build the lookup table of references where the key is the string "{stage ID}:{group ID}" and the value is an object linking to
     * the Stage and the Group
     */
    private function buildStageGroupLookup() : void
    {
        // Get all unresolved references {STG:GRP:...}
        if (!isset($this->stg_grp_lookup)) {
            $this->stg_grp_lookup = [];
            foreach ($this->getMatches() as $match) {
                if ($match instanceof GroupMatch) {
                    $home_team_parts = explode(':', substr($match->getHomeTeam()->getID(), 1), 3);
                    if (count($home_team_parts) > 2) {
                        $key = $home_team_parts[0].':'.$home_team_parts[1];
                        if (!key_exists($key, $this->stg_grp_lookup)) {
                            $stage_and_group = new stdClass();
                            $stage_and_group->stage = $home_team_parts[0];
                            $stage_and_group->group = $home_team_parts[1];
                            $this->stg_grp_lookup[$key] = $stage_and_group;
                        }
                    }
                    $away_team_parts = explode(':', substr($match->getAwayTeam()->getID(), 1), 3);
                    if (count($away_team_parts) > 2) {
                        $key = $away_team_parts[0].':'.$away_team_parts[1];
                        if (!key_exists($key, $this->stg_grp_lookup)) {
                            $stage_and_group = new stdClass();
                            $stage_and_group->stage = $away_team_parts[0];
                            $stage_and_group->group = $away_team_parts[1];
                            $this->stg_grp_lookup[$key] = $stage_and_group;
                        }
                    }
                    if (!is_null($match->getOfficials()) && property_exists($match->getOfficials(), 'team')) {
                        $referee_team_parts = explode(':', substr($match->getOfficials()->team, 1), 3);
                        if (count($referee_team_parts) > 2) {
                            $key = $referee_team_parts[0].':'.$referee_team_parts[1];
                            if (!key_exists($key, $this->stg_grp_lookup)) {
                                $stage_and_group = new stdClass();
                                $stage_and_group->stage = $referee_team_parts[0];
                                $stage_and_group->group = $referee_team_parts[1];
                                $this->stg_grp_lookup[$key] = $stage_and_group;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the match with the specified ID
     *
     * @param string $match_id The ID of the match
     *
     * @return GroupMatch The requested match
     */
    public function getMatchById(string $match_id) : GroupMatch
    {
        if (property_exists($this->match_lookup, $match_id)) {
            return $this->match_lookup->{$match_id};
        }
        throw new OutOfBoundsException('Match with ID '.$match_id.' not found', 1);
    }

    /**
     * Process the matches in this group
     */
    protected function processMatches() : void
    {
        foreach ($this->original_matches as $match) {
            if ($match->type === 'match') {
                // There seems to be a bug in opis/json-schema such that the schema rule to require the "complete" field when the "matchType" is "continuous"
                // is thrown off when the array of matches also includes a "break", so manually check here
                if ($this->match_type === MatchType::CONTINUOUS && !property_exists($match, 'complete')) {
                    throw new Exception('Group {'.$this->stage->getID().':'.$this->id.'}, match ID {'.$match->id.'}, missing field "complete"');
                }
                if (property_exists($this->match_lookup, $match->id)) {
                    throw new Exception('Group {'.$this->stage->getID().':'.$this->id.'}: matches with duplicate IDs {'.$match->id.'} not allowed');
                }
                $new_match = new GroupMatch($this, $match);
                array_push($this->matches, $new_match);
                $this->match_lookup->{$match->id} = $new_match;
                if (property_exists($match, 'court')) {
                    $this->matches_have_courts = true;
                }
                if (property_exists($match, 'date')) {
                    $this->matches_have_dates = true;
                }
                if (property_exists($match, 'duration')) {
                    $this->matches_have_durations = true;
                }
                if (property_exists($match, 'mvp')) {
                    $this->matches_have_mvps = true;
                }
                if (property_exists($match, 'manager')) {
                    $this->matches_have_managers = true;
                }
                if (property_exists($match, 'notes')) {
                    $this->matches_have_notes = true;
                }
                if (property_exists($match, 'officials')) {
                    $this->matches_have_officials = true;
                }
                if (property_exists($match, 'start')) {
                    $this->matches_have_starts = true;
                }
                if (property_exists($match, 'venue')) {
                    $this->matches_have_venues = true;
                }
                if (property_exists($match, 'warmup')) {
                    $this->matches_have_warmups = true;
                }

                $this->competition->validateTeamID($match->homeTeam->id, $match->id, 'homeTeam');
                $this->competition->validateTeamID($match->awayTeam->id, $match->id, 'awayTeam');
                if (property_exists($match, 'officials') && property_exists($match->officials, 'team')) {
                    $this->competition->validateTeamID($match->officials->team, $match->id, 'officials > team');
                }
                if (strncmp($match->homeTeam->id, '{', 1) === 0) {
                    array_push($this->team_references, $match->homeTeam->id);
                }
                if (strncmp($match->awayTeam->id, '{', 1) === 0) {
                    array_push($this->team_references, $match->awayTeam->id);
                }

                $this->playing_team_ids[$match->homeTeam->id] = true;
                $this->playing_team_ids[$match->awayTeam->id] = true;
                $this->team_ids[$match->homeTeam->id] = true;
                $this->team_ids[$match->awayTeam->id] = true;
                if (property_exists($match, 'officials') && property_exists($match->officials, 'team')) {
                    $this->team_ids[$match->officials->team] = true;
                    $this->officiating_team_ids[$match->officials->team] = true;
                }
            } elseif ($match->type === 'break') {
                array_push($this->matches, new GroupBreak($this, $match));
            }
        }
    }

    /**
     * Returns whether all matches in the group are complete
     *
     * @return bool whether all matches in the group are complete
     */
    abstract public function isComplete() : bool;

    /**
     * Returns whether the matches in this group have courts
     *
     * @return bool whether the matches in this group have courts
     */
    public function matchesHaveCourts() : bool
    {
        return $this->matches_have_courts;
    }

    /**
     * Returns whether the matches in this group have dates
     *
     * @return bool whether the matches in this group have dates
     */
    public function matchesHaveDates() : bool
    {
        return $this->matches_have_dates;
    }

    /**
     * Returns whether the matches in this group have a duration
     *
     * @return bool whether the matches in this group have a duration
     */
    public function matchesHaveDurations() : bool
    {
        return $this->matches_have_durations;
    }

    /**
     * Returns whether the matches in this group have MVPs
     *
     * @return bool whether the matches in this group have MVPs
     */
    public function matchesHaveMVPs() : bool
    {
        return $this->matches_have_mvps;
    }

    /**
     * Returns whether the matches in this group have court managers
     *
     * @return bool whether the matches in this group have court managers
     */
    public function matchesHaveManagers() : bool
    {
        return $this->matches_have_managers;
    }

    /**
     * Returns whether the matches in this group have notes
     *
     * @return bool whether the matches in this group have notes
     */
    public function matchesHaveNotes() : bool
    {
        return $this->matches_have_notes;
    }

    /**
     * Returns whether the matches in this group have officials
     *
     * @return bool whether the matches in this group have officials
     */
    public function matchesHaveOfficials() : bool
    {
        return $this->matches_have_officials;
    }

    /**
     * Returns whether the matches in this group have start times
     *
     * @return bool whether the matches in this group have start times
     */
    public function matchesHaveStarts() : bool
    {
        return $this->matches_have_starts;
    }

    /**
     * Returns whether the matches in this group have venues
     *
     * @return bool whether the matches in this group have venues
     */
    public function matchesHaveVenues() : bool
    {
        return $this->matches_have_venues;
    }

    /**
     * Returns whether the matches in this group have warmup times
     *
     * @return bool whether the matches in this group have warmup times
     */
    public function matchesHaveWarmups() : bool
    {
        return $this->matches_have_warmups;
    }

    /**
     * Returns whether all of the teams in this group are known yet or not
     *
     * @return bool whether all of the teams in this group are known yet or not
     */
    public function allTeamsKnown() : bool
    {
        //  Look for all referenced groups and check they're complete
        $all_groups_complete = true;
        foreach ($this->team_references as $team_reference) {
            $parts = explode(':', trim($team_reference, '{}'), 4);
            if (!$this->competition->getStageById($parts[0])->getGroupById($parts[1])->isComplete()) {
                $all_groups_complete = false;
            }
        }
        return $all_groups_complete;
    }

    /**
     * Returns whether the specified team is known to have matches in this group
     *
     * @return bool whether the specified team is known to have matches in this group
     */
    public function teamHasMatches(string $team_id) : bool
    {
        if (!property_exists($this->team_has_matches_lookup, $team_id)) {
            $this->team_has_matches_lookup->$team_id = false;
            foreach($this->matches as $match) {
                if ($match instanceof GroupBreak) {
                    continue;
                }
                if ($this->competition->getTeamByID($match->getHomeTeam()->getID())->getID() === $team_id) {
                    $this->team_has_matches_lookup->$team_id = true;
                    break;
                }
                if ($this->competition->getTeamByID($match->getAwayTeam()->getID())->getID() === $team_id) {
                    $this->team_has_matches_lookup->$team_id = true;
                    break;
                }
            }
        }
        return $this->team_has_matches_lookup->$team_id;
    }

    /**
     * Returns whether the specified team is known to have officiating duties in this group
     *
     * @return bool whether the specified team is known to have officiating duties in this group
     */
    public function teamHasOfficiating(string $team_id) : bool
    {
        if (!property_exists($this->team_has_officiating_lookup, $team_id)) {
            $this->team_has_officiating_lookup->$team_id = false;
            foreach($this->matches as $match) {
                if ($match instanceof GroupBreak) {
                    continue;
                }
                if (!is_null($match->getOfficials()) && property_exists($match->getOfficials(), 'team') &&
                    $this->competition->getTeamByID($match->getOfficials()->team)->getID() === $team_id)
                {
                    $this->team_has_officiating_lookup->$team_id = true;
                    break;
                }
            }
        }
        return $this->team_has_officiating_lookup->$team_id;
    }

    /**
     * Returns whether the specified team may have matches or officiating duties in this group.  If the group is complete
     * then this returns false (as there is no doubt; calls to teamHasMatches() or teamHasOfficiating() will give a definite answer)
     *
     * This answers the question of whether there are any team references in the group that could resolve to the given team, but
     * that are currently unresolved; i.e. is there a chain of references that lead back to a group containing the team that may lead
     * to the team playing or officiating in this group
     *
     * As an example, consider a competition containing a pool stage followed by a knockout stage.  When the pool stage is incomplete
     * and the knockout stage is not yet started, it is still be possible for a team in the pool stage to reach the knockout stage.
     * A call to this function will return true if the specified team could qualify for the given group in the knockout stage.  This
     * may be important in determining whether top consider the "if unknown" properties of the competition.  i.e. if a team
     * "may have matches" then you should take the "if unknown" properties into account as that team may have matches in the group;
     * if a team definitely does not have matches then you can ignore the "if unknown" properties
     *
     * Note that this function does <i>not</i> calculate whether it is "mathematically possible" for a team to have matches, only
     * whether a path between groups exists.  e.g. consider the example above where the knockout stage takes the winner of each pool
     * in the pool stage. A team may have played all of it's matches and be last in the pool, meaning it is impossible for that team
     * to finish first in their pool, but this function only considers that there is a route from that team's pool to the knockout
     * stage, and so would return true
     *
     * @return bool whether it is possible for a team with the given ID to have matches in this group
     */
    public function teamMayHaveMatches(string $team_id) : bool
    {
        if ($this->isComplete()) {
            // If the group is complete then there are no "maybes"; everything is known so you should call teamHasMatches()
            return false;
        }

        if ($this->competition->getTeamByID($team_id)->getID() === CompetitionTeam::UNKNOWN_TEAM_ID) {
            return false;
        }

        $this->buildStageGroupLookup();

        // Look up each reference to see if it leads back to this team
        foreach ($this->stg_grp_lookup as $stage_and_group) {
            $group = $this->competition->getStageById($stage_and_group->stage)->getGroupById($stage_and_group->group);
            if ((!$group->isComplete() && $group->teamHasMatches($team_id)) || $group->teamMayHaveMatches($team_id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a list of match dates in this Group.  If a team ID is given then return dates for just that team.
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
        if ($team_id === null || $team_id === CompetitionTeam::UNKNOWN_TEAM_ID || $flags & VBC_MATCH_ALL) {
            foreach ($this->matches as $match) {
                if ($match instanceof GroupMatch) {
                    $match_dates[$match->getDate()] = 1;
                }
            }
        } else {
            foreach ($this->matches as $match) {
                if (!$match instanceof GroupMatch) {
                    continue;
                }

                if ($flags & VBC_MATCH_PLAYING &&
                    (($this->competition->getTeamByID($match->getHomeTeam()->getID())->getID() === $team_id) ||
                     ($this->competition->getTeamByID($match->getAwayTeam()->getID())->getID() === $team_id))) {
                        $match_dates[$match->getDate()] = 1;
                } else if ($flags & VBC_MATCH_OFFICIATING &&
                    !is_null($match->getOfficials()) &&
                    property_exists($match->getOfficials(), 'team') &&
                    $this->competition->getTeamByID($match->getOfficials()->team)->getID() === $team_id) {
                    $match_dates[$match->getDate()] = 1;
                }
            }
        }
        return array_keys($match_dates);
    }

    /**
     * Returns a list of matches on the specified date in this Group.  If a team ID is given then return matches for just that team.
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
        if ($team_id === null || $team_id === CompetitionTeam::UNKNOWN_TEAM_ID || $flags & VBC_MATCH_ALL) {
            foreach ($this->matches as $match) {
                if ($match->getDate() === $date) {
                    array_push($matches, $match);
                }
            }
        } else {
            foreach ($this->matches as $match) {
                if ($match->getDate() === $date) {
                    if (!$match instanceof GroupMatch) {
                        array_push($matches, $match);
                    } else if ($flags & VBC_MATCH_PLAYING &&
                              (($this->competition->getTeamByID($match->getHomeTeam()->getID())->getID() === $team_id) ||
                               ($this->competition->getTeamByID($match->getAwayTeam()->getID())->getID() === $team_id))) {
                        array_push($matches, $match);
                    } else if ($flags & VBC_MATCH_OFFICIATING &&
                               !is_null($match->getOfficials()) &&
                               property_exists($match->getOfficials(), 'team') &&
                               $this->competition->getTeamByID($match->getOfficials()->team)->getID() === $team_id) {
                        array_push($matches, $match);
                    }
                }
            }
        }
        return $matches;
    }
}
