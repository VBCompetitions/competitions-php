<?php

namespace VBCompetitions\Competitions;

use stdClass;

/**
 * Class HTML
 *
 * This class provides methods for generating HTML representations of league tables and match lists.
 */
class HTML {
    /**
     * League table column identifiers.
     */
    public const LEAGUE_COLUMN_POSITION = 'pos';
    public const LEAGUE_COLUMN_TEAM = 'team';
    public const LEAGUE_COLUMN_PLAYED = 'played';
    public const LEAGUE_COLUMN_WINS = 'wins';
    public const LEAGUE_COLUMN_LOSSES = 'losses';
    public const LEAGUE_COLUMN_DRAWS = 'draws';
    public const LEAGUE_COLUMN_SETS_FOR = 'sf';
    public const LEAGUE_COLUMN_SETS_AGAINST = 'sa';
    public const LEAGUE_COLUMN_SETS_DIFFERENCE = 'sd';
    public const LEAGUE_COLUMN_POINTS_FOR = 'pf';
    public const LEAGUE_COLUMN_POINTS_AGAINST = 'pa';
    public const LEAGUE_COLUMN_POINTS_DIFFERENCE = 'pd';
    public const LEAGUE_COLUMN_LEAGUE_POINTS = 'pts';

    /**
     * Match list column identifiers.
     */
    public const MATCH_COLUMN_AWAY_SCORE = 'away_score';
    public const MATCH_COLUMN_AWAY_TEAM = 'away_team';
    public const MATCH_COLUMN_BLANK = 'blank';
    public const MATCH_COLUMN_BREAK = 'break';
    public const MATCH_COLUMN_COURT = 'court';
    public const MATCH_COLUMN_DATE = 'date';
    public const MATCH_COLUMN_DURATION = 'duration';
    public const MATCH_COLUMN_HOME_SCORE = 'home_score';
    public const MATCH_COLUMN_HOME_TEAM = 'home_team';
    public const MATCH_COLUMN_ID = 'id';
    public const MATCH_COLUMN_MANAGER = 'manager';
    public const MATCH_COLUMN_MVP = 'mvp';
    public const MATCH_COLUMN_NOTES = 'notes';
    public const MATCH_COLUMN_OFFICIALS = 'officials';
    public const MATCH_COLUMN_SCORE = 'score';
    public const MATCH_COLUMN_SETS_SCORE = 'sets_score';
    public const MATCH_COLUMN_START = 'start';
    public const MATCH_COLUMN_VENUE = 'venue';
    public const MATCH_COLUMN_WARMUP = 'warmup';

    /**
     * Generates a table cell object.
     *
     * @param string $column_id The identifier of the column
     * @param string $class The class name for styling
     * @param ?string $text The text content of the cell
     * @param ?array $metadata Additional metadata for the cell
     * @param int $colspan The number of columns spanned by the cell (default: 1)
     * @return object The generated table cell object
     */
    private static function genTableCell(string $column_id, string $class, ?string $text = '', ?array $metadata = null, $colspan = 1) : object
    {
        $cell = new stdClass();
        $cell->column_id = $column_id;
        $cell->class = $class;
        $cell->text = $text;
        if ($colspan !== 1) {
            $cell->colspan = $colspan;
        }
        $cell->metadata = $metadata;
        return $cell;
    }

    /**
     * Enriches league table configuration.
     *
     * @param LeagueTable $table The league table object
     * @param ?object $config The configuration object
     * @return object The modified configuration object
     */
    private static function enrichLeagueConfig(LeagueTable $table, object $config = null) : object
    {
        if ($config === null) {
            $config = new stdClass();
        }

        if (!property_exists($config, 'headings')) {
            # Default the headings in if the data exists for them
            $config->headings = [HTML::LEAGUE_COLUMN_POSITION, HTML::LEAGUE_COLUMN_TEAM, HTML::LEAGUE_COLUMN_PLAYED, HTML::LEAGUE_COLUMN_WINS, HTML::LEAGUE_COLUMN_LOSSES];
            if ($table->hasDraws()) {
                array_push($config->headings, HTML::LEAGUE_COLUMN_DRAWS);
            }
            if ($table->hasSets()) {
                array_push($config->headings, HTML::LEAGUE_COLUMN_SETS_FOR);
                array_push($config->headings, HTML::LEAGUE_COLUMN_SETS_AGAINST);
                array_push($config->headings, HTML::LEAGUE_COLUMN_SETS_DIFFERENCE);
            }
            array_push($config->headings, HTML::LEAGUE_COLUMN_POINTS_FOR);
            array_push($config->headings, HTML::LEAGUE_COLUMN_POINTS_AGAINST);
            array_push($config->headings, HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE);
            array_push($config->headings, HTML::LEAGUE_COLUMN_LEAGUE_POINTS);
        }

        # Default the map from heading data to most likely heading text
        if (!property_exists($config, 'headingMap')) {
            $config->headingMap = [];
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_POSITION, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_POSITION] = 'Pos';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_TEAM, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_TEAM] = 'Team';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_PLAYED, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_PLAYED] = 'P';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_WINS, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_WINS] = 'W';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_LOSSES, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_LOSSES] = 'L';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_DRAWS, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_DRAWS] = 'D';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_SETS_FOR, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_SETS_FOR] = 'SF';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_SETS_AGAINST, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_SETS_AGAINST] = 'SA';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_SETS_DIFFERENCE, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_SETS_DIFFERENCE] = 'SD';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_POINTS_FOR, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_POINTS_FOR] = 'PF';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_POINTS_AGAINST] = 'PA';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE] = 'PD';
        }
        if(!array_key_exists(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $config->headingMap)) {
            $config->headingMap[HTML::LEAGUE_COLUMN_LEAGUE_POINTS] = 'PTS';
        }
        return $config;
    }

    /**
     * Enriches match configuration.
     *
     * @param MatchContainerInterface $match_container The match container object
     * @param ?object $config The configuration object
     * @return object The modified configuration object
     */
    private static function enrichMatchConfig(MatchContainerInterface $match_container, object $config = null) : object
    {
        if ($config === null) {
            $config = new stdClass();
        }

        if (!property_exists($config, 'lookupTeamIDs')) {
            $config->lookupTeamIDs = true;
        }

        if (!property_exists($config, 'includeTeamMVPs')) {
            $config->includeTeamMVPs = false;
        }

        if (!property_exists($config, 'headings')) {
            # Default the headings in if the data exists for them
            $config->headings = [HTML::MATCH_COLUMN_ID];
            if ($match_container->matchesHaveCourts()) {
                array_push($config->headings, HTML::MATCH_COLUMN_COURT);
            }
            if ($match_container->matchesHaveVenues()) {
                array_push($config->headings, HTML::MATCH_COLUMN_VENUE);
            }
            if ($match_container->matchesHaveDates()) {
                array_push($config->headings, HTML::MATCH_COLUMN_DATE);
            }
            if ($match_container->matchesHaveWarmups()) {
                array_push($config->headings, HTML::MATCH_COLUMN_WARMUP);
            }
            if ($match_container->matchesHaveStarts()) {
                array_push($config->headings, HTML::MATCH_COLUMN_START);
            }
            if ($match_container->matchesHaveDurations()) {
                array_push($config->headings, HTML::MATCH_COLUMN_DURATION);
            }
            array_push($config->headings, HTML::MATCH_COLUMN_HOME_TEAM);
            array_push($config->headings, HTML::MATCH_COLUMN_SCORE);
            array_push($config->headings, HTML::MATCH_COLUMN_AWAY_TEAM);
            if ($match_container->matchesHaveOfficials()) {
                array_push($config->headings, HTML::MATCH_COLUMN_OFFICIALS);
            }
            if ($match_container->matchesHaveMVPs()) {
                array_push($config->headings, HTML::MATCH_COLUMN_MVP);
            }
            if ($match_container->matchesHaveManagers()) {
                array_push($config->headings, HTML::MATCH_COLUMN_MANAGER);
            }
            if ($match_container->matchesHaveNotes()) {
                array_push($config->headings, HTML::MATCH_COLUMN_NOTES);
            }
        }

        if (!property_exists($config, 'breakHeadings')) {
            $config->breakHeadings = [HTML::MATCH_COLUMN_DATE, HTML::MATCH_COLUMN_START];
        }

        # Default the map from heading data to most likely heading text
        if (!property_exists($config, 'headingMap')) {
            $config->headingMap = [];
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_ID, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_ID] = 'MatchNo';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_COURT, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_COURT] = 'Court';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_VENUE, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_VENUE] = 'Venue';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_DATE, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_DATE] = 'Date';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_WARMUP, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_WARMUP] = 'Warmup';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_START, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_START] = 'Start';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_DURATION, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_DURATION] = 'Duration';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_HOME_TEAM, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_HOME_TEAM] = 'Home Team';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_SCORE, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_SCORE] = 'Score';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_AWAY_TEAM, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_AWAY_TEAM] = 'Away Team';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_OFFICIALS, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_OFFICIALS] = 'Officials';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_MVP, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_MVP] = 'MVP';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_MANAGER, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_MANAGER] = 'Manager';
        }
        if(!array_key_exists(HTML::MATCH_COLUMN_NOTES, $config->headingMap)) {
            $config->headingMap[HTML::MATCH_COLUMN_NOTES] = 'Notes';
        }

        if (!property_exists($config, 'merge')) {
            $config->merge = [HTML::MATCH_COLUMN_DATE];
        }
        return $config;
    }

    /**
     * Generates league table headings.
     *
     * @param LeagueTable $table The league table object
     * @param object $config The configuration object
     * @return array The generated league table headings
     */
    private static function generateLeagueTableHeadings(LeagueTable $table, object $config) : array
    {
        $headings = [];
        foreach ($config->headings as $heading) {
            switch ($heading) {
                case HTML::LEAGUE_COLUMN_POSITION:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_POSITION, 'vbc-league-table-pos vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_POSITION]));
                    break;
                case HTML::LEAGUE_COLUMN_TEAM:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_TEAM, 'vbc-league-table-team vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_TEAM]));
                    break;
                case HTML::LEAGUE_COLUMN_PLAYED:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_PLAYED, 'vbc-league-table-played vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_PLAYED]));
                    break;
                case HTML::LEAGUE_COLUMN_WINS:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_WINS, 'vbc-league-table-wins vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_WINS]));
                    break;
                case HTML::LEAGUE_COLUMN_LOSSES:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_LOSSES, 'vbc-league-table-losses vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_LOSSES]));
                    break;
                case HTML::LEAGUE_COLUMN_DRAWS:
                    if ($table->hasDraws()) {
                        array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_DRAWS, 'vbc-league-table-draws vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_DRAWS]));
                    }
                    break;
                case HTML::LEAGUE_COLUMN_SETS_FOR:
                    if ($table->hasSets()) {
                        array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_SETS_FOR, 'vbc-league-table-sf vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_SETS_FOR]));
                    }
                    break;
                case HTML::LEAGUE_COLUMN_SETS_AGAINST:
                    if ($table->hasSets()) {
                        array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_SETS_AGAINST, 'vbc-league-table-sa vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_SETS_AGAINST]));
                    }
                    break;
                case HTML::LEAGUE_COLUMN_SETS_DIFFERENCE:
                    if ($table->hasSets()) {
                        array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_SETS_DIFFERENCE, 'vbc-league-table-sd vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_SETS_DIFFERENCE]));
                    }
                    break;
                case HTML::LEAGUE_COLUMN_POINTS_FOR:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_POINTS_FOR, 'vbc-league-table-pf vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_POINTS_FOR]));
                    break;
                case HTML::LEAGUE_COLUMN_POINTS_AGAINST:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_POINTS_AGAINST, 'vbc-league-table-pa vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_POINTS_AGAINST]));
                    break;
                case HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, 'vbc-league-table-pd vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE]));
                    break;
                case HTML::LEAGUE_COLUMN_LEAGUE_POINTS:
                    array_push($headings, HTML::genTableCell(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, 'vbc-league-table-pts vbc-league-table-group-'.$table->getGroupID(), $config->headingMap[HTML::LEAGUE_COLUMN_LEAGUE_POINTS]));
                    break;
            }
        }
        return $headings;
    }

    /**
     * Generates a league table row.
     *
     * @param object $config The configuration object
     * @param LeagueTableEntry $entry The league table entry object
     * @param int $pos The position of the team in the table
     * @param bool $this_team Indicates if it's the team of interest
     * @return array The generated league table row
     */
    private static function generateLeagueTableRow(object $config, LeagueTableEntry $entry, int $pos, bool $this_team) : array
    {
        $cells = [];
        foreach ($config->headings as $heading) {
            switch ($heading) {
                case HTML::LEAGUE_COLUMN_POSITION:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_POSITION, 'vbc-league-table-pos vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), $pos.'.'));
                    break;
                case HTML::LEAGUE_COLUMN_TEAM:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_TEAM, 'vbc-league-table-team vbc-league-table-group-'.$entry->getGroupID().($this_team ? ' vbc-this-team' : ''), $entry->getTeam(), [ 'team_id' => $entry->getTeamID() ]));
                    break;
                case HTML::LEAGUE_COLUMN_PLAYED:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_PLAYED, 'vbc-league-table-played vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getPlayed())));
                    break;
                case HTML::LEAGUE_COLUMN_WINS:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_WINS, 'vbc-league-table-wins vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getWins())));
                    break;
                case HTML::LEAGUE_COLUMN_LOSSES:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_LOSSES, 'vbc-league-table-losses vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getLosses())));
                    break;
                case HTML::LEAGUE_COLUMN_DRAWS:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_DRAWS, 'vbc-league-table-draws vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getDraws())));
                    break;
                case HTML::LEAGUE_COLUMN_SETS_FOR:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_SETS_FOR, 'vbc-league-table-sf vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getSF())));
                    break;
                case HTML::LEAGUE_COLUMN_SETS_AGAINST:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_SETS_AGAINST, 'vbc-league-table-sa vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getSA())));
                    break;
                case HTML::LEAGUE_COLUMN_SETS_DIFFERENCE:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_SETS_DIFFERENCE, 'vbc-league-table-sd vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getSD())));
                    break;
                case HTML::LEAGUE_COLUMN_POINTS_FOR:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_POINTS_FOR, 'vbc-league-table-pf vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getPF())));
                    break;
                case HTML::LEAGUE_COLUMN_POINTS_AGAINST:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_POINTS_AGAINST, 'vbc-league-table-pa vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getPA())));
                    break;
                case HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, 'vbc-league-table-pd vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getPD())));
                    break;
                case HTML::LEAGUE_COLUMN_LEAGUE_POINTS:
                    array_push($cells, HTML::genTableCell(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, 'vbc-league-table-pts vbc-league-table-num vbc-league-table-group-'.$entry->getGroupID(), strval($entry->getPTS())));
                    break;
            }
        }
        return $cells;
    }

    /**
     * Generates match list headings.
     *
     * @param MatchContainerInterface $match_container The match container object
     * @param object $config The configuration object
     */
    private static function generateMatchListHeadings(MatchContainerInterface $match_container, object $config)
    {
        $break_col_span = 0;
        $headings = [];

        foreach ($config->headings as $heading) {
            $break_col_span++;
            switch ($heading) {
                case HTML::MATCH_COLUMN_BLANK:
                    array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_BLANK, 'vbc-match-blank vbc-match-group-'.$match_container->getID(), ''));
                    break;
                case HTML::MATCH_COLUMN_ID:
                    array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_ID, 'vbc-match-id vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_ID]));
                    break;
                case HTML::MATCH_COLUMN_COURT:
                    if ($match_container->matchesHaveCourts()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_COURT, 'vbc-match-court vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_COURT]));
                    }
                    break;
                case HTML::MATCH_COLUMN_VENUE:
                    if ($match_container->matchesHaveVenues()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_VENUE, 'vbc-match-venue vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_VENUE]));
                    }
                    break;
                case HTML::MATCH_COLUMN_DATE:
                    if ($match_container->matchesHaveDates()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_DATE, 'vbc-match-date vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_DATE]));
                    }
                    break;
                case HTML::MATCH_COLUMN_WARMUP:
                    if ($match_container->matchesHaveWarmups()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_WARMUP, 'vbc-match-warmup vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_WARMUP]));
                    }
                    break;
                case HTML::MATCH_COLUMN_START:
                    if ($match_container->matchesHaveStarts()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_START, 'vbc-match-start vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_START]));
                    }
                    break;
                case HTML::MATCH_COLUMN_DURATION:
                    if ($match_container->matchesHaveDurations()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_DURATION, 'vbc-match-duration vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_DURATION]));
                    }
                    break;
                case HTML::MATCH_COLUMN_HOME_TEAM:
                    array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_HOME_TEAM, 'vbc-match-team vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_HOME_TEAM]));
                    break;
                case HTML::MATCH_COLUMN_SCORE:
                    $break_col_span++;
                    array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_SCORE, 'vbc-match-score vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_SCORE], null, 2));
                    break;
                case HTML::MATCH_COLUMN_AWAY_TEAM:
                    array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_AWAY_TEAM, 'vbc-match-team vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_AWAY_TEAM]));
                    break;
                case HTML::MATCH_COLUMN_OFFICIALS:
                    if ($match_container->matchesHaveOfficials()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_OFFICIALS, 'vbc-match-officials vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_OFFICIALS]));
                    }
                    break;
                case HTML::MATCH_COLUMN_MVP:
                    if ($match_container->matchesHaveMVPs()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_MVP, 'vbc-match-mvp vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_MVP]));
                    }
                    break;
                case HTML::MATCH_COLUMN_MANAGER:
                    if ($match_container->matchesHaveManagers()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_MANAGER, 'vbc-match-manager vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_MANAGER]));
                    }
                    break;
                case HTML::MATCH_COLUMN_NOTES:
                    if ($match_container->matchesHaveNotes()) {
                        array_push($headings, HTML::genTableCell(HTML::MATCH_COLUMN_NOTES, 'vbc-match-notes vbc-match-group-'.$match_container->getID(), $config->headingMap[HTML::MATCH_COLUMN_NOTES]));
                    }
                    break;
            }
        }

        $return_data = new stdClass();
        $return_data->break_col_span = $break_col_span;
        $return_data->headings = $headings;
        return $return_data;
    }

    /**
     * Generates a match row.
     *
     * @param MatchContainerInterface $match_container The match container object
     * @param object $config The configuration object
     * @param MatchInterface $match The match object
     * @param ?string $team_id The ID of the team (default: null)
     * @return array The generated match row
     */
    private static function generateMatchRow(MatchContainerInterface $match_container, object $config, MatchInterface $match, ?string $team_id) : array
    {
        $cells = [];
        $global_class = ($match_container instanceof IfUnknown ? 'vbc-unknown-value ' : '');
        foreach ($config->headings as $heading) {
            switch ($heading) {
                case HTML::MATCH_COLUMN_BLANK:
                    array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_BLANK, $global_class.'vbc-match-blank vbc-match-group-'.$match->getGroup()->getID(), ''));
                    break;
                case HTML::MATCH_COLUMN_ID:
                    array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_ID, $global_class.'vbc-match-id vbc-match-group-'.$match->getGroup()->getID(), $match->getID()));
                    break;
                case HTML::MATCH_COLUMN_COURT:
                    if ($match_container->matchesHaveCourts()) {
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_COURT, $global_class.'vbc-match-court vbc-match-group-'.$match->getGroup()->getID(), $match->getCourt()));
                    }
                    break;
                case HTML::MATCH_COLUMN_VENUE:
                    if ($match_container->matchesHaveVenues()) {
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_VENUE, $global_class.'vbc-match-venue vbc-match-group-'.$match->getGroup()->getID(), $match->getVenue()));
                    }
                    break;
                case HTML::MATCH_COLUMN_DATE:
                    if ($match_container->matchesHaveDates()) {
                        $class = 'vbc-match-date';
                        if ($match instanceof GroupMatch && $match->isComplete()) {
                            $class .= ' vbc-match-played';
                        }
                        $class .= ' vbc-match-group-'.$match->getGroup()->getID();
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_DATE, $global_class.$class, $match->getDate()));
                    }
                    break;
                case HTML::MATCH_COLUMN_WARMUP:
                    if ($match_container->matchesHaveWarmups()) {
                        $class = 'vbc-match-warmup';
                        if ($match instanceof GroupMatch && $match->isComplete()) {
                            $class .= ' vbc-match-played';
                        }
                        $class .= ' vbc-match-group-'.$match->getGroup()->getID();
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_WARMUP, $global_class.$class, $match->getWarmup()));
                    }
                    break;
                case HTML::MATCH_COLUMN_START:
                    if ($match_container->matchesHaveStarts()) {
                        $class = 'vbc-match-start';
                        if ($match instanceof GroupMatch && $match->isComplete()) {
                            $class .= ' vbc-match-played';
                        }
                        $class .= ' vbc-match-group-'.$match->getGroup()->getID();
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_START, $global_class.$class, $match->getStart()));
                    }
                    break;
                case HTML::MATCH_COLUMN_DURATION:
                    if ($match_container->matchesHaveDurations()) {
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_DURATION, $global_class.'vbc-match-duration vbc-match-group-'.$match->getGroup()->getID(), $match->getDuration()));
                    }
                    break;
                case HTML::MATCH_COLUMN_HOME_TEAM:
                    $home_team_match = $team_id !== CompetitionTeam::UNKNOWN_TEAM_ID && $match_container->getCompetition()->getTeamByID($match->getHomeTeam()->getID())->getID() === $team_id;
                    $class = 'vbc-match-team vbc-match-group-'.$match->getGroup()->getID().($home_team_match ? ' vbc-this-team' : '');
                    if ($config->lookupTeamIDs) {
                        $team_name = $match_container->getCompetition()->getTeamByID($match->getHomeTeam()->getID())->getName();
                    } else {
                        $team_name = $match->getHomeTeam()->getID();
                    }
                    $metadata = [];
                    if (property_exists($match->getHomeTeam(), 'mvp')) {
                        $metadata['mvp'] = $match->getHomeTeam()->getMVP();
                    }
                    array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_HOME_TEAM, $global_class.$class, $team_name, $metadata));
                    break;
                case HTML::MATCH_COLUMN_SCORE:
                    if ($match->getGroup()->getMatchType() === MatchType::SETS) {
                        if ($match->isComplete()) {
                            if ($match->isDraw()) {
                                $sets_table = '<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-'.$match->getGroup()->getID().' vbc-match-loser">';
                                $sets_table .= $match->getHomeTeamSets().'</td><td class="vbc-match-score vbc-match-group-'.$match->getGroup()->getID().' vbc-match-loser">';
                                $sets_table .= $match->getAwayTeamSets().'</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-'.$match->getGroup()->getID().'" colspan="4">';
                                $set_scores = [];
                                for ($set_count=0; $set_count < count($match->getHomeTeam()->getScores()); $set_count++) {
                                    if ($match->getHomeTeam()->getScores()[$set_count] > $match->getAwayTeam()->getScores()[$set_count]) {
                                        $class = ($match->getHomeTeam()->getScores()[$set_count] > $match->getGroup()->getSetConfig()->getMinPoints() ? 'vbc-match-winner ':'').'vbc-match-group-'.$match->getGroup()->getID();
                                        //  is non-breaking hyphen to make sure set scores stay on one line
                                        array_push($set_scores, '<span class="'.$class.'">'.$match->getHomeTeam()->getScores()[$set_count].'</span>&#8209;'.$match->getAwayTeam()->getScores()[$set_count]);
                                    } else if ($match->getHomeTeam()->getScores()[$set_count] < $match->getAwayTeam()->getScores()[$set_count]) {
                                        $class = ($match->getAwayTeam()->getScores()[$set_count] > $match->getGroup()->getSetConfig()->getMinPoints() ? 'vbc-match-winner ':'').'vbc-match-group-'.$match->getGroup()->getID();
                                        array_push($set_scores, $match->getHomeTeam()->getScores()[$set_count].'&#8209;<span class="'.$class.'">'.$match->getAwayTeam()->getScores()[$set_count].'</span>');
                                    } else {
                                        array_push($set_scores, $match->getHomeTeam()->getScores()[$set_count].'&#8209;'.$match->getAwayTeam()->getScores()[$set_count]);
                                    }
                                }
                                $sets_table .= join('   ', $set_scores);
                                $sets_table .= '</table>';

                                array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_SETS_SCORE, $global_class.'vbc-match-score-sets vbc-match-group-'.$match->getGroup()->getID(), $sets_table, null, 2));
                            } else {
                                $sets_table = '<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-'.$match->getGroup()->getID().' ';
                                $sets_table .= ($match->getWinnerTeamID() === $match->getHomeTeam()->getID() ? 'vbc-match-winner' : ' vbc-match-loser');
                                $sets_table .= '">'.$match->getHomeTeamSets().'</td><td class="vbc-match-score vbc-match-group-'.$match->getGroup()->getID().' ';
                                $sets_table .= ($match->getWinnerTeamID() === $match->getAwayTeam()->getID() ? 'vbc-match-winner' : ' vbc-match-loser');
                                $sets_table .= '">'.$match->getAwayTeamSets().'</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-'.$match->getGroup()->getID().'" colspan="4">';
                                $set_scores = [];
                                for ($set_count=0; $set_count < count($match->getHomeTeam()->getScores()); $set_count++) {
                                    if ($match->getHomeTeam()->getScores()[$set_count] > $match->getAwayTeam()->getScores()[$set_count]) {
                                        $class = ($match->getHomeTeam()->getScores()[$set_count] > $match->getGroup()->getSetConfig()->getMinPoints() ? 'vbc-match-winner ':'').'vbc-match-group-'.$match->getGroup()->getID();
                                        array_push($set_scores, '<span class="'.$class.'">'.$match->getHomeTeam()->getScores()[$set_count].'</span>&#8209;'.$match->getAwayTeam()->getScores()[$set_count]);
                                    } else if ($match->getHomeTeam()->getScores()[$set_count] < $match->getAwayTeam()->getScores()[$set_count]) {
                                        $class = ($match->getAwayTeam()->getScores()[$set_count] > $match->getGroup()->getSetConfig()->getMinPoints() ? 'vbc-match-winner ':'').'vbc-match-group-'.$match->getGroup()->getID();
                                        array_push($set_scores, $match->getHomeTeam()->getScores()[$set_count].'&#8209;<span class="'.$class.'">'.$match->getAwayTeam()->getScores()[$set_count].'</span>');
                                    } else {
                                        array_push($set_scores, $match->getHomeTeam()->getScores()[$set_count].'&#8209;'.$match->getAwayTeam()->getScores()[$set_count]);
                                    }
                                }
                                $sets_table .= join('   ', $set_scores);
                                $sets_table .= '</table>';

                                array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_SETS_SCORE, $global_class.'vbc-match-score-sets vbc-match-group-'.$match->getGroup()->getID(), $sets_table, null, 2));
                            }
                        } else {
                            $sets_table = '<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-loser vbc-match-group-'.$match->getGroup()->getID().'">'.$match->getHomeTeamSets().'</td>';
                            $sets_table .= '<td class="vbc-match-score vbc-match-loser">'.$match->getAwayTeamSets().'</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-'.$match->getGroup()->getID().'" colspan="4">';
                            $set_scores = [];
                            for ($set_count=0; $set_count < count($match->getHomeTeam()->getScores()); $set_count++) {
                                if ($match->getHomeTeam()->getScores()[$set_count] > $match->getAwayTeam()->getScores()[$set_count]) {
                                    $class = ($match->getHomeTeam()->getScores()[$set_count] > $match->getGroup()->getSetConfig()->getMinPoints() ? 'vbc-match-winner ':'').'vbc-match-group-'.$match->getGroup()->getID();
                                    array_push($set_scores, '<span class="'.$class.'">'.$match->getHomeTeam()->getScores()[$set_count].'</span>&#8209;'.$match->getAwayTeam()->getScores()[$set_count]);
                                } else if ($match->getHomeTeam()->getScores()[$set_count] < $match->getAwayTeam()->getScores()[$set_count]) {
                                    $class = ($match->getAwayTeam()->getScores()[$set_count] > $match->getGroup()->getSetConfig()->getMinPoints() ? 'vbc-match-winner ':'').'vbc-match-group-'.$match->getGroup()->getID();
                                    array_push($set_scores, $match->getHomeTeam()->getScores()[$set_count].'&#8209;<span class="'.$class.'">'.$match->getAwayTeam()->getScores()[$set_count].'</span>');
                                } else {
                                    array_push($set_scores, $match->getHomeTeam()->getScores()[$set_count].'&#8209;'.$match->getAwayTeam()->getScores()[$set_count]);
                                }
                            }
                            if (count($set_scores) === 0) {
                                array_push($set_scores, '0&#8209;0');
                            }
                            $sets_table .= join('   ', $set_scores);
                            $sets_table .= '</table>';

                            array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_SETS_SCORE, $global_class.'vbc-match-score-sets vbc-match-group-'.$match->getGroup()->getID(), $sets_table, null, 2));
                        }
                    } else {
                        $home_team_score = count($match->getHomeTeam()->getScores()) > 0 ? $match->getHomeTeam()->getScores()[0] : 0;
                        $away_team_score = count($match->getAwayTeam()->getScores()) > 0 ? $match->getAwayTeam()->getScores()[0] : 0;
                        $home_team_class = 'vbc-match-score vbc-match-group-'.$match->getGroup()->getID();
                        $away_team_class = 'vbc-match-score vbc-match-group-'.$match->getGroup()->getID();

                        if ($match->isComplete() && !$match->isDraw()) {
                            $home_team_class = 'vbc-match-score vbc-match-group-'.$match->getGroup()->getID().($match->getWinnerTeamID() === $match->getHomeTeam()->getID() ? ' vbc-match-winner' : ' vbc-match-loser');
                            $away_team_class = 'vbc-match-score vbc-match-group-'.$match->getGroup()->getID().($match->getWinnerTeamID() === $match->getAwayTeam()->getID() ? ' vbc-match-winner' : ' vbc-match-loser');
                        }

                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_HOME_SCORE, $global_class.$home_team_class, $home_team_score));
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_AWAY_SCORE, $global_class.$away_team_class, $away_team_score));
                    }
                    break;
                case HTML::MATCH_COLUMN_AWAY_TEAM:
                    $away_team_match = $team_id !== CompetitionTeam::UNKNOWN_TEAM_ID && $match_container->getCompetition()->getTeamByID($match->getAwayTeam()->getID())->getID() === $team_id;
                    $class = 'vbc-match-team vbc-match-group-'.$match->getGroup()->getID().($away_team_match ? ' vbc-this-team' : '');
                    if ($config->lookupTeamIDs) {
                        $team_name = $match_container->getCompetition()->getTeamByID($match->getAwayTeam()->getID())->getName();
                    } else {
                        $team_name = $match->getAwayTeam()->getID();
                    }
                    $metadata = [];
                    if (property_exists($match->getAwayTeam(), 'mvp')) {
                        $metadata['mvp'] = $match->getAwayTeam()->getMVP();
                    }
                    array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_AWAY_TEAM, $global_class.$class, $team_name, $metadata));
                    break;
                case HTML::MATCH_COLUMN_OFFICIALS:
                    if ($match_container->matchesHaveOfficials()) {
                        $class = 'vbc-match-officials vbc-match-group-'.$match->getGroup()->getID();
                        if ($match->getOfficials() === null) {
                            array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_OFFICIALS, $global_class.$class, ''));
                        } else if ($match->getOfficials()->isTeam()) {
                            if ($config->lookupTeamIDs) {
                                $official_team = $match_container->getCompetition()->getTeamByID($match->getOfficials()->getTeamID());

                                if ($team_id !== CompetitionTeam::UNKNOWN_TEAM_ID &&
                                    $team_id === $official_team->getID()) {
                                        $class .= ' vbc-this-team';
                                }
                                array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_OFFICIALS, $global_class.$class, $official_team->getName()));
                            } else {
                                if ($team_id !== CompetitionTeam::UNKNOWN_TEAM_ID &&
                                    $team_id === $match->getOfficials()->getTeamID()) {
                                        $class .= ' vbc-this-team';
                                }
                                array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_OFFICIALS, $global_class.$class, $match->getOfficials()->getTeamID()));
                            }
                        } else {
                            $referees = 'First: '.$match->getOfficials()->getFirstRef();
                            $referees .= $match->getOfficials()->hasSecondRef() ? ', Second: '.$match->getOfficials()->getSecondRef() : '';
                            $referees .= $match->getOfficials()->hasChallengeRef() ? ', Challenge: '.$match->getOfficials()->getChallengeRef() : '';
                            $referees .= $match->getOfficials()->hasAssistantChallengeRef() ? ', Assistant Challenge: '.$match->getOfficials()->getAssistantChallengeRef() : '';
                            $referees .= $match->getOfficials()->hasReserveRef() ? ', Reserve: '.$match->getOfficials()->getReserveRef() : '';
                            $referees .= $match->getOfficials()->hasScorer() ? ', Scorer: '.$match->getOfficials()->getScorer() : '';
                            $referees .= $match->getOfficials()->hasAssistantScorer() ? ', Scorer: '.$match->getOfficials()->getAssistantScorer() : '';
                            $referees .= $match->getOfficials()->hasLinespersons() ? ', Linespersons: '.join(', ', $match->getOfficials()->getLinespersons()) : '';
                            $referees .= $match->getOfficials()->hasBallCrew() ? ', Ball crew: '.join(', ', $match->getOfficials()->getBallCrew()) : '';
                            array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_OFFICIALS, $global_class.$class, $referees));
                        }
                    }
                    break;
                case HTML::MATCH_COLUMN_MVP:
                    if ($match_container->matchesHaveMVPs()) {
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_MVP, $global_class.'vbc-match-mvp vbc-match-group-'.$match->getGroup()->getID(), $match->getMVP()));
                    }
                    break;
                case HTML::MATCH_COLUMN_MANAGER:
                    if ($match_container->matchesHaveManagers()) {
                        $class = 'vbc-match-manager vbc-match-group-'.$match->getGroup()->getID();
                        if ($match->getManager() === null) {
                            array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_MANAGER, $global_class.$class, ''));
                        } else if ($match->getManager()->isTeam()) {
                            if ($config->lookupTeamIDs) {
                                $manager_team = $match_container->getCompetition()->getTeamByID($match->getManager()->getTeamID());

                                if ($team_id !== CompetitionTeam::UNKNOWN_TEAM_ID &&
                                    $team_id === $manager_team->getID()) {
                                    $class .= ' vbc-this-team';
                                }
                                array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_MANAGER, $global_class.$class, $manager_team->getName()));
                            } else {
                                if ($team_id !== CompetitionTeam::UNKNOWN_TEAM_ID &&
                                    $team_id === $match->getManager()->getTeamID()) {
                                    $class .= ' vbc-this-team';
                                }
                                array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_MANAGER, $global_class.$class, $match->getManager()->getTeamID()));
                            }
                        } else {
                            array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_MANAGER, $global_class.$class, $match->getManager()->getManagerName()));
                        }
                    }
                    break;
                case HTML::MATCH_COLUMN_NOTES:
                    if ($match_container->matchesHaveNotes()) {
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_NOTES, $global_class.'vbc-match-notes vbc-match-group-'.$match->getGroup()->getID(), $match->getNotes()));
                    }
                    break;
            }
        }
        return $cells;
    }

    /**
     * Generates a break row.
     *
     * @param object $config The configuration object
     * @param GroupBreak $break The group break object
     * @return array The generated break row
     */
    private static function generateBreakRow(object $config, GroupBreak $break) : array
    {
        $cells = [];
        $col_span = 1;
        foreach ($config->headings as $heading) {
            switch ($heading) {
                case HTML::MATCH_COLUMN_START:
                    if ($col_span > 1) {
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_BREAK, 'vbc-match-break', '', null, $col_span));
                        $col_span = 1;
                    }
                    array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_START, 'vbc-match-start', $break->getStart()));
                    break;
                case HTML::MATCH_COLUMN_DATE:
                    if ($col_span > 1) {
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_BREAK, 'vbc-match-break', '', null, $col_span));
                        $col_span = 1;
                    }
                    array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_DATE, 'vbc-match-date', $break->getDate()));
                    break;
                case HTML::MATCH_COLUMN_DURATION:
                    if ($col_span > 1) {
                        array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_BREAK, 'vbc-match-break', '', null, $col_span));
                        $col_span = 1;
                    }
                    array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_DURATION, 'vbc-match-duration', $break->getDuration()));
                    break;
                default:
                    $col_span++;
            }
        }
        if ($col_span > 1) {
            $break_name = $break->getName();
            $name = isset($break_name) ? $break->getName() : 'Break';
            array_push($cells, HTML::genTableCell(HTML::MATCH_COLUMN_BREAK, 'vbc-match-break', $name, null, $col_span));
        }
        return $cells;
    }

    /**
     * Generates a standard HTML table to represent the matches table for a group
     *
     * The $config object can be used to configure the match data as described below:
     *
     * <pre>{
     *   headings: []           - the list of headings (using the HTML::MATCH_COLUMN_* constants) in the desired order.
     *                            This defaults to MATCH_COLUMN_ID, MATCH_COLUMN_HOME_TEAM, MATCH_COLUMN_SCORE,
     *                            MATCH_COLUMN_AWAY_TEAM and will include any fields where one of the matches has a value.
     *                            This can also have multiple MATCH_COLUMN_BLANK entries.  In this case it will include a
     *                            cell with an empty string for the text and the class "vbc-blank"
     *   breakHeadings: []      - the list of headings to populate before printing a break's "name" value.  This defaults
     *                            to MATCH_COLUMN_DATE, HTML::MATCH_COLUMN_START
     *   headingMap: {}         - associative array the key is the heading name (using the HTML::MATCH_COLUMN_* constants),
     *                            and the value is the new text to use for that heading
     *   merge: []              - the list of headings where rows with matching data can be merged together (by a "rowspan"
     *                            property), e.g. all matches on the same date share a single cell in the output for that date
     *                            field.  By default this contains MATCH_COLUMN_DATE
     *   lookupTeamIDs: true    - Whether team IDs should be resolved to the team name (where defined)
     *   includeTeamMVPs: false - Whether a team's MVP for completed matches should be included with the team name data
     * }
     * </pre>
     *
     * @param MatchContainerInterface $match_container The container of the matches to present
     * @param ?object $config The configuration for generating the league table data (default: null)
     * @param ?string $team_id The ID for a team to decorate as "this" team (default: null)
     * @param int $flags Controls which matches to include (default: VBC_MATCH_ALL_IN_GROUP)
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL_IN_GROUP</code> if a team is in a group then this includes all matches in that group
     *                         (e.g. a pool in a competition may want to show all matches)</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> includes matches that a team is playing in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> includes matches that a team is officiating</li>
     *                   </ul>
     *
     * @return string the HTML table as a string
     */
    public static function getMatchesHTML(MatchContainerInterface $match_container, object $config = null, string $team_id = null, int $flags = 1) : string
    {
        $config = HTML::enrichMatchConfig($match_container, $config);
        $matches = HTML::getMatchesForHTML($match_container, $config, $team_id, $flags);
        $body = '<table class="'.$matches->class.'"><tr>';
        foreach ($matches->headings as $heading) {
            $body .= '<th class="'.$heading->class.'"';
            if (property_exists($heading, 'colspan')) {
                $body .= ' colspan="'.$heading->colspan.'"';
            }
            $body .= '>'.$heading->text.'</th>';
        }
        $body .= '</tr>';
        foreach ($matches->rows as $row) {
            $body .= '<tr>';
            foreach ($row as $cell) {
                $body .= '<td class="'.$cell->class.'"';
                if (property_exists($cell, 'colspan')) {
                    $body .= ' colspan="'.$cell->colspan.'"';
                }
                if (property_exists($cell, 'rowspan')) {
                    $body .= ' rowspan="'.$cell->rowspan.'"';
                }
                $body .= '>'.$cell->text;
                if ($config->includeTeamMVPs && ($cell->column_id === HTML::MATCH_COLUMN_HOME_TEAM || $cell->column_id === HTML::MATCH_COLUMN_AWAY_TEAM) && $cell->metadata['mvp'] !== null && strlen($cell->metadata['mvp']) > 0) {
                    $body .= '<br><span class="vbc-match-team-mvp">MVP: '.$cell->metadata['mvp'].'</span>';
                }
                $body .= '</td>';
            }
            $body .= '</tr>';
        }
        $body .= '</table>';
        return $body;
    }

    /**
     * Generates the logical data for presenting the list of matches in a group.  This can be used to draw a table
     * in a way where the caller can manipulate the data (for example injecting their own columns/rows or any other data).
     * If you want a standard HTML table then call getMatchesHTML().
     *
     * The $config object can be used to configure the match data as described below:
     *
     * <pre>{
     *   headings: []           - the list of headings (using the HTML::MATCH_COLUMN_* constants) in the desired order.
     *                            This defaults to MATCH_COLUMN_ID, MATCH_COLUMN_HOME_TEAM, MATCH_COLUMN_SCORE,
     *                            MATCH_COLUMN_AWAY_TEAM and will include any fields where one of the matches has a value.
     *                            This can also have multiple MATCH_COLUMN_BLANK entries.  In this case it will include a
     *                            cell with an empty string for the text and the class "vbc-blank"
     *   breakHeadings: []      - the list of headings to populate before printing a break's "name" value.  This defaults
     *                            to MATCH_COLUMN_DATE, HTML::MATCH_COLUMN_START
     *   headingMap: {}         - associative array where the key is the heading name (using the HTML::MATCH_COLUMN_* constants),
     *                            and the value is the new text to use for that heading
     *   merge: []              - the list of headings where rows with matching data can be merged together (by a "rowspan"
     *                            property), e.g. all matches on the same date share a single cell in the output for that date
     *                            field.  By default this contains MATCH_COLUMN_DATE
     *   lookupTeamIDs: true    - Whether team IDs should be resolved to the team name (where defined)
     *   includeTeamMVPs: false - Whether a team's MVP for completed matches should be included with the team name data
     * }
     * </pre>
     *
     * The data returned is an object of the form:
     * <pre>{
     *   class: "vbc-match vbc-match-group-{group ID}" - a CSS class name for the table
     *   headings: [                       - an array of heading entities, representing a table's row of headings
     *     {
     *       column_id: "xxx"              - an ID for this heading entity.  This will be one of the values defined by the
     *                                       following list and, unless changed by $config, in the following order.  Note
     *                                       that some headings are only present if applicable (e.g. start time is only
     *                                       shown if defined):
     *                                          HTML::MATCH_COLUMN_ID
     *                                          HTML::MATCH_COLUMN_COURT
     *                                          HTML::MATCH_COLUMN_VENUE
     *                                          HTML::MATCH_COLUMN_DATE
     *                                          HTML::MATCH_COLUMN_WARMUP
     *                                          HTML::MATCH_COLUMN_START
     *                                          HTML::MATCH_COLUMN_DURATION
     *                                          HTML::MATCH_COLUMN_HOME_TEAM
     *                                          HTML::MATCH_COLUMN_SCORE
     *                                          HTML::MATCH_COLUMN_AWAY_TEAM
     *                                          HTML::MATCH_COLUMN_OFFICIALS
     *                                          HTML::MATCH_COLUMN_MVP
     *                                          HTML::MATCH_COLUMN_MANAGER
     *                                          HTML::MATCH_COLUMN_NOT
     *
     *       class: "vbc-match-xxx vbc-match-group-{group ID}" - a CSS class list for this heading, where the
     *                                       "xxx" suffix is the column_id.
     *
     *       text: "Xxx"                   - the text for this heading
     *
     *         [colspan|rowspan]           - an optional field indicating how many columns or rows this cell should span
     *                                       e.g. the heading for the score field will span two columns for continuous matches
     *     },
     *     {...next heading's data...}
     *   ],
     *   rows: [                           - an array of data rows, like a table row
     *     [                               - an array of data entities, like a table data cell
     *       {
     *         column_id: "xxx"            - an ID for this data cell entity.  As with the headings, this is a string matching
     *                                       one of the constants HTML::MATCH_COLUMN_*.  Note that for set-based matches this
     *                                       will also include HTML::MATCH_COLUMN_SETS_SCORE
     *         class: "vbc-match-xxx"      - a CSS class list for this cell, where the "xxx" suffix is the column_id.  For the
     *                                       MATCH_COLUMN_SCORE fields, this will include "vbc-match-winner" or "vbc-match-loser"
     *                                       for completed matches that aren't a draw
     *         text: "xxx"                 - the text for this data cell, typically the data for this match.  Note that for
     *                                       set-based matches, the score text is an embedded HTML table with the set score and
     *                                       score in each individual set
     *         [colspan|rowspan]           - an optional field indicating how many columns or rows this cell should span
     *                                       e.g. the
     *         [metadata]                  - an optional field containing extra metadata about this table cell.  This
     *                                       includes the following:
     *                                       - "mvp": the team's MVP in a match (included in the team name cell)
     *       },
     *       {...next cell's data...}
     *     ],
     *     [...next row's data...]
     *   ]
     *
     * }
     * </pre>
     *
     * @param MatchContainerInterface $match_container The container of the matches to present
     * @param ?object $config The configuration for generating the league table data (default: null)
     * @param ?string $team_id The ID for a team to decorate as "this" team (default: null)
     * @param int $flags Controls which matches to include (default: VBC_MATCH_ALL_IN_GROUP)
     *                   <ul>
     *                     <li><code>VBC_MATCH_ALL_IN_GROUP</code> if a team is in a group then this includes all matches in that group
     *                         (e.g. a pool in a competition may want to show all matches)</li>
     *                     <li><code>VBC_MATCH_PLAYING</code> includes matches that a team is playing in</li>
     *                     <li><code>VBC_MATCH_OFFICIATING</code> includes matches that a team is officiating</li>
     *                   </ul>
     *
     * @return object An object containing the match information suitable for generating HTML from
     */
    public static function getMatchesForHTML(MatchContainerInterface $match_container, object $config = null, string $team_id = null, int $flags = 1) : object
    {
        $config = HTML::enrichMatchConfig($match_container, $config);
        $table = new stdClass();

        $table->class = 'vbc-match vbc-match-group-'.$match_container->getID();
        $match_list_heading = HTML::generateMatchListHeadings($match_container, $config);
        $table->headings = $match_list_heading->headings;
        $table->rows = [];
        foreach ($match_container->getMatches($team_id, $flags) as $match) {
            if ($match instanceof GroupBreak) {
                // TODO - how do we know width?
                array_push($table->rows, HTML::generateBreakRow($config, $match));
            } else if ($match instanceof GroupMatch) {
                array_push($table->rows, HTML::generateMatchRow($match_container, $config, $match, $team_id));// $home_team_match, $away_team_match, $official_team_match, $manager_team_match));
            }
        }

        if (count($table->rows) === 0) {
            return $table;
        }

        if (count($config->merge) === 0) {
            return $table;
        }

        // If merging dates, look for the date column and merge down
        $cells_column_index = 0;
        if (in_array(HTML::MATCH_COLUMN_DATE, $config->merge)) {
            for ($headings_column_index = 0; $headings_column_index < count($table->headings); $headings_column_index++) {
                if ($table->headings[$headings_column_index]->column_id === HTML::MATCH_COLUMN_DATE) {
                    for ($row_index = 0; $row_index < count($table->rows); $row_index++) {
                        $cell = $table->rows[$row_index][$cells_column_index];
                        $row_look_ahead = $row_index + 1;
                        while ($row_look_ahead < count($table->rows) &&
                            $cell->text !== '' &&
                            $cell->column_id === HTML::MATCH_COLUMN_DATE &&
                            $cell->text === $table->rows[$row_look_ahead][$cells_column_index]->text)
                        {
                            $row_look_ahead++;
                        }
                        if ($row_look_ahead > $row_index + 1) {
                            $rowspan = $row_look_ahead - $row_index;
                            $table->rows[$row_index][$cells_column_index]->rowspan = $rowspan;
                            for ($row_delete_ahead = $row_index + 1; $row_delete_ahead < $row_look_ahead; $row_delete_ahead++) {
                                $table->rows[$row_delete_ahead][$cells_column_index]->delete = true;
                            }
                            $row_index += $rowspan - 1;
                        }
                    }
                }

                $cells_column_index += property_exists($table->headings[$headings_column_index], 'rowspan') ? $table->headings[$headings_column_index]->rowspan : 1;
            }
        }

        // go row by row, adding a blank line before a date
        // we have to do this now or cells may have been merged over a date boundary
        for ($row_index = 0; $row_index < count($table->rows); $row_index++) {
            $row = $table->rows[$row_index];
            for ($column_index = 0; $column_index < count($row); $column_index++) {
                $cell = $row[$column_index];
                if ($cell->column_id === HTML::MATCH_COLUMN_DATE) {
                    $new_cells = [];
                    for ($i = 0; $i < count($table->headings); $i++) {
                        array_push($new_cells, HTML::genTableCell(HTML::MATCH_COLUMN_BLANK, '', '&nbsp;'));
                    }
                    array_splice($table->rows, $row_index, 0, [$new_cells]);
                    $row_index += $cell->rowspan??1;
                    break;
                }
            }
        }

        // Go column by column looking ahead in each row to see how many rows to merge
        $cells_column_index = 0;
        for ($headings_column_index = 0; $headings_column_index < count($table->headings); $headings_column_index++) {
            if ($table->headings[$headings_column_index]->column_id !== HTML::MATCH_COLUMN_DATE && in_array($table->headings[$headings_column_index]->column_id, $config->merge)) {
                for ($row_index = 0; $row_index < count($table->rows); $row_index++) {
                    if (count($table->rows[$row_index]) <= $cells_column_index) {
                        continue; // skip "break" rows
                    }
                    $cell = $table->rows[$row_index][$cells_column_index];
                    $row_look_ahead = $row_index + 1;
                    while ($row_look_ahead < count($table->rows) &&
                        $cell->text !== '' &&
                        $cell->column_id === $table->headings[$headings_column_index]->column_id &&
                        isset($table->rows[$row_look_ahead][$cells_column_index]) &&
                        $cell->text === $table->rows[$row_look_ahead][$cells_column_index]->text)
                    {
                        $row_look_ahead++;
                    }
                    if ($row_look_ahead > $row_index + 1) {
                        $rowspan = $row_look_ahead - $row_index;
                        $table->rows[$row_index][$cells_column_index]->rowspan = $rowspan;
                        for ($row_delete_ahead = $row_index + 1; $row_delete_ahead < $row_look_ahead; $row_delete_ahead++) {
                            $table->rows[$row_delete_ahead][$cells_column_index]->delete = true;
                        }
                        $row_index += $rowspan - 1;
                    }
                }
            }

            $cells_column_index += property_exists($table->headings[$headings_column_index], 'rowspan') ? $table->headings[$headings_column_index]->rowspan : 1;
        }

        // go cell by cell looking for cells to delete
        // $cells_column_count = count($table->row[0]);
        for ($row_index = 0; $row_index < count($table->rows); $row_index++) {
            $table->rows[$row_index] = array_values(array_filter($table->rows[$row_index], fn($r): bool => !(property_exists($r, 'delete') && $r->delete)));
        }

        return $table;
    }

    /**
     * Generates a standard HTML table to represent the league table for a League group, along with a description of
     * how the league positions are determined
     *
     * The $config object can be used to configure the league table data as described below:
     * <pre>{
     *   headings: []      - the list of headings (using the HTML::LEAGUE_COLUMN_* constants) in the desired order
     *   headingMap: {}    - associative array where the key is the heading name (using the HTML::LEAGUE_COLUMN_* constants),
     *                       and the value is the new text to use for that heading
     * }
     * </pre>
     *
     * @param League $league The Knockout group to present
     * @param ?object $config The configuration for generating the league table data (default: null)
     * @param ?string $team_id The ID for a team to decorate as "this" team (default: null)
     *
     * @return string string The generated HTML for league table
     */
    public static function getLeagueTableHTML(League $league, object $config = null, string $team_id = null) : string
    {
        $config = HTML::enrichLeagueConfig($league->getLeagueTable(), $config);
        $league_table = HTML::getLeagueTableForHTML($league, $config, $team_id);
        $body = '<table class="'.$league_table->class.'"><tr>';
        foreach ($league_table->headings as $heading) {
            $body .= '<th class="'.$heading->class.'">'.$heading->text.'</th>';
        }
        $body .= '</tr>';
        foreach ($league_table->rows as $row) {
            $body .= '<tr>';
            foreach ($row as $cell) {
                $body .= '<td class="'.$cell->class.'">'.$cell->text.'</td>';
            }
            $body .= '</tr>';
        }
        $body .= '</table>';

        $body .= '<p>'.$league->getLeagueTable()->getOrderingText().'</p>';
        return $body;
    }

    /**
     * Generates the logical data for presenting the league table for a League group.  This can be used to draw a table
     * in a way where the caller can manipulate the data (for example injecting their own columns/rows or any other data).
     * If you want a standard HTML table then call getLeagueTableHTML().
     *
     * The $config object can be used to configure the league table data as described below:
     * <pre>{
     *   headings: []      - the list of headings (using the HTML::LEAGUE_COLUMN_* constants) in the desired order
     *   headingMap: {}    - associative array where the key is the heading name (using the HTML::LEAGUE_COLUMN_* constants),
     *                       and the value is the new text to use for that heading
     * }
     * </pre>
     *
     * The data returned is an object of the form:
     * <pre>{
     *   class: "vbc-league-table vbc-league-table-group-{group ID}" - a CSS class name for the table
     *   headings: [                       - an array of heading entities, representing a table's row of headings
     *     {
     *       column_id: "xxx"              - an ID for this heading entity.  This will be one of the values defined by the
     *                                       following list and, unless changed by $config, in the following order.  Note
     *                                       that some headings are only present if applicable (e.g. draws is only shown if
     *                                       draws are allowed, and sets for/against/difference is only shown if matches
     *                                       are played to sets):
     *                                          HTML::LEAGUE_COLUMN_POSITION
     *                                          HTML::LEAGUE_COLUMN_TEAM
     *                                          HTML::LEAGUE_COLUMN_PLAYED
     *                                          HTML::LEAGUE_COLUMN_WINS
     *                                          HTML::LEAGUE_COLUMN_LOSSES
     *                                          HTML::LEAGUE_COLUMN_DRAWS
     *                                          HTML::LEAGUE_COLUMN_SETS_FOR
     *                                          HTML::LEAGUE_COLUMN_SETS_AGAINST
     *                                          HTML::LEAGUE_COLUMN_SETS_DIFFERENCE
     *                                          HTML::LEAGUE_COLUMN_POINTS_FOR
     *                                          HTML::LEAGUE_COLUMN_POINTS_AGAINST
     *                                          HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE
     *                                          HTML::LEAGUE_COLUMN_LEAGUE_POINTS
     *
     *       class: "vbc-league-table-xxx vbc-league-table-group-{group ID}" - a CSS class list for this heading, where the
     *                                       "xxx" suffix is the column_id.
     *
     *       text: "Xxx"                   - the text for this heading
     *     },
     *     {...next heading's data...}
     *   ],
     *   rows: [                             - an array of data rows, like a table row
     *     [                                 - an array of data entities, like a table data cell
     *       {
     *         column_id: "xxx"              - an ID for this data cell entity.  As with the headings, this is a string matching
     *                                         one of the constants HTML::LEAGUE_COLUMN_*
     *         class: "vbc-{group-type}-xxx" - a CSS class list for this cell, where the group-type is one of league|knockout|crossover
     *                                         and the "xxx" suffix is the column_id.  Note that this will include "vbc-league-table-num"
     *                                         if the value is a number
     *         text: "xxx"                   - the text for this data cell, typically the data for this league table entry
     *       },
     *       {...next cell's data...}
     *     ],
     *     [...next row's data...]
     *   ]
     * }
     * </pre>
     *
     * @param League $league The League group to present
     * @param ?object $config The configuration for generating the league table data (default: null)
     * @param ?string $team_id The ID for a team to decorate as "this" team (default: null)
     *
     * @return object An object containing the league table information suitable for generating HTML from
     */
    public static function getLeagueTableForHTML(League $league, object $config = null, string $team_id = null) : object
    {
        $config = HTML::enrichLeagueConfig($league->getLeagueTable(), $config);
        $table = new stdClass();

        $table->class = 'vbc-league-table vbc-league-table-group-'.$league->getID();
        $table->headings = HTML::generateLeagueTableHeadings($league->getLeagueTable(), $config);
        $table->rows = [];
        foreach ($league->getLeagueTable()->entries as $pos => $league_entry) {
            $position = $pos + 1;
            $this_team = $team_id !== null
                      && $team_id !== CompetitionTeam::UNKNOWN_TEAM_ID
                      && $league->getCompetition()->getTeamByID($league_entry->getTeamID())->getID() === $league->getCompetition()->getTeamByID($team_id)->getID();
            $table->rows[$pos] = HTML::generateLeagueTableRow($config, $league_entry, $position, $this_team);
        }

        return $table;
    }

    /**
     * Generates a standard HTML table to represent the final standings in a Knockout group
     *
     * @param Knockout $knockout The Knockout group to present
     * @param ?string $team_id The ID for a team to decorate as "this" team (default: null)
     *
     * @return string the HTML table as a string
     */
    public static function getFinalStandingHTML(Knockout $knockout, string $team_id = null) : string
    {
        if ($knockout->getKnockoutConfig() === null) {
            return '';
        }
        $final_standing_table = HTML::getFinalStandingForHTML($knockout, $team_id);
        $body = '<table class="'.$final_standing_table->class.'"><tr>';
        foreach ($final_standing_table->headings as $heading) {
            $body .= '<th class="'.$heading->class.'">'.$heading->text.'</th>';
        }
        $body .= '</tr>';
        foreach ($final_standing_table->rows as $row) {
            $body .= '<tr>';
            foreach ($row as $cell) {
                $body .= '<td class="'.$cell->class.'">'.$cell->text.'</td>';
            }
            $body .= '</tr>';
        }
        $body .= '</table>';

        return $body;
    }

    /**
     * Generates the logical data for presenting the standings for a Knockout group.  This can be used to draw a table
     * in a way where the caller can manipulate the data (for example injecting their own columns/rows or any other data).
     * If you want a standard HTML table then call getFinalStandingHTML().
     *
     * The data returned is an object of the form:
     * <pre>{
     *   class: "vbc-knockout"            - a CSS class name for the table
     *   headings: [                      - an array of heading entities, representing a table's row of headings
     *     {
     *       column_id: "pos"             - an ID for this heading entity
     *       class: "vbc-knockout-pos"    - a CSS class for this heading
     *       text: "Pos"                  - the text for this entity
     *     },
     *     {
     *       column_id: "team"
     *       class: "vbc-knockout-team"
     *       text: "Team"
     *     }
     *   ],
     *   rows: [                          - an array of data rows, like a table row
     *     [                              - an array of data entities, like a table data cell
     *       {
     *         column_id: "pos"           - an ID for this data cell entity
     *         class: "vbc-knockout-pos"  - a CSS class for this cell
     *         text: "1st"                - the text for this data cell, as defined in the Knockout standings configuration in the competition JSON data
     *       },
     *       {
     *         column_id: "team"
     *         class: "vbc-knockout-team" - a CSS class for this cell, which includes "vbc-this-team" if the team's ID matches $team_id
     *         text: "Team Name"          - The team name for this standing row
     *       }
     *     ],
     *     [...next row's data...]
     *   ]
     *
     * }
     * </pre>
     *
     * @param Knockout $knockout The Knockout group to present
     * @param ?string $team_id The ID for a team to decorate as "this" team (default: null)
     *
     * @return object An object containing the final standing information suitable for generating HTML from
     */
    public static function getFinalStandingForHTML(Knockout $knockout, string $team_id = null) : object
    {
        $table = new stdClass();

        $table->class = 'vbc-knockout';
        $table->headings = [];
        array_push($table->headings, HTML::genTableCell('pos', 'vbc-knockout-pos', 'Pos'));
        array_push($table->headings, HTML::genTableCell('team', 'vbc-knockout-team', 'Team'));

        $table->rows = [];
        if ($knockout->getKnockoutConfig() !== null) {
            foreach ($knockout->getKnockoutConfig()->getStanding() as $standing_info) {
                $this_team = $team_id !== null
                          && $team_id !== CompetitionTeam::UNKNOWN_TEAM_ID
                          && $knockout->getCompetition()->getTeamByID($standing_info->id) === $knockout->getCompetition()->getTeamByID($team_id);
                $cells = [];
                array_push($cells, HTML::genTableCell('pos', 'vbc-knockout-pos', $standing_info->position));
                array_push($cells, HTML::genTableCell('team', 'vbc-knockout-team'.($this_team ? ' vbc-this-team' : ''), $knockout->getCompetition()->getTeamByID($standing_info->id)->getName()));
                array_push($table->rows, $cells);
            }
        }
        return $table;
    }
}
