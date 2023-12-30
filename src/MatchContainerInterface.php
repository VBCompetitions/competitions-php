<?php

namespace VBCompetitions\Competitions;

// @codeCoverageIgnoreStart
define('VBC_MATCH_ALL_IN_GROUP', 1);
define('VBC_MATCH_ALL', 2);
define('VBC_MATCH_PLAYING', 4);
define('VBC_MATCH_OFFICIATING', 8);

define('VBC_TEAMS_FIXED_ID', 1);
define('VBC_TEAMS_KNOWN', 2);
define('VBC_TEAMS_MAYBE', 4);
define('VBC_TEAMS_ALL', 8);
define('VBC_TEAMS_PLAYING', 16);
define('VBC_TEAMS_OFFICIATING', 32);
// @codeCoverageIgnoreEnd

interface MatchContainerInterface {
    public function matchesHaveCourts() : bool;
    public function matchesHaveDates() : bool;
    public function matchesHaveDurations() : bool;
    public function matchesHaveMVPs() : bool;
    public function matchesHaveManagers() : bool;
    public function matchesHaveNotes() : bool;
    public function matchesHaveOfficials() : bool;
    public function matchesHaveStarts() : bool;
    public function matchesHaveVenues() : bool;
    public function matchesHaveWarmups() : bool;

    public function getCompetition() : Competition;

    public function getID() : string;

    /**
     * Returns a list of matches from this match container, where the list depends on the input parameters and on the type of the MatchContainer
     *
     * @param string $team_id When provided, return the matches where this team is playing
     * @param int $flags Controls what gets returned
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL_IN_GROUP</code> if a team is in a group then this returns all matches in that group (e.g. a pool in a competition may want to show all matches)</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> returns only matches that a team is playing in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> returns only matches that a team is officiating</li>
     *                   </ul>
     * @return array<MatchInterface>
     */
    public function getMatches(string $team_id = null, int $flags = 0) : array;

    // teamHasMatches
    // teamHasOfficiating

    /**
     * Returns a list of teams from this match container
     *
     * @param int $flags Controls what gets returned (MAYBE overrides KNOWN overrides FIXED_ID)
     *                   <ul>
     *                     <li><code>VBC_TEAMS_FIXED_ID</code> returns teams with a defined team ID (no references)</li>
     *                     <li><code>VBC_TEAMS_KNOWN</code> returns teams that are known (references that are resolved</li>
     *                     <li><code>VBC_TEAMS_MAYBE</code> returns teams that might be in this container (references that may resolve to a team)</li>
     *                     <li><code>VBC_TEAMS_ALL</code> returns all team IDs, including unresolved references</li>
     *                   </ul>
     * @return array<string>
     */
    public function getTeamIDs(int $flags = 0) : array;
}
