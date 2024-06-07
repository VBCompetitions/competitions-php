<?php

namespace VBCompetitions\Competitions;

use Exception;

/**
 * A group within this stage of the competition.  Leagues expect all teams to play each other at least once, and have a league table
 */
final class League extends Group
{
    /** @var LeagueTable The table for this group, if the group type is league */
    private LeagueTable $table;

    /**
     * Constructs a new League instance.
     *
     * @param Stage $stage A link back to the Stage this Group is in
     * @param string $id The unique ID of this Group
     * @param MatchType $match_type Whether matches are continuous or played to sets
     * @param bool $draws_allowed Indicates whether draws are allowed in matches
     */
    function __construct(Stage $stage, string $id, MatchType $match_type, bool $draws_allowed)
    {
        parent::__construct($stage, $id, $match_type);
        $this->type = GroupType::LEAGUE;
        $this->draws_allowed = $draws_allowed;
    }

    /**
     * Processes matches to update the league table.
     *
     * @return void
     */
    public function processMatches() : void
    {
        if ($this->matches_processed) {
            return;
        }
        $this->matches_processed = true;

        $this->table = new LeagueTable($this);

        $team_results = array();

        foreach($this->matches as $match) {
            if ($match instanceof GroupBreak || $match->isFriendly()) {
                continue;
            }
            $home_team_id = $match->getHomeTeam()->getID();
            $away_team_id = $match->getAwayTeam()->getID();

            if (!array_key_exists($home_team_id, $team_results)) {
                $team_results[$home_team_id] = new LeagueTableEntry($this, $home_team_id, $this->competition->getTeamByID($home_team_id)->getName());
            }

            if (!array_key_exists($away_team_id, $team_results)) {
                $team_results[$away_team_id] = new LeagueTableEntry($this, $away_team_id, $this->competition->getTeamByID($away_team_id)->getName());
            }

            if ($match->isComplete()) {
                // Handle draws
                if (!$match->isDraw()) {
                    $team_results[$match->getWinnerTeamID()]->setWins($team_results[$match->getWinnerTeamID()]->getWins() + 1);
                    $team_results[$match->getLoserTeamID()]->setLosses($team_results[$match->getLoserTeamID()]->getLosses() + 1);

                    if (!property_exists($team_results[$match->getWinnerTeamID()]->getH2H(), $match->getLoserTeamID())) {
                        $team_results[$match->getWinnerTeamID()]->getH2H()->{$match->getLoserTeamID()} = 1;
                    } else {
                        $team_results[$match->getWinnerTeamID()]->getH2H()->{$match->getLoserTeamID()}++;
                    }
                    if (!property_exists($team_results[$match->getLoserTeamID()]->getH2H(), $match->getWinnerTeamID())) {
                        $team_results[$match->getLoserTeamID()]->getH2H()->{$match->getWinnerTeamID()} = -1;
                    } else {
                        $team_results[$match->getLoserTeamID()]->getH2H()->{$match->getWinnerTeamID()}--;
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
                        } else if ($match->getHomeTeam()->getScores()[$i] < $match->getAwayTeam()->getScores()[$i]) {
                            $away_team_sets++;
                        }
                    }
                    $team_results[$home_team_id]->setSF($team_results[$home_team_id]->getSF() + $home_team_sets);
                    $team_results[$home_team_id]->setSA($team_results[$home_team_id]->getSA() + $away_team_sets);
                    $team_results[$away_team_id]->setSF($team_results[$away_team_id]->getSF() + $away_team_sets);
                    $team_results[$away_team_id]->setSA($team_results[$away_team_id]->getSA() + $home_team_sets);

                    $team_results[$home_team_id]->setPTS($team_results[$home_team_id]->getPTS() + ($this->league_config->getPoints()->getPerSet() * $home_team_sets));
                    $team_results[$away_team_id]->setPTS($team_results[$away_team_id]->getPTS() + ($this->league_config->getPoints()->getPerSet() * $away_team_sets));
                    if ($match->isDraw()) {
                        $team_results[$home_team_id]->setDraws($team_results[$home_team_id]->getDraws() + 1);
                        $team_results[$away_team_id]->setDraws($team_results[$away_team_id]->getDraws() + 1);
                    } else {
                        if (abs($home_team_sets - $away_team_sets) === 1) {
                            $team_results[$match->getWinnerTeamID()]->setPTS($team_results[$match->getWinnerTeamID()]->getPTS() + $this->league_config->getPoints()->getWinByOne());
                            $team_results[$match->getLoserTeamID()]->setPTS($team_results[$match->getLoserTeamID()]->getPTS() + $this->league_config->getPoints()->getLoseByOne());
                        } else {
                            $team_results[$match->getWinnerTeamID()]->setPTS($team_results[$match->getWinnerTeamID()]->getPTS() + $this->league_config->getPoints()->getWin());
                            $team_results[$match->getLoserTeamID()]->setPTS($team_results[$match->getLoserTeamID()]->getPTS() + $this->league_config->getPoints()->getLose());
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
                        $team_results[$match->getWinnerTeamID()]->setPTS($team_results[$match->getWinnerTeamID()]->getPTS() + $this->league_config->getPoints()->getWin());
                        $team_results[$match->getLoserTeamID()]->setPTS($team_results[$match->getLoserTeamID()]->getPTS() + $this->league_config->getPoints()->getLose());
                    }
                }

                if ($match->getHomeTeam()->getForfeit()) {
                    $team_results[$home_team_id]->setPTS($team_results[$home_team_id]->getPTS() - $this->league_config->getPoints()->getForfeit());
                }
                if ($match->getAwayTeam()->getForfeit()) {
                    $team_results[$away_team_id]->setPTS($team_results[$away_team_id]->getPTS() - $this->league_config->getPoints()->getForfeit());
                }
                $team_results[$home_team_id]->setBP($team_results[$home_team_id]->getBP() + $match->getHomeTeam()->getBonusPoints());
                $team_results[$home_team_id]->setPP($team_results[$home_team_id]->getPP() + $match->getHomeTeam()->getPenaltyPoints());

                $team_results[$away_team_id]->setBP($team_results[$away_team_id]->getBP() + $match->getAwayTeam()->getBonusPoints());
                $team_results[$away_team_id]->setPP($team_results[$away_team_id]->getPP() + $match->getAwayTeam()->getPenaltyPoints());
            }
        }

        foreach ($team_results as $team_line) {
            $team_line->setPD($team_line->getPF() - $team_line->getPA());
            $team_line->setSD($team_line->getSF() - $team_line->getSA());
            $team_line->setPTS($team_line->getPTS() + ($team_line->getPlayed() * $this->league_config->getPoints()->getPlayed()) + $team_line->getBP() - $team_line->getPP());
            array_push($this->table->entries, $team_line);
        }

        usort($this->table->entries, [League::class, 'sortLeagueTable']);
    }

    /**
     * Sorts the league table entries based on the configured ordering.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the second.
     */
    private function sortLeagueTable(LeagueTableEntry $a, LeagueTableEntry $b) : int
    {
        $ordering = $this->league_config->getOrdering();
        for ($i = 0; $i < count($ordering); $i++) {
            $compare_result = 0;
            switch ($ordering[$i]) {
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
                case 'BP':
                    $compare_result = League::compareBonusPoints($a, $b);
                    break;
                case 'PP':
                    $compare_result = League::comparePenaltyPoints($a, $b);
                    break;
            }
            if ($compare_result !== 0) {
                return $compare_result;
            }
        }
        return League::compareTeamName($a, $b);
    }

    /**
     * Compares two LeagueTableEntry objects based on their team names.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareTeamName(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return strcmp($a->getTeam(), $b->getTeam());
    }

    /**
     * Compares two LeagueTableEntry objects based on their league points.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareLeaguePoints(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getPTS() - $a->getPTS();
    }

    /**
     * Compares two LeagueTableEntry objects based on their wins.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareWins(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getWins() - $a->getWins();
    }

    /**
     * Compares two LeagueTableEntry objects based on their losses.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareLosses(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getLosses() - $a->getLosses();
    }

    /**
     * Compares two LeagueTableEntry objects based on their head to head record.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareHeadToHead(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        if (!property_exists($b->getH2H(), $a->getTeamID()) || !property_exists($a->getH2H(), $b->getTeamID())) {
            return 0;
        }
        return $b->getH2H()->{$a->getTeamID()} - $a->getH2H()->{$b->getTeamID()};
    }

    /**
     * Compares two LeagueTableEntry objects based on their points scored.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function comparePointsFor(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getPF() - $a->getPF();
    }

    /**
     * Compares two LeagueTableEntry objects based on their points conceded.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function comparePointsAgainst(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $a->getPA() - $b->getPA();
    }

    /**
     * Compares two LeagueTableEntry objects based on their points difference.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function comparePointsDifference(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getPD() - $a->getPD();
    }

    /**
     * Compares two LeagueTableEntry objects based on their sets won.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareSetsFor(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getSF() - $a->getSF();
    }

    /**
     * Compares two LeagueTableEntry objects based on their sets lost.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareSetsAgainst(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $a->getSA() - $b->getSA();
    }

    /**
     * Compares two LeagueTableEntry objects based on their set difference.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareSetsDifference(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getSD() - $a->getSD();
    }

    /**
     * Compares two LeagueTableEntry objects based on their bonus points.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function compareBonusPoints(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $b->getBP() - $a->getBP();
    }

    /**
     * Compares two LeagueTableEntry objects based on their penalty points.
     *
     * @param LeagueTableEntry $a The first league table entry to compare
     * @param LeagueTableEntry $b The second league table entry to compare
     * @return int Returns an integer less than, equal to, or greater than zero to be used by the sort function
     */
    private static function comparePenaltyPoints(LeagueTableEntry $a, LeagueTableEntry $b)
    {
        return $a->getPP() - $b->getPP();
    }

    /**
     * Gets the league table for this group.
     *
     * @throws Exception If the league table cannot be retrieved
     * @return LeagueTable The league table
     */
    public function getLeagueTable() : LeagueTable
    {
        $this->processMatches();
        return $this->table;
    }

    /**
     * Returns the configuration object for the league.
     *
     * @return LeagueConfig The league configuration object
     */
    public function getLeagueConfig() : LeagueConfig
    {
        return $this->league_config;
    }

    /**
     * Gets the team by ID based on the type of entity.
     *
     * @param string $type The type part of the team reference ('MATCH-ID' or 'league')
     * @param string $entity The entity (e.g., 'winner' or 'loser')
     * @return CompetitionTeam The CompetitionTeam instance
     * @throws Exception If the entity is invalid
     */
    public function getTeamByID(string $type, string $entity) : CompetitionTeam
    {
        if ($type === 'league') {
            $this->processMatches();
            if (!$this->isComplete()) {
                throw new Exception('Cannot get the team in a league position on an incomplete league');
            }
            if ((int)$entity > count($this->table->entries)) {
                throw new Exception('Invalid League position: position is bigger than the number of teams');
            }
            return $this->competition->getTeamByID($this->table->entries[(int)$entity-1]->getTeamID());
        }

        $match = $this->getMatchByID($type);

        return match ($entity) {
            'winner' => $this->competition->getTeamByID($match->getWinnerTeamID()),
            'loser' => $this->competition->getTeamByID($match->getLoserTeamID()),
            default => throw new Exception('Invalid entity "'.$entity.'" in team reference'),
        };
    }
}
