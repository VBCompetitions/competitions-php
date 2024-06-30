<?php

namespace VBCompetitions\Competitions;

use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;

/**
 * A group within a stage of the competition
 */
abstract class Group implements JsonSerializable, MatchContainerInterface
{
    /** @var string A unique ID for this group, e.g. 'P1' */
    protected string $id;

    /** @var ?string Descriptive title for the group, e.g. 'Pool 1' */
    protected ?string $name = null;

    /** @var ?string Free form string to add notes about this group. */
    protected ?string $notes = null;

    /** @var ?array An array of string values as a verbose description of the nature of the group */
    protected ?array $description = null;

    /** @var GroupType The type of competition applying to this group */
    protected GroupType $type;

    /** @var ?KnockoutConfig Configuration for the knockout matches */
    protected ?KnockoutConfig $knockout_config = null;

    /** @var ?LeagueConfig Configuration for the league */
    protected ?LeagueConfig $league_config;

    /** @var MatchType Are the matches played in sets or continuous points */
    protected MatchType $match_type;

    /** @var ?SetConfig Configuration defining the nature of a set */
    protected ?SetConfig $sets;

    /** @var bool Sets whether drawn matches are allowed */
    protected bool $draws_allowed;

    /** @var array An array of matches in this group (or breaks in play) */
    protected array $matches = [];

    /** @var bool Whether this group is complete, i.e. have all matches been played */
    protected bool $is_complete = true;

    //** @var bool A latch on whether we've calculated the latest known completeness of the group */
    protected bool $is_complete_known = false;

    /** @var Stage The Stage this Group is in */
    protected Stage $stage;

    /** @var Competition The competition this Group is in */
    protected Competition $competition;

    /** @var stdClass Lookup table for whether the team has matches they're playing in */
    private stdClass $team_has_matches_lookup;

    /** @var stdClass Lookup table for whether the team have matches to officiate */
    private stdClass $team_has_officiating_lookup;

    /** @var array An array of team references in this group */
    private array $team_references;

    /** @var array An array of team IDs in this group */
    private array $team_ids;

    /** @var array An array of team IDs in this group */
    private array $playing_team_ids;

    /** @var array An array of team IDs in this group */
    private array $officiating_team_ids;

    /** @var array A lookup table of references where the key is the string "{stage ID}:{group ID}" */
    private array $stg_grp_lookup;

    /** @var array A cached list of the teams that might be in this group via a reference */
    private array $maybe_teams;

    /** @var bool */
    private bool $matches_have_courts = false;

    /** @var bool */
    private bool $matches_have_dates = false;

    /** @var bool */
    private bool $matches_have_durations = false;

    /** @var bool */
    private bool $matches_have_mvps = false;

    /** @var bool */
    private bool $matches_have_managers = false;

    /** @var bool */
    private bool $matches_have_notes = false;

    /** @var bool */
    private bool $matches_have_officials = false;

    /** @var bool */
    private bool $matches_have_starts = false;

    /** @var bool */
    private bool $matches_have_venues = false;

    /** @var bool */
    private bool $matches_have_warmups = false;

    /** @var object A Lookup table from match IDs to that match */
    private object $match_lookup;

    /** @var bool */
    protected bool $matches_processed = false;

    /**
     * Contains the group data of a stage, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this Group is in
     * @param string $id The unique ID of this Group
     * @param MatchType $match_type Whether matches are continuous or played to sets
     */
    function __construct(Stage $stage, string $id, MatchType $match_type)
    {
        $this->id = $id;
        $this->stage = $stage;
        $this->competition = $stage->getCompetition();
        $this->match_type = $match_type;
        $this->match_lookup = new stdClass();
        $this->team_has_matches_lookup = new stdClass();
        $this->team_has_officiating_lookup = new stdClass();
        $this->team_references = [];
        $this->playing_team_ids = [];
        $this->officiating_team_ids = [];
        $this->team_ids = [];
    }

    /**
     * Load group data from object
     *
     * @param object $group_data The data defining this Group
     * @return Group The loaded group instance
     */
    public function loadFromData(object $group_data) : Group
    {
        if (property_exists($group_data, 'name')) {
            $this->setName($group_data->name);
        }

        if (property_exists($group_data, 'notes')) {
            $this->setNotes($group_data->notes);
        }

        if (property_exists($group_data, 'description')) {
            $this->setDescription($group_data->description);
        }

        if (property_exists($group_data, 'sets')) {
            $set_config = new SetConfig($this);
            $this->setSetConfig($set_config);
            $set_config->loadFromData($group_data->sets);
        }

        if (property_exists($group_data, 'knockout')) {
            $knockout_config = new KnockoutConfig($this);
            $this->setKnockoutConfig($knockout_config);
            $knockout_config->loadFromData($group_data->knockout);
        }

        if (property_exists($group_data, 'league')) {
            $league_config = new LeagueConfig($this);
            $this->setLeagueConfig($league_config);
            $league_config->loadFromData($group_data->league);
        }

        foreach ($group_data->matches as $match_data) {
            if ($match_data->type === 'match') {
                $this->addMatch((new GroupMatch($this, $match_data->id))->loadFromData($match_data));
            } else if ($match_data->type === 'break') {
                $this->addBreak((new GroupBreak($this))->loadFromData($match_data));
            }
        }

        return $this;
    }

    /**
     * Return the group data suitable for saving into a competition file
     *
     * @return mixed The serialized group data
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
            $group->league = $this->league_config;
        } elseif ($this instanceof Knockout && $this->knockout_config !== null) {
            $group->knockout = $this->knockout_config;
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
     * Get the stage this group is in
     *
     * @return Stage The stage this group is in
     */
    public function getStage() : Stage
    {
        return $this->stage;
    }

    /**
     * Get the ID for this group
     *
     * @return string The id for this group
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Get the name for this group
     *
     * @return ?string The name for this group
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Set the group Name
     *
     * @param string $name The new name for the group
     * @return Group The Group instance
     */
    public function setName(string $name) : Group
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the notes for this group
     *
     * @return ?string The notes for this group
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Set the notes for this group
     *
     * @param ?string $notes The notes for this group
     * @return Group The Group instance
     */
    public function setNotes(?string $notes) : Group
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Get the description for this group
     *
     * @return ?array the description for this group
     */
    public function getDescription() : ?array
    {
        return $this->description;
    }

    /**
     * Set the description for this group
     *
     * @param null|array<string> $description The description for this group
     * @return Group The Group instance
     */
    public function setDescription($description) : Group
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the knockout configuration for this group
     *
     * @param KnockoutConfig $knockout_config The knockout configuration
     * @return Group The Group instance
     */
    public function setKnockoutConfig(KnockoutConfig $knockout_config) : Group
    {
        $this->knockout_config = $knockout_config;
        return $this;
    }

    /**
     * Set the league configuration for this group
     *
     * @param LeagueConfig $league_config The league configuration
     * @return Group The Group instance
     */
    public function setLeagueConfig(LeagueConfig $league_config) : Group
    {
        $this->league_config = $league_config;
        return $this;
    }

   /**
     * Get the type for this group
     *
     * @return GroupType The type for this group
     */
    public function getType() : GroupType
    {
        return $this->type;
    }

    /**
     * Get the match type for the matches in this group
     *
     * @return MatchType The match type for the matches in this group
     */
    public function getMatchType() : MatchType
    {
        return $this->match_type;
    }

    /**
     * Returns the set configuration that defines a set for this group
     *
     * @return SetConfig The set configuration for this group
     */
    public function getSetConfig() : SetConfig
    {
        return $this->sets;
    }

    /**
     * Set the set configuration that defines a set for this group
     *
     * @param SetConfig $sets The set configuration for this group
     * @return Group The Group instance
     */
    public function setSetConfig(SetConfig $sets) : Group
    {
        $this->sets = $sets;
        return $this;
    }

    /**
     * Returns whether draws are allowed in this group
     *
     * @return bool Are draws allowed
     */
    public function getDrawsAllowed() : bool
    {
        return $this->draws_allowed;
    }

    /**
     * Sets whether draws are allowed in this group
     *
     * @param bool $draws_allowed Are draws allowed
     * @return Group The Group instance
     */
    public function setDrawsAllowed(bool $draws_allowed) : Group
    {
        // TODO - check if there are any draws already when this is set to false, and throw
        $this->draws_allowed = $draws_allowed;
        return $this;
    }

    /**
     * Get the competition this group is in
     *
     * @return Competition The competition
     */
    public function getCompetition() : Competition
    {
        return $this->competition;
    }

    /**
     * Add a match to this group
     *
     * @param GroupMatch $match The match to add
     * @return Group The Group instance
     */
    public function addMatch(GroupMatch $match) : Group
    {
        $this->matches_processed = false;

        $this->competition->validateTeamID($match->getHomeTeam()->getID(), $match->getID(), 'homeTeam');
        $this->competition->validateTeamID($match->getAwayTeam()->getID(), $match->getID(), 'awayTeam');

        array_push($this->matches, $match);
        $this->match_lookup->{$match->getID()} = $match;

        if ($match->hasCourt()) {
            $this->matches_have_courts = true;
        }
        if ($match->hasDate()) {
            $this->matches_have_dates = true;
        }
        if ($match->hasDuration()) {
            $this->matches_have_durations = true;
        }
        if ($match->hasMVP()) {
            $this->matches_have_mvps = true;
        }
        if ($match->hasManager()) {
            $this->matches_have_managers = true;
        }
        if ($match->hasNotes()) {
            $this->matches_have_notes = true;
        }
        if ($match->hasOfficials()) {
            $this->matches_have_officials = true;
        }
        if ($match->hasStart()) {
            $this->matches_have_starts = true;
        }
        if ($match->hasVenue()) {
            $this->matches_have_venues = true;
        }
        if ($match->hasWarmup()) {
            $this->matches_have_warmups = true;
        }

        if (strncmp($match->getHomeTeam()->getID(), '{', 1) === 0) {
            array_push($this->team_references, $match->getHomeTeam()->getID());
        }
        if (strncmp($match->getAwayTeam()->getID(), '{', 1) === 0) {
            array_push($this->team_references, $match->getAwayTeam()->getID());
        }

        $this->playing_team_ids[$match->getHomeTeam()->getID()] = true;
        $this->playing_team_ids[$match->getAwayTeam()->getID()] = true;
        $this->team_ids[$match->getHomeTeam()->getID()] = true;
        $this->team_ids[$match->getAwayTeam()->getID()] = true;
        if ($match->hasOfficials() && $match->getOfficials()->isTeam()) {
            $this->team_ids[$match->getOfficials()->getTeamID()] = true;
            $this->officiating_team_ids[$match->getOfficials()->getTeamID()] = true;
        }

        $this->is_complete_known = false;

        return $this;
    }

    /**
     * Add a break to this group
     *
     * @param GroupBreak $break The break to add
     * @return Group The Group instance
     */
    public function addBreak(GroupBreak $break) : Group
    {
        array_push($this->matches, $break);

        $this->is_complete_known = false;

        return $this;
    }

    /**
     * Returns a list of matches from this Group, where the list depends on the input parameters and on the type of the MatchContainer
     *
     * @param string $id When provided, return the matches where this team is playing, otherwise all matches are returned
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
    public function getMatches(string $id = null, int $flags = 0) : array
    {
        if ($id === null ||
            $flags & VBC_MATCH_ALL_IN_GROUP ||
            $id === CompetitionTeam::UNKNOWN_TEAM_ID ||
            strncmp($id, '{', 1) === 0)
        {
            return $this->matches;
        }

        $matches = [];

        foreach ($this->matches as $match) {
            if ($match instanceof GroupBreak) {
                continue;
            } else if ($flags & VBC_MATCH_PLAYING &&
                ($this->competition->getTeam($match->getHomeTeam()->getID())->getID() === $id || $this->competition->getTeam($match->getAwayTeam()->getID())->getID() === $id))
            {
                array_push($matches, $match);
            } else if ($flags & VBC_MATCH_OFFICIATING &&
                $match->getOfficials() !== null &&
                $match->getOfficials()->isTeam() &&
                $this->competition->getTeam($match->getOfficials()->getTeamID())->getID() === $id)
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
            $team_ids = array_values(array_unique(array_filter(array_keys($this->team_ids), fn($k): bool => $this->competition->getTeam($k)->getID() !== CompetitionTeam::UNKNOWN_TEAM_ID)));
            usort($team_ids, function($a, $b) {
                return strcmp($this->competition->getTeam($a)->getName(), $this->competition->getTeam($b)->getName());
            });
        } else if ($flags & VBC_TEAMS_FIXED_ID) {
            $team_ids = array_values(array_filter(array_keys($this->team_ids), fn($k): bool => strncmp($k, '{', 1) !== 0));
            usort($team_ids, function($a, $b) {
                return strcmp($this->competition->getTeam($a)->getName(), $this->competition->getTeam($b)->getName());
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
                $group = $this->competition->getStage($stage_and_group->stage)->getGroup($stage_and_group->group);
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
                    if ($match->getOfficials() !== null && $match->getOfficials()->isTeam()) {
                        $referee_team_parts = explode(':', substr($match->getOfficials()->getTeamID(), 1), 3);
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
     * @param string $id The ID of the match
     *
     * @return GroupMatch The requested match
     */
    public function getMatch(string $id) : GroupMatch
    {
        if (property_exists($this->match_lookup, $id)) {
            return $this->match_lookup->{$id};
        }
        throw new OutOfBoundsException('Match with ID '.$id.' not found', 1);
    }

    /**
     * Checks if a match with the given ID exists in the group.
     *
     * @param string $id The ID of the match to check
     * @return bool True if a match with the given ID exists, false otherwise
     */
    public function hasMatch(string $id) : bool
    {
        return property_exists($this->match_lookup, $id);
    }

    /**
     * Process the matches in this group
     */
    public function processMatches() : void
    {
        $this->matches_processed = true;
    }

    /**
     * Get the team by ID based on the type of entity.
     *
     * @param string $type The type part of the team reference ('MATCH-ID' or 'league')
     * @param string $entity The entity (e.g., 'winner' or 'loser')
     * @return CompetitionTeam The CompetitionTeam instance
     * @throws Exception If the entity is invalid
     */
    public function getTeam(string $type, string $entity) : CompetitionTeam
    {
        if ($type === 'league') {
            throw new Exception('Invalid type "league" in team reference.  Cannot get league position from a non-league group');
        }

        $match = $this->getMatch($type);

        return match ($entity) {
            'winner' => $this->competition->getTeam($match->getWinnerTeamID()),
            'loser' => $this->competition->getTeam($match->getLoserTeamID()),
            default => throw new Exception('Invalid entity "'.$entity.'" in team reference'),
        };
    }

    /**
     * Checks if the matches in this group have been processed.
     *
     * @return bool True if the matches in this group have been processed, false otherwise
     */
    public function isProcessed() : bool
    {
        return $this->matches_processed;
    }

    /**
     * Checks if the group is complete, i.e., all matches in the group are complete.
     *
     * @return bool True if the group is complete, false otherwise
     */
    public function isComplete() : bool
    {
        if (!$this->is_complete_known) {
            $completed_matches = 0;
            $matches_in_this_pool = 0;

            foreach ($this->getMatches() as $match) {
                if ($match instanceof GroupBreak) {
                    continue;
                }

                $matches_in_this_pool++;

                if ($match->isComplete()) {
                    $completed_matches++;
                }
            }
            $this->is_complete = $completed_matches === $matches_in_this_pool;
            $this->is_complete_known = true;
        }

        return $this->is_complete;
    }

    /**
     * Checks if the matches in this group have courts.
     *
     * @return bool True if the matches in this group have courts, false otherwise
     */
    public function matchesHaveCourts() : bool
    {
        return $this->matches_have_courts;
    }

    /**
     * Checks if the matches in this group have dates.
     *
     * @return bool True if the matches in this group have dates, false otherwise
     */
    public function matchesHaveDates() : bool
    {
        return $this->matches_have_dates;
    }

    /**
     * Checks if the matches in this group have durations.
     *
     * @return bool True if the matches in this group have durations, false otherwise
     */
    public function matchesHaveDurations() : bool
    {
        return $this->matches_have_durations;
    }

    /**
     * Checks if the matches in this group have MVPs.
     *
     * @return bool True if the matches in this group have MVPs, false otherwise
     */
    public function matchesHaveMVPs() : bool
    {
        return $this->matches_have_mvps;
    }

    /**
     * Checks if the matches in this group have court managers.
     *
     * @return bool True if the matches in this group have court managers, false otherwise
     */
    public function matchesHaveManagers() : bool
    {
        return $this->matches_have_managers;
    }

    /**
     * Checks if the matches in this group have notes.
     *
     * @return bool True if the matches in this group have notes, false otherwise
     */
    public function matchesHaveNotes() : bool
    {
        return $this->matches_have_notes;
    }

    /**
     * Checks if the matches in this group have officials.
     *
     * @return bool True if the matches in this group have officials, false otherwise
     */
    public function matchesHaveOfficials() : bool
    {
        return $this->matches_have_officials;
    }

    /**
     * Checks if the matches in this group have start times.
     *
     * @return bool True if the matches in this group have start times, false otherwise
     */
    public function matchesHaveStarts() : bool
    {
        return $this->matches_have_starts;
    }

    /**
     * Checks if the matches in this group have venues.
     *
     * @return bool True if the matches in this group have venues, false otherwise
     */
    public function matchesHaveVenues() : bool
    {
        return $this->matches_have_venues;
    }

    /**
     * Checks if the matches in this group have warmup times.
     *
     * @return bool True if the matches in this group have warmup times, false otherwise
     */
    public function matchesHaveWarmups() : bool
    {
        return $this->matches_have_warmups;
    }

    /**
     * Returns whether all of the teams in this group are known yet or not.
     *
     * @return bool Whether all of the teams in this group are known yet or not
     */
    public function allTeamsKnown() : bool
    {
        //  Look for all referenced groups and check they're complete
        $all_groups_complete = true;
        foreach ($this->team_references as $team_reference) {
            $parts = explode(':', trim($team_reference, '{}'), 4);
            if (!$this->competition->getStage($parts[0])->getGroup($parts[1])->isComplete()) {
                $all_groups_complete = false;
            }
        }
        return $all_groups_complete;
    }

    /**
     * Returns whether the specified team is known to have matches in this group.
     *
     * @param string $id The ID of the team
     * @return bool Whether the specified team is known to have matches in this group
     */
    public function teamHasMatches(string $id) : bool
    {
        if (!property_exists($this->team_has_matches_lookup, $id)) {
            $this->team_has_matches_lookup->$id = false;
            foreach ($this->matches as $match) {
                if ($match instanceof GroupBreak) {
                    continue;
                }
                if ($this->competition->getTeam($match->getHomeTeam()->getID())->getID() === $id) {
                    $this->team_has_matches_lookup->$id = true;
                    break;
                }
                if ($this->competition->getTeam($match->getAwayTeam()->getID())->getID() === $id) {
                    $this->team_has_matches_lookup->$id = true;
                    break;
                }
            }
        }
        return $this->team_has_matches_lookup->$id;
    }

    /**
     * Returns whether the specified team is known to have officiating duties in this group.
     *
     * @param string $id The ID of the team
     * @return bool Whether the specified team is known to have officiating duties in this group
     */
    public function teamHasOfficiating(string $id) : bool
    {
        if (!property_exists($this->team_has_officiating_lookup, $id)) {
            $this->team_has_officiating_lookup->$id = false;
            foreach ($this->matches as $match) {
                if ($match instanceof GroupBreak) {
                    continue;
                }
                if ($match->getOfficials() !== null && $match->getOfficials()->isTeam() &&
                    $this->competition->getTeam($match->getOfficials()->getTeamID())->getID() === $id)
                {
                    $this->team_has_officiating_lookup->$id = true;
                    break;
                }
            }
        }
        return $this->team_has_officiating_lookup->$id;
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
    public function teamMayHaveMatches(string $id) : bool
    {
        if ($this->isComplete()) {
            // If the group is complete then there are no "maybes"; everything is known so you should call teamHasMatches()
            return false;
        }

        if ($this->competition->getTeam($id)->getID() === CompetitionTeam::UNKNOWN_TEAM_ID) {
            return false;
        }

        $this->buildStageGroupLookup();

        // Look up each reference to see if it leads back to this team
        foreach ($this->stg_grp_lookup as $stage_and_group) {
            $group = $this->competition->getStage($stage_and_group->stage)->getGroup($stage_and_group->group);
            if ((!$group->isComplete() && $group->teamHasMatches($id)) || $group->teamMayHaveMatches($id)) {
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
                    (($this->competition->getTeam($match->getHomeTeam()->getID())->getID() === $team_id) ||
                     ($this->competition->getTeam($match->getAwayTeam()->getID())->getID() === $team_id))) {
                        $match_dates[$match->getDate()] = 1;
                } else if ($flags & VBC_MATCH_OFFICIATING &&
                    $match->getOfficials() !== null &&
                    $match->getOfficials()->isTeam() &&
                    $this->competition->getTeam($match->getOfficials()->getTeamID())->getID() === $team_id) {
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
                              (($this->competition->getTeam($match->getHomeTeam()->getID())->getID() === $team_id) ||
                               ($this->competition->getTeam($match->getAwayTeam()->getID())->getID() === $team_id))) {
                        array_push($matches, $match);
                    } else if ($flags & VBC_MATCH_OFFICIATING &&
                               $match->getOfficials() !== null &&
                               $match->getOfficials()->isTeam() &&
                               $this->competition->getTeam($match->getOfficials()->getTeamID())->getID() === $team_id) {
                        array_push($matches, $match);
                    }
                }
            }
        }
        return $matches;
    }
}
