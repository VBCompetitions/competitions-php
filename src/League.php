<?php

namespace VBCompetitions\Competitions;

use Exception;
use stdClass;

/**
 * A group within this stage of the competition.  Leagues expect all teams to play each other at least once, and have a league table
 */
final class League extends Group
{
    /** Configuration for the league */
    protected object $league;

    /** The table for this group, if the group type is league */
    private LeagueTable $table;

    /** Whether this group is complete, i.e. have all matches been played */
    protected bool $is_complete = false;

    /**
     * Contains the group data of a stage, creating any metadata needed
     *
     * @param Stage $stage A link back to the Stage this Group is in
     * @param object $league_data The data defining this Group
     */
    function __construct(Stage $stage, object $league_data)
    {
        parent::__construct($stage, $league_data);

        $this->draws_allowed = $league_data->drawsAllowed;

        $this->league = new stdClass();
        $this->league->ordering = $league_data->league->ordering;
        $this->league->points = $league_data->league->points;

        $this->processMatches();
    }

    /**
     * Summary of calculateLeagueTable
     * @return void
     */
    protected function processMatches() : void
    {
        parent::processMatches();

        $this->table = new LeagueTable($this);

        $team_results = array();

        $completed_matches = 0;
        $matches_in_this_pool = 0;

        foreach($this->matches as $match) {
            if ($match instanceof GroupBreak) {
                continue;
            }

            $matches_in_this_pool++;
            $home_team_id = $match->getHomeTeam()->getID();
            $away_team_id = $match->getAwayTeam()->getID();

            if (!array_key_exists($home_team_id, $team_results)) {
                $team_results[$home_team_id] = new LeagueTableEntry($this, $home_team_id, $this->competition->getTeamByID($home_team_id)->getName());
            }

            if (!array_key_exists($away_team_id, $team_results)) {
                $team_results[$away_team_id] = new LeagueTableEntry($this, $away_team_id, $this->competition->getTeamByID($away_team_id)->getName());
            }

            if ($match->isComplete()) {
                $completed_matches++;

                // Handle draws
                if (!$match->isDraw()) {
                    $team_results[$match->getWinnerTeamId()]->setWins($team_results[$match->getWinnerTeamId()]->getWins() + 1);
                    $team_results[$match->getLoserTeamId()]->setLosses($team_results[$match->getLoserTeamId()]->getLosses() + 1);

                    if (!property_exists($team_results[$match->getWinnerTeamId()]->getH2H(), $match->getLoserTeamId())) {
                        $team_results[$match->getWinnerTeamId()]->getH2H()->{$match->getLoserTeamId()} = 1;
                    } else {
                        $team_results[$match->getWinnerTeamId()]->getH2H()->{$match->getLoserTeamId()}++;
                    }
                    if (!property_exists($team_results[$match->getLoserTeamId()]->getH2H(), $match->getWinnerTeamId())) {
                        $team_results[$match->getLoserTeamId()]->getH2H()->{$match->getWinnerTeamId()} = 0;
                    } else {
                        $team_results[$match->getLoserTeamId()]->getH2H()->{$match->getWinnerTeamId()}--;
                    }
                } else {
                    if (!property_exists($team_results[$match->getHomeTeam()->getID()]->getH2H(), $match->getAwayTeam()->getID())) {
                        $team_results[$match->getHomeTeam()->getID()]->getH2H()->{$match->getAwayTeam()->getID()} = 0;
                    }
                    if (!property_exists($team_results[$match->getAwayTeam()->getID()]->getH2H(), $match->getHomeTeam()->getID())) {
                        $team_results[$match->getAwayTeam()->getID()]->getH2H()->{$match->getHomeTeam()->getID()} = 0;
                    }
                }

                $team_results[$home_team_id]->setPlayed($team_results[$home_team_id]->getPlayed() + 1);
                $team_results[$away_team_id]->setPlayed($team_results[$away_team_id]->getPlayed() + 1);

                if ($this->table->hasSets()) {
                    $home_team_sets = 0;
                    $away_team_sets = 0;
                    for ($i=0; $i < count($match->getHomeTeam()->getScores()); $i++) {
                        if ($match->getHomeTeam()->getScores()[$i] < $this->sets->getMinPoints() && $match->getAwayTeam()->getScores()[$i] < $this->sets->getMinPoints()) {
                            continue;
                        }
                        $team_results[$home_team_id]->setPF($team_results[$home_team_id]->getPF() + $match->getHomeTeam()->getScores()[$i]);
                        $team_results[$home_team_id]->setPA($team_results[$home_team_id]->getPA() + $match->getAwayTeam()->getScores()[$i]);

                        $team_results[$away_team_id]->setPF($team_results[$away_team_id]->getPF() + $match->getAwayTeam()->getScores()[$i]);
                        $team_results[$away_team_id]->setPA($team_results[$away_team_id]->getPA() + $match->getHomeTeam()->getScores()[$i]);

                        if ($match->getHomeTeam()->getScores()[$i] > $match->getAwayTeam()->getScores()[$i]) {
                            $home_team_sets++;
                        } else {
                            $away_team_sets++;
                        }
                    }
                    $team_results[$home_team_id]->setSF($team_results[$home_team_id]->getSF() + $home_team_sets);
                    $team_results[$home_team_id]->setSA($team_results[$home_team_id]->getSA() + $away_team_sets);
                    $team_results[$away_team_id]->setSF($team_results[$away_team_id]->getSF() + $away_team_sets);
                    $team_results[$away_team_id]->setSA($team_results[$away_team_id]->getSA() + $home_team_sets);

                    $team_results[$home_team_id]->setPTS($team_results[$home_team_id]->getPTS() + ($this->league->points->perSet * $home_team_sets));
                    $team_results[$away_team_id]->setPTS($team_results[$away_team_id]->getPTS() + ($this->league->points->perSet * $away_team_sets));
                    if ($match->isDraw()) {
                        $team_results[$home_team_id]->setDraws($team_results[$home_team_id]->getDraws() + 1);
                        $team_results[$away_team_id]->setDraws($team_results[$away_team_id]->getDraws() + 1);
                    } else {
                        if (abs($home_team_sets - $away_team_sets) === 1) {
                            $team_results[$match->getWinnerTeamId()]->setPTS($team_results[$match->getWinnerTeamId()]->getPTS() + $this->league->points->winByOne);
                            $team_results[$match->getLoserTeamId()]->setPTS($team_results[$match->getLoserTeamId()]->getPTS() + $this->league->points->loseByOne);
                        } else {
                            $team_results[$match->getWinnerTeamId()]->setPTS($team_results[$match->getWinnerTeamId()]->getPTS() + $this->league->points->win);
                            $team_results[$match->getLoserTeamId()]->setPTS($team_results[$match->getLoserTeamId()]->getPTS() + $this->league->points->lose);
                        }
                    }
                } else {
                    $team_results[$home_team_id]->setPF($team_results[$home_team_id]->getPF() + $match->getHomeTeam()->getScores()[0]);
                    $team_results[$home_team_id]->setPA($team_results[$home_team_id]->getPA() + $match->getAwayTeam()->getScores()[0]);

                    $team_results[$away_team_id]->setPF($team_results[$away_team_id]->getPF() + $match->getAwayTeam()->getScores()[0]);
                    $team_results[$away_team_id]->setPA($team_results[$away_team_id]->getPA() + $match->getHomeTeam()->getScores()[0]);
                    if ($match->isDraw()) {
                        $team_results[$home_team_id]->setDraws($team_results[$home_team_id]->getDraws() + 1);
                        $team_results[$away_team_id]->setDraws($team_results[$away_team_id]->getDraws() + 1);
                    } else {
                        $team_results[$match->getWinnerTeamId()]->setPTS($team_results[$match->getWinnerTeamId()]->getPTS() + $this->league->points->win);
                        $team_results[$match->getLoserTeamId()]->setPTS($team_results[$match->getLoserTeamId()]->getPTS() + $this->league->points->lose);
                    }
                }

                if ($match->getHomeTeam()->getForfeit()) {
                    $team_results[$home_team_id]->setPTS($team_results[$home_team_id]->getPTS() - $this->league->points->forfeit);
                }
                if ($match->getAwayTeam()->getForfeit()) {
                    $team_results[$away_team_id]->setPTS($team_results[$away_team_id]->getPTS() - $this->league->points->forfeit);
                }
                $team_results[$home_team_id]->setPTS($team_results[$home_team_id]->getPTS() + $match->getHomeTeam()->getBonusPoints());
                $team_results[$home_team_id]->setPTS($team_results[$home_team_id]->getPTS() - $match->getHomeTeam()->getPenaltyPoints());
                $team_results[$away_team_id]->setPTS($team_results[$away_team_id]->getPTS() + $match->getAwayTeam()->getBonusPoints());
                $team_results[$away_team_id]->setPTS($team_results[$away_team_id]->getPTS() - $match->getAwayTeam()->getPenaltyPoints());
            }
        }

        foreach ($team_results as $team_line) {
            $team_line->setPD($team_line->getPF() - $team_line->getPA());
            $team_line->setSD($team_line->getSF() - $team_line->getSA());
            $team_line->setPTS($team_line->getPTS() + ($team_line->getPlayed() * $this->league->points->played));
            array_push($this->table->entries, $team_line);
        }

        $this->is_complete = $completed_matches === $matches_in_this_pool;

        usort($this->table->entries, [League::class, 'sortLeagueTable']);

        if ($this->is_complete) {
            for ($i = 0; $i < count($this->table->entries); $i++) {
                $this->competition->addTeamReference(
                    $this->stage->getID().':'.$this->id.':league:'.($i+1),
                    $this->competition->getTeamByID($this->table->entries[$i]->getTeamID())
                );
            }
        }
    }

    /**
     * Summary of sortLeagueTable
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private function sortLeagueTable(LeagueTableEntry $a, LeagueTableEntry $b) : int
    {
        for ($i = 0; $i < count($this->league->ordering); $i++) {
            $compare_result = 0;
            switch ($this->league->ordering[$i]) {
                case 'PTS':
                    $compare_result = League::compareLeaguePoints($a, $b);
                    break;
                case 'WINS':
                    $compare_result = League::compareWins($a, $b);
                    break;
                case 'LOSSES':
                    $compare_result = League::compareLosses($a, $b);
                    break;
                case 'H2H':
                    $compare_result = League::compareHeadToHead($a, $b);
                    break;
                case 'PF':
                    $compare_result = League::comparePointsFor($a, $b);
                    break;
                case 'PA':
                    $compare_result = League::comparePointsAgainst($a, $b);
                    break;
                case 'PD':
                    $compare_result = League::comparePointsDifference($a, $b);
                    break;
                case 'SF':
                    $compare_result = League::compareSetsFor($a, $b);
                    break;
                case 'SA':
                    $compare_result = League::compareSetsAgainst($a, $b);
                    break;
                case 'SD':
                    $compare_result = League::compareSetsDifference($a, $b);
                    break;
            }
            if ($compare_result !== 0) {
                return $compare_result;
            }
        }
        return League::compareTeamName($a, $b);
    }

    /**
     * Summary of compareTeamName
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function compareTeamName(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return strcmp($a->getTeam(), $b->getTeam());
    }

    /**
     * Summary of compareLeaguePoints
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function compareLeaguePoints(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getPTS() - $a->getPTS();
    }

    /**
     * Summary of compareWins
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function compareWins(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getWins() - $a->getWins();
    }

    /**
     * Summary of compareLosses
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function compareLosses(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getLosses() - $a->getLosses();
    }

    /**
     * Summary of compareHeadToHead
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return float|int
     */
    private static function compareHeadToHead(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        if (!property_exists($b->getH2H(), $a->getTeamID()) || !property_exists($a->getH2H(), $b->getTeamID())) {
            return 0;
        }
        return $b->getH2H()->{$a->getTeamID()} - $a->getH2H()->{$b->getTeamID()};
    }

    /**
     * Summary of comparePointsFor
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function comparePointsFor(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getPF() - $a->getPF();
    }

    /**
     * Summary of comparePointsAgainst
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function comparePointsAgainst(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $a->getPA() - $b->getPA();
    }

    /**
     * Summary of comparePointsDifference
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function comparePointsDifference(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getPD() - $a->getPD();
    }

    /**
     * Summary of compareSetsFor
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function compareSetsFor(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getSF() - $a->getSF();
    }

    /**
     * Summary of compareSetsAgainst
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function compareSetsAgainst(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $a->getSA() - $b->getSA();
    }

    /**
     * Summary of compareSetsDifference
     * @param LeagueTableEntry $a
     * @param LeagueTableEntry $b
     * @return int
     */
    private static function compareSetsDifference(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getSD() - $a->getSD();
    }

    /**
     * Summary of getLeagueTable
     * @throws Exception
     * @return LeagueTable
     */
    public function getLeagueTable() : LeagueTable
    {
        return $this->table;
    }

    /**
     * Returns whether the League group is complete, i.e. all matches in the group are complete.
     *
     * @return bool whether the League group is complete
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
        return GroupType::LEAGUE;
    }

    /**
     * Return the config object for the league, containing the ordering config and the league pints config
     *
     * @return object the league config
     */
    public function getLeagueConfig() : object
    {
        return $this->league;
    }
}
