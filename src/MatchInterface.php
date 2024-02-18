<?php

namespace VBCompetitions\Competitions;

interface MatchInterface {
    public function isComplete() : bool;
    public function isDraw() : bool;
    public function getCourt() : ?string;
    public function getVenue() : ?string;
    public function getDate() : ?string;
    public function getWarmup() : ?string;
    public function getDuration() : ?string;
    public function getStart() : ?string;
    public function getManager() : ?MatchManager;
    public function getMVP() : ?string;
    public function getNotes() : ?string;
    public function getOfficials() : ?MatchOfficials;
    public function hasOfficials() : bool;
    public function getID() : string;
    public function getWinnerTeamId() : string;
    public function getLoserTeamId() : string;
    /**
     * @return array<int>
     */
    public function getHomeTeamScores() : array;
    /**
     * @return array<int>
     */
    public function getAwayTeamScores() : array;
    public function getHomeTeamSets() : int;
    public function getAwayTeamSets() : int;
    public function getGroup() : Group|IfUnknown;
    public function getAwayTeam() : MatchTeam;
    public function getHomeTeam() : MatchTeam;

    /**
     * Set the scores for this match
     *
     * @param array<int> $home_team_scores The score array for the home team
     * @param array<int> $away_team_scores The score array for the away team
     * @param bool $complete Whether the match is complete or not (required for continuous scoring matches)
     */
    public function setScores(array $home_team_scores, array $away_team_scores, ?bool $complete = null) : void;
}
