<?php

namespace VBCompetitions\Competitions;

use stdClass;

final class LeagueTableEntry
{
    private string $team_id;
    private string $team;
    private int $played = 0;
    private int $wins = 0;
    private int $losses = 0;
    private int $draws = 0;
    private int $sf = 0;
    private int $sa = 0;
    private int $sd = 0;
    private int $pf = 0;
    private int $pa = 0;
    private int $pd = 0;
    private int $bp = 0;
    private int $pp = 0;
    private int $pts = 0;
    private object $head;

    private League $league;

    function __construct(League $league, string $team_id, string $name)
    {
        $this->league = $league;
        $this->team_id = $team_id;
        $this->team = $name;
        $this->head = new stdClass();
    }

    public function getGroupID() : string
    {
        return $this->league->getID();
    }

    public function getTeamID() : string
    {
        return $this->team_id;
    }

    public function getTeam() : string
    {
        return $this->team;
    }

    public function getPlayed() : int
    {
        return $this->played;
    }

    public function setPlayed(int $played) : void
    {
        $this->played = $played;
    }

    public function getWins() : int
    {
        return $this->wins;
    }

    public function setWins(int $wins) : void
    {
        $this->wins = $wins;
    }

    public function getLosses() : int
    {
        return $this->losses;
    }

    public function setLosses(int $losses) : void
    {
        $this->losses = $losses;
    }

    public function getDraws() : int
    {
        return $this->draws;
    }

    public function setDraws(int $draws) : void
    {
        $this->draws = $draws;
    }

    public function getSF() : int
    {
        return $this->sf;
    }

    public function setSF(int $sf) : void
    {
        $this->sf = $sf;
    }

    public function getSA() : int
    {
        return $this->sa;
    }

    public function setSA(int $sa) : void
    {
        $this->sa = $sa;
    }

    public function getSD() : int
    {
        return $this->sd;
    }

    public function setSD(int $sd) : void
    {
        $this->sd = $sd;
    }

    public function getPF() : int
    {
        return $this->pf;
    }

    public function setPF(int $pf) : void
    {
        $this->pf = $pf;
    }

    public function getPA() : int
    {
        return $this->pa;
    }

    public function setPA(int $pa) : void
    {
        $this->pa = $pa;
    }

    public function getPD() : int
    {
        return $this->pd;
    }

    public function setPD(int $pd) : void
    {
        $this->pd = $pd;
    }

    public function getBP() : int
    {
        return $this->bp;
    }

    public function setBP(int $bp) : void
    {
        $this->bp = $bp;
    }

    public function getPP() : int
    {
        return $this->pp;
    }

    public function setPP(int $pp) : void
    {
        $this->pp = $pp;
    }

    public function getPTS() : int
    {
        return $this->pts;
    }

    public function setPTS(int $pts) : void
    {
        $this->pts = $pts;
    }

    public function getH2H() : object
    {
        return $this->head;
    }
}
