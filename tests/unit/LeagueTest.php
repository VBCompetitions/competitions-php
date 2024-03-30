<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\GroupType;
use VBCompetitions\Competitions\League;
use VBCompetitions\Competitions\LeagueConfig;
use VBCompetitions\Competitions\LeagueConfigPoints;
use VBCompetitions\Competitions\LeagueTable;
use VBCompetitions\Competitions\LeagueTableEntry;
use VBCompetitions\Competitions\MatchOfficials;
use VBCompetitions\Competitions\MatchTeam;
use VBCompetitions\Competitions\MatchType;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Competition::class)]
#[CoversClass(Stage::class)]
#[CoversClass(League::class)]
#[CoversClass(LeagueConfig::class)]
#[CoversClass(LeagueConfigPoints::class)]
#[CoversClass(LeagueTable::class)]
#[CoversClass(LeagueTableEntry::class)]
final class LeagueTest extends TestCase {
    public function testLeague1() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league.json');
        $league = $competition->getStageById('L')->getGroupById('LG');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(3);
        $tm1->setWins(0);
        $tm1->setLosses(3);
        $tm1->setPF(72);
        $tm1->setPA(84);
        $tm1->setPD(-12);
        $tm1->setPTS(0);
        $tm1->getH2H()->TM2 = 0;
        $tm1->getH2H()->TM3 = 0;
        $tm1->getH2H()->TM4 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(3);
        $tm2->setWins(1);
        $tm2->setLosses(2);
        $tm2->setPF(76);
        $tm2->setPA(74);
        $tm2->setPD(2);
        $tm2->setPTS(3);
        $tm2->getH2H()->TM1 = 1;
        $tm2->getH2H()->TM3 = 0;
        $tm2->getH2H()->TM4 = 0;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(3);
        $tm3->setWins(2);
        $tm3->setLosses(1);
        $tm3->setPF(75);
        $tm3->setPA(75);
        $tm3->setPD(0);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;
        $tm3->getH2H()->TM4 = 0;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(3);
        $tm4->setWins(3);
        $tm4->setLosses(0);
        $tm4->setPF(80);
        $tm4->setPA(70);
        $tm4->setPD(10);
        $tm4->setPTS(9);
        $tm4->getH2H()->TM1 = 1;
        $tm4->getH2H()->TM2 = 1;
        $tm4->getH2H()->TM3 = 1;

        array_push($expectedTable->entries, $tm4);
        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm1);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
        $this->assertEquals(GroupType::LEAGUE, $league->getType());
        $this->assertEquals('LG', $table->getGroupID());
        $this->assertEquals('LG', $table->entries[0]->getGroupID());
        $this->assertFalse($table->hasDraws());

        try {
            $league->getTeamByID('league', '5');
            $this->fail('League should not be able to return more teams than are in league');
        } catch (Exception $e) {
            $this->assertEquals('Invalid League position: position is bigger than the number of teams', $e->getMessage());
        }
    }

    public function testLeagueByPD() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-pd.json');
        $league = $competition->getStageById('L')->getGroupById('LG');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(3);
        $tm1->setWins(0);
        $tm1->setLosses(3);
        $tm1->setSF(0);
        $tm1->setSA(9);
        $tm1->setSD(-9);
        $tm1->setPF(194);
        $tm1->setPA(235);
        $tm1->setPD(-41);
        $tm1->setPTS(0);
        $tm1->getH2H()->TM2 = 0;
        $tm1->getH2H()->TM3 = 0;
        $tm1->getH2H()->TM4 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(3);
        $tm2->setWins(2);
        $tm2->setLosses(1);
        $tm2->setSF(6);
        $tm2->setSA(3);
        $tm2->setSD(3);
        $tm2->setPF(215);
        $tm2->setPA(203);
        $tm2->setPD(12);
        $tm2->setPTS(6);
        $tm2->getH2H()->TM1 = 1;
        $tm2->getH2H()->TM3 = 0;
        $tm2->getH2H()->TM4 = 1;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(3);
        $tm3->setWins(2);
        $tm3->setLosses(1);
        $tm3->setSF(6);
        $tm3->setSA(3);
        $tm3->setSD(3);
        $tm3->setPF(219);
        $tm3->setPA(204);
        $tm3->setPD(15);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;
        $tm3->getH2H()->TM4 = 0;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(3);
        $tm4->setWins(2);
        $tm4->setLosses(1);
        $tm4->setSF(6);
        $tm4->setSA(3);
        $tm4->setSD(3);
        $tm4->setPF(224);
        $tm4->setPA(210);
        $tm4->setPD(14);
        $tm4->setPTS(6);
        $tm4->getH2H()->TM1 = 1;
        $tm4->getH2H()->TM2 = 0;
        $tm4->getH2H()->TM3 = 1;

        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm4);
        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm1);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
    }

    public function testLeagueBySD() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-sd.json');
        $league = $competition->getStageById('L')->getGroupById('LG');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(3);
        $tm1->setWins(0);
        $tm1->setLosses(3);
        $tm1->setSF(0);
        $tm1->setSA(9);
        $tm1->setSD(-9);
        $tm1->setPF(184);
        $tm1->setPA(235);
        $tm1->setPD(-51);
        $tm1->setPTS(0);
        $tm1->getH2H()->TM2 = 0;
        $tm1->getH2H()->TM3 = 0;
        $tm1->getH2H()->TM4 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(3);
        $tm2->setWins(2);
        $tm2->setLosses(1);
        $tm2->setSF(6);
        $tm2->setSA(4);
        $tm2->setSD(2);
        $tm2->setPF(236);
        $tm2->setPA(219);
        $tm2->setPD(17);
        $tm2->setPTS(6);
        $tm2->getH2H()->TM1 = 1;
        $tm2->getH2H()->TM3 = 0;
        $tm2->getH2H()->TM4 = 1;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(3);
        $tm3->setWins(2);
        $tm3->setLosses(1);
        $tm3->setSF(6);
        $tm3->setSA(3);
        $tm3->setSD(3);
        $tm3->setPF(220);
        $tm3->setPA(203);
        $tm3->setPD(17);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;
        $tm3->getH2H()->TM4 = 0;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(3);
        $tm4->setWins(2);
        $tm4->setLosses(1);
        $tm4->setSF(7);
        $tm4->setSA(3);
        $tm4->setSD(4);
        $tm4->setPF(249);
        $tm4->setPA(232);
        $tm4->setPD(17);
        $tm4->setPTS(6);
        $tm4->getH2H()->TM1 = 1;
        $tm4->getH2H()->TM2 = 0;
        $tm4->getH2H()->TM3 = 1;

        array_push($expectedTable->entries, $tm4);
        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm1);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
    }

    public function testLeagueByH2HA() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-h2h.json');
        $league = $competition->getStageById('L')->getGroupById('LG1');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(3);
        $tm1->setWins(1);
        $tm1->setLosses(2);
        $tm1->setSF(3);
        $tm1->setSA(6);
        $tm1->setSD(-3);
        $tm1->setPF(202);
        $tm1->setPA(226);
        $tm1->setPD(-24);
        $tm1->setPTS(3);
        $tm1->getH2H()->TM2 = 0;
        $tm1->getH2H()->TM3 = 1;
        $tm1->getH2H()->TM4 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(3);
        $tm2->setWins(2);
        $tm2->setLosses(1);
        $tm2->setSF(6);
        $tm2->setSA(3);
        $tm2->setSD(3);
        $tm2->setPF(216);
        $tm2->setPA(202);
        $tm2->setPD(14);
        $tm2->setPTS(6);
        $tm2->getH2H()->TM1 = 1;
        $tm2->getH2H()->TM3 = 0;
        $tm2->getH2H()->TM4 = 1;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(3);
        $tm3->setWins(1);
        $tm3->setLosses(2);
        $tm3->setSF(3);
        $tm3->setSA(6);
        $tm3->setSD(-3);
        $tm3->setPF(210);
        $tm3->setPA(214);
        $tm3->setPD(-4);
        $tm3->setPTS(3);
        $tm3->getH2H()->TM1 = 0;
        $tm3->getH2H()->TM2 = 1;
        $tm3->getH2H()->TM4 = 0;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(3);
        $tm4->setWins(2);
        $tm4->setLosses(1);
        $tm4->setSF(6);
        $tm4->setSA(3);
        $tm4->setSD(3);
        $tm4->setPF(224);
        $tm4->setPA(210);
        $tm4->setPD(14);
        $tm4->setPTS(6);
        $tm4->getH2H()->TM1 = 1;
        $tm4->getH2H()->TM2 = 0;
        $tm4->getH2H()->TM3 = 1;

        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm4);
        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm1);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
    }

    public function testLeagueByH2HB() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-h2h.json');
        $league = $competition->getStageById('L2')->getGroupById('LG2');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(3);
        $tm1->setWins(1);
        $tm1->setLosses(2);
        $tm1->setSF(3);
        $tm1->setSA(6);
        $tm1->setSD(-3);
        $tm1->setPF(202);
        $tm1->setPA(226);
        $tm1->setPD(-24);
        $tm1->setPTS(3);
        $tm1->getH2H()->TM2 = 0;
        $tm1->getH2H()->TM3 = 1;
        $tm1->getH2H()->TM4 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(3);
        $tm2->setWins(2);
        $tm2->setLosses(1);
        $tm2->setSF(6);
        $tm2->setSA(3);
        $tm2->setSD(3);
        $tm2->setPF(208);
        $tm2->setPA(194);
        $tm2->setPD(14);
        $tm2->setPTS(6);
        $tm2->getH2H()->TM1 = 1;
        $tm2->getH2H()->TM3 = 1;
        $tm2->getH2H()->TM4 = 0;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(3);
        $tm3->setWins(1);
        $tm3->setLosses(2);
        $tm3->setSF(3);
        $tm3->setSA(6);
        $tm3->setSD(-3);
        $tm3->setPF(205);
        $tm3->setPA(209);
        $tm3->setPD(-4);
        $tm3->setPTS(3);
        $tm3->getH2H()->TM1 = 0;
        $tm3->getH2H()->TM2 = 0;
        $tm3->getH2H()->TM4 = 1;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(3);
        $tm4->setWins(2);
        $tm4->setLosses(1);
        $tm4->setSF(6);
        $tm4->setSA(3);
        $tm4->setSD(3);
        $tm4->setPF(211);
        $tm4->setPA(197);
        $tm4->setPD(14);
        $tm4->setPTS(6);
        $tm4->getH2H()->TM1 = 1;
        $tm4->getH2H()->TM2 = 1;
        $tm4->getH2H()->TM3 = 0;

        array_push($expectedTable->entries, $tm4);
        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm1);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
    }

    public function testLeagueByH2HPlayTwice() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-h2h-twice.json');
        $league = $competition->getStageById('L')->getGroupById('LG1');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(6);
        $tm1->setWins(2);
        $tm1->setLosses(4);
        $tm1->setSF(6);
        $tm1->setSA(12);
        $tm1->setSD(-6);
        $tm1->setPF(404);
        $tm1->setPA(452);
        $tm1->setPD(-48);
        $tm1->setPTS(6);
        $tm1->getH2H()->TM2 = -1;
        $tm1->getH2H()->TM3 = 2;
        $tm1->getH2H()->TM4 = -1;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(6);
        $tm2->setWins(4);
        $tm2->setLosses(2);
        $tm2->setSF(12);
        $tm2->setSA(6);
        $tm2->setSD(6);
        $tm2->setPF(432);
        $tm2->setPA(404);
        $tm2->setPD(28);
        $tm2->setPTS(12);
        $tm2->getH2H()->TM1 = 2;
        $tm2->getH2H()->TM3 = -1;
        $tm2->getH2H()->TM4 = 2;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(6);
        $tm3->setWins(2);
        $tm3->setLosses(4);
        $tm3->setSF(6);
        $tm3->setSA(12);
        $tm3->setSD(-6);
        $tm3->setPF(420);
        $tm3->setPA(428);
        $tm3->setPD(-8);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = -1;
        $tm3->getH2H()->TM2 = 2;
        $tm3->getH2H()->TM4 = -1;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(6);
        $tm4->setWins(4);
        $tm4->setLosses(2);
        $tm4->setSF(12);
        $tm4->setSA(6);
        $tm4->setSD(6);
        $tm4->setPF(448);
        $tm4->setPA(420);
        $tm4->setPD(28);
        $tm4->setPTS(12);
        $tm4->getH2H()->TM1 = 2;
        $tm4->getH2H()->TM2 = -1;
        $tm4->getH2H()->TM3 = 2;

        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm4);
        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm1);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
    }

    public function testLeagueByPTS() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-comparisons.json');
        $league = $competition->getStageById('PTS')->getGroupById('PTS');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(2);
        $tm1->setWins(1);
        $tm1->setLosses(1);
        $tm1->setSF(3);
        $tm1->setSA(2);
        $tm1->setSD(1);
        $tm1->setPF(120);
        $tm1->setPA(117);
        $tm1->setPD(3);
        $tm1->setPTS(4);
        $tm1->getH2H()->TM2 = 1;
        $tm1->getH2H()->TM3 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(2);
        $tm2->setWins(0);
        $tm2->setLosses(2);
        $tm2->setSF(0);
        $tm2->setSA(4);
        $tm2->setSD(-4);
        $tm2->setPF(88);
        $tm2->setPA(100);
        $tm2->setPD(-12);
        $tm2->setPTS(0);
        $tm2->getH2H()->TM1 = 0;
        $tm2->getH2H()->TM3 = 0;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(2);
        $tm3->setWins(2);
        $tm3->setLosses(0);
        $tm3->setSF(4);
        $tm3->setSA(1);
        $tm3->setSD(3);
        $tm3->setPF(123);
        $tm3->setPA(114);
        $tm3->setPD(9);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;

        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm1);
        array_push($expectedTable->entries, $tm2);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
    }

    public function testLeagueByPF() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-comparisons.json');
        $league = $competition->getStageById('PF')->getGroupById('PF');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(2);
        $tm1->setWins(1);
        $tm1->setLosses(1);
        $tm1->setSF(3);
        $tm1->setSA(2);
        $tm1->setSD(1);
        $tm1->setPF(120);
        $tm1->setPA(117);
        $tm1->setPD(3);
        $tm1->setPTS(4);
        $tm1->getH2H()->TM2 = 1;
        $tm1->getH2H()->TM3 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(2);
        $tm2->setWins(0);
        $tm2->setLosses(2);
        $tm2->setSF(0);
        $tm2->setSA(4);
        $tm2->setSD(-4);
        $tm2->setPF(88);
        $tm2->setPA(100);
        $tm2->setPD(-12);
        $tm2->setPTS(0);
        $tm2->getH2H()->TM1 = 0;
        $tm2->getH2H()->TM3 = 0;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(2);
        $tm3->setWins(2);
        $tm3->setLosses(0);
        $tm3->setSF(4);
        $tm3->setSA(1);
        $tm3->setSD(3);
        $tm3->setPF(123);
        $tm3->setPA(114);
        $tm3->setPD(9);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;

        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm1);
        array_push($expectedTable->entries, $tm2);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
    }

    public function testLeagueByPA() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-comparisons.json');
        $league = $competition->getStageById('PA')->getGroupById('PA');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(2);
        $tm1->setWins(1);
        $tm1->setLosses(1);
        $tm1->setSF(3);
        $tm1->setSA(2);
        $tm1->setSD(1);
        $tm1->setPF(120);
        $tm1->setPA(117);
        $tm1->setPD(3);
        $tm1->setPTS(4);
        $tm1->getH2H()->TM2 = 1;
        $tm1->getH2H()->TM3 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(2);
        $tm2->setWins(0);
        $tm2->setLosses(2);
        $tm2->setSF(0);
        $tm2->setSA(4);
        $tm2->setSD(-4);
        $tm2->setPF(88);
        $tm2->setPA(100);
        $tm2->setPD(-12);
        $tm2->setPTS(0);
        $tm2->getH2H()->TM1 = 0;
        $tm2->getH2H()->TM3 = 0;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(2);
        $tm3->setWins(2);
        $tm3->setLosses(0);
        $tm3->setSF(4);
        $tm3->setSA(1);
        $tm3->setSD(3);
        $tm3->setPF(123);
        $tm3->setPA(114);
        $tm3->setPD(9);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;

        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm1);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
    }

    public function testLeagueBySF() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-comparisons.json');
        $league = $competition->getStageById('SF')->getGroupById('SF');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(2);
        $tm1->setWins(1);
        $tm1->setLosses(1);
        $tm1->setSF(3);
        $tm1->setSA(2);
        $tm1->setSD(1);
        $tm1->setPF(120);
        $tm1->setPA(117);
        $tm1->setPD(3);
        $tm1->setPTS(4);
        $tm1->getH2H()->TM2 = 1;
        $tm1->getH2H()->TM3 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(2);
        $tm2->setWins(0);
        $tm2->setLosses(2);
        $tm2->setSF(0);
        $tm2->setSA(4);
        $tm2->setSD(-4);
        $tm2->setPF(88);
        $tm2->setPA(100);
        $tm2->setPD(-12);
        $tm2->setPTS(0);
        $tm2->getH2H()->TM1 = 0;
        $tm2->getH2H()->TM3 = 0;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(2);
        $tm3->setWins(2);
        $tm3->setLosses(0);
        $tm3->setSF(4);
        $tm3->setSA(1);
        $tm3->setSD(3);
        $tm3->setPF(123);
        $tm3->setPA(114);
        $tm3->setPD(9);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;

        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm1);
        array_push($expectedTable->entries, $tm2);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
    }

    public function testLeagueBySA() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-comparisons.json');
        $league = $competition->getStageById('SA')->getGroupById('SA');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(2);
        $tm1->setWins(1);
        $tm1->setLosses(1);
        $tm1->setSF(3);
        $tm1->setSA(2);
        $tm1->setSD(1);
        $tm1->setPF(120);
        $tm1->setPA(117);
        $tm1->setPD(3);
        $tm1->setPTS(4);
        $tm1->getH2H()->TM2 = 1;
        $tm1->getH2H()->TM3 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(2);
        $tm2->setWins(0);
        $tm2->setLosses(2);
        $tm2->setSF(0);
        $tm2->setSA(4);
        $tm2->setSD(-4);
        $tm2->setPF(88);
        $tm2->setPA(100);
        $tm2->setPD(-12);
        $tm2->setPTS(0);
        $tm2->getH2H()->TM1 = 0;
        $tm2->getH2H()->TM3 = 0;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(2);
        $tm3->setWins(2);
        $tm3->setLosses(0);
        $tm3->setSF(4);
        $tm3->setSA(1);
        $tm3->setSD(3);
        $tm3->setPF(123);
        $tm3->setPA(114);
        $tm3->setPD(9);
        $tm3->setPTS(6);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;

        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm1);
        array_push($expectedTable->entries, $tm2);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
    }

    public function testLeagueIncomplete() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'incomplete-league.json');
        $league = $competition->getStageById('L')->getGroupById('LG');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(1);
        $tm1->setWins(1);
        $tm1->setLosses(0);
        $tm1->setSF(0);
        $tm1->setSA(0);
        $tm1->setSD(0);
        $tm1->setPF(22);
        $tm1->setPA(14);
        $tm1->setPD(8);
        $tm1->setPTS(3);
        $tm1->getH2H()->TM3 = 1;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(1);
        $tm2->setWins(1);
        $tm2->setLosses(0);
        $tm2->setSF(0);
        $tm2->setSA(0);
        $tm2->setSD(0);
        $tm2->setPF(20);
        $tm2->setPA(15);
        $tm2->setPD(5);
        $tm2->setPTS(3);
        $tm2->getH2H()->TM4 = 1;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(1);
        $tm3->setWins(0);
        $tm3->setLosses(1);
        $tm3->setSF(0);
        $tm3->setSA(0);
        $tm3->setSD(0);
        $tm3->setPF(14);
        $tm3->setPA(22);
        $tm3->setPD(-8);
        $tm3->setPTS(0);
        $tm3->getH2H()->TM1 = 0;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(1);
        $tm4->setWins(0);
        $tm4->setLosses(1);
        $tm4->setSF(0);
        $tm4->setSA(0);
        $tm4->setSD(0);
        $tm4->setPF(15);
        $tm4->setPA(20);
        $tm4->setPD(-5);
        $tm4->setPTS(0);
        $tm4->getH2H()->TM2 = 0;

        array_push($expectedTable->entries, $tm1);
        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm4);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertFalse($league->isComplete());
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
    }

    public function testLeagueIncompleteDraws() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'incomplete-league.json');
        $league = $competition->getStageById('LD')->getGroupById('LG');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(1);
        $tm1->setWins(0);
        $tm1->setLosses(0);
        $tm1->setDraws(1);
        $tm1->setSF(0);
        $tm1->setSA(0);
        $tm1->setSD(0);
        $tm1->setPF(22);
        $tm1->setPA(22);
        $tm1->setPD(0);
        $tm1->setPTS(0);
        $tm1->getH2H()->TM3 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(1);
        $tm2->setWins(1);
        $tm2->setLosses(0);
        $tm2->setSF(0);
        $tm2->setSA(0);
        $tm2->setSD(0);
        $tm2->setPF(35);
        $tm2->setPA(30);
        $tm2->setPD(5);
        $tm2->setPTS(3);
        $tm2->getH2H()->TM4 = 1;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(1);
        $tm3->setWins(0);
        $tm3->setLosses(0);
        $tm3->setDraws(1);
        $tm3->setSF(0);
        $tm3->setSA(0);
        $tm3->setSD(0);
        $tm3->setPF(22);
        $tm3->setPA(22);
        $tm3->setPD(0);
        $tm3->setPTS(0);
        $tm3->getH2H()->TM1 = 0;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(1);
        $tm4->setWins(0);
        $tm4->setLosses(1);
        $tm4->setSF(0);
        $tm4->setSA(0);
        $tm4->setSD(0);
        $tm4->setPF(30);
        $tm4->setPA(35);
        $tm4->setPD(-5);
        $tm4->setPTS(0);
        $tm4->getH2H()->TM2 = 0;

        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm4);
        array_push($expectedTable->entries, $tm1);
        array_push($expectedTable->entries, $tm3);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertFalse($league->isComplete());
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
    }

    public function testLeagueIncompleteSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'incomplete-league.json');
        $league = $competition->getStageById('LS')->getGroupById('LG');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(1);
        $tm1->setWins(0);
        $tm1->setLosses(0);
        $tm1->setDraws(1);
        $tm1->setSF(1);
        $tm1->setSA(1);
        $tm1->setSD(0);
        $tm1->setPF(40);
        $tm1->setPA(39);
        $tm1->setPD(1);
        $tm1->setPTS(0);
        $tm1->getH2H()->TM3 = 0;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(1);
        $tm2->setWins(1);
        $tm2->setLosses(0);
        $tm2->setSF(2);
        $tm2->setSA(0);
        $tm2->setSD(2);
        $tm2->setPF(50);
        $tm2->setPA(38);
        $tm2->setPD(12);
        $tm2->setPTS(3);
        $tm2->getH2H()->TM4 = 1;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(1);
        $tm3->setWins(0);
        $tm3->setLosses(0);
        $tm3->setDraws(1);
        $tm3->setSF(1);
        $tm3->setSA(1);
        $tm3->setSD(0);
        $tm3->setPF(39);
        $tm3->setPA(40);
        $tm3->setPD(-1);
        $tm3->setPTS(0);
        $tm3->getH2H()->TM1 = 0;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(1);
        $tm4->setWins(0);
        $tm4->setLosses(1);
        $tm4->setSF(0);
        $tm4->setSA(2);
        $tm4->setSD(-2);
        $tm4->setPF(38);
        $tm4->setPA(50);
        $tm4->setPD(-12);
        $tm4->setPTS(0);
        $tm4->getH2H()->TM2 = 0;

        array_push($expectedTable->entries, $tm2);
        array_push($expectedTable->entries, $tm1);
        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm4);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertFalse($league->isComplete());
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
    }

    public function testLeagueWithForfeitsBonusPenalties() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-forfeit.json');
        $league = $competition->getStageById('L')->getGroupById('LG');
        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');

        $expectedTable = new LeagueTable($league);
        $tm1 = new LeagueTableEntry($league, 'TM1', 'Team 1');
        $tm1->setPlayed(3);
        $tm1->setWins(1);
        $tm1->setLosses(2);
        $tm1->setPF(72);
        $tm1->setPA(56);
        $tm1->setPD(16);
        $tm1->setPTS(4);
        $tm1->getH2H()->TM2 = 0;
        $tm1->getH2H()->TM3 = 0;
        $tm1->getH2H()->TM4 = 1;
        $tm2 = new LeagueTableEntry($league, 'TM2', 'Team 2');
        $tm2->setPlayed(3);
        $tm2->setWins(1);
        $tm2->setLosses(2);
        $tm2->setPF(53);
        $tm2->setPA(74);
        $tm2->setPD(-21);
        $tm2->setPTS(2);
        $tm2->getH2H()->TM1 = 1;
        $tm2->getH2H()->TM3 = 0;
        $tm2->getH2H()->TM4 = 0;
        $tm3 = new LeagueTableEntry($league, 'TM3', 'Team 3');
        $tm3->setPlayed(3);
        $tm3->setWins(2);
        $tm3->setLosses(1);
        $tm3->setPF(75);
        $tm3->setPA(52);
        $tm3->setPD(23);
        $tm3->setPTS(7);
        $tm3->getH2H()->TM1 = 1;
        $tm3->getH2H()->TM2 = 1;
        $tm3->getH2H()->TM4 = 0;
        $tm4 = new LeagueTableEntry($league, 'TM4', 'Team 4');
        $tm4->setPlayed(3);
        $tm4->setWins(2);
        $tm4->setLosses(1);
        $tm4->setPF(52);
        $tm4->setPA(70);
        $tm4->setPD(-18);
        $tm4->setPTS(5);
        $tm4->getH2H()->TM1 = 0;
        $tm4->getH2H()->TM2 = 1;
        $tm4->getH2H()->TM3 = 1;

        array_push($expectedTable->entries, $tm3);
        array_push($expectedTable->entries, $tm4);
        array_push($expectedTable->entries, $tm1);
        array_push($expectedTable->entries, $tm2);

        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertTrue($league->isComplete(), 'Group should be found as completed');
        $this->assertEquals($expectedTable->entries[0], $table->entries[0]);
        $this->assertEquals($expectedTable->entries[1], $table->entries[1]);
        $this->assertEquals($expectedTable->entries[2], $table->entries[2]);
        $this->assertEquals($expectedTable->entries[3], $table->entries[3]);
        $this->assertEquals(GroupType::LEAGUE, $league->getType());
    }

    public function testLeagueWithEveryOrderAndPoints() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-everything.json');
        $league = $competition->getStageById('L')->getGroupById('LG');

        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');
        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertEquals('Position is decided by points, then wins, then losses, then head-to-head, then points for, then points against, then points difference, then sets for, then sets against, then sets difference', $table->getOrderingText());

        $this->assertEquals('Teams win 1 point per played, 2 points per win, 3 points per set, 4 points per win by one set, 5 points per loss, 6 points per loss by one set and 7 points per forfeited match', $table->getScoringText());
    }

    public function testLeagueWithNoScoring() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league-no-scoring.json');
        $league = $competition->getStageById('L')->getGroupById('LG');

        $this->assertInstanceOf('VBCompetitions\Competitions\League', $league, 'Group should be a league');
        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }

        $this->assertEquals('', $table->getScoringText());
    }

    public function testLeagueGroupsWithoutNames() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'league-no-names.json');
        $league = $competition->getStageById('L');

        $this->assertNull($league->getGroupById('LG0')->getName());
        $this->assertEquals('League', $league->getGroupById('LG1')->getName());
        $this->assertNull($league->getGroupById('LG2')->getName());
    }

    public function testLeagueWithFriendliesJSON() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'league-with-friendlies.json');

        $league = $competition->getStageByID('S')->getGroupByID('L');
        $table = null;
        if ($league instanceof League) {
            // We do this so IDEs don't complain about (Group) $league not having a getLeagueTable method
            $table = $league->getLeagueTable();
        }
        $this->assertCount(3, $table->entries);
        $this->assertEquals('TA', $table->entries[0]->getTeamID());
        $this->assertEquals(2, $table->entries[0]->getWins());
        $this->assertEquals('TB', $table->entries[1]->getTeamID());
        $this->assertEquals(1, $table->entries[1]->getWins());
        $this->assertEquals('TC', $table->entries[2]->getTeamID());
        $this->assertEquals(0, $table->entries[2]->getWins());
    }

    public function testLeagueWithFriendliesCode() : void
    {
        $competition = new Competition('league with friendlies');
        $teamA = new CompetitionTeam($competition, 'TA', 'Team A');
        $teamB = new CompetitionTeam($competition, 'TB', 'Team B');
        $teamC = new CompetitionTeam($competition, 'TC', 'Team C');
        $teamD = new CompetitionTeam($competition, 'TD', 'Team D');
        $competition->addTeam($teamA)->addTeam($teamB)->addTeam($teamC)->addTeam($teamD);
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $league = new League($stage, 'L', MatchType::CONTINUOUS, false);
        $stage->addGroup($league);
        $league_config = new LeagueConfig($league);
        $league->setLeagueConfig($league_config);
        $league_config->setOrdering(['PTS', 'H2H']);
        $config_points = new LeagueConfigPoints($league_config);
        $league_config->setPoints($config_points);
        $match1 = new GroupMatch($league, 'M1');
        $match1->setHomeTeam(new MatchTeam($match1, $teamA->getID()))->setAwayTeam(new MatchTeam($match1, $teamB->getID()))->setOfficials(new MatchOfficials($match1, $teamC->getID()))->setScores([23], [19], true);
        $match2 = new GroupMatch($league, 'M2');
        $match2->setHomeTeam(new MatchTeam($match2, $teamB->getID()))->setAwayTeam(new MatchTeam($match2, $teamC->getID()))->setOfficials(new MatchOfficials($match2, $teamD->getID()))->setScores([23], [19], true);
        $match3 = new GroupMatch($league, 'M3');
        $match3->setHomeTeam(new MatchTeam($match3, $teamC->getID()))->setAwayTeam(new MatchTeam($match3, $teamD->getID()))->setOfficials(new MatchOfficials($match3, $teamA->getID()))->setScores([23], [19], true)->setFriendly(true);
        $match4 = new GroupMatch($league, 'M4');
        $match4->setHomeTeam(new MatchTeam($match4, $teamD->getID()))->setAwayTeam(new MatchTeam($match4, $teamB->getID()))->setOfficials(new MatchOfficials($match4, $teamC->getID()))->setScores([19], [123], true)->setFriendly(true);
        $match5 = new GroupMatch($league, 'M5');
        $match5->setHomeTeam(new MatchTeam($match5, $teamA->getID()))->setAwayTeam(new MatchTeam($match5, $teamC->getID()))->setOfficials(new MatchOfficials($match5, $teamD->getID()))->setScores([23], [19], true);
        $match6 = new GroupMatch($league, 'M6');
        $match6->setHomeTeam(new MatchTeam($match6, $teamA->getID()))->setAwayTeam(new MatchTeam($match6, $teamD->getID()))->setOfficials(new MatchOfficials($match6, $teamC->getID()))->setScores([23], [19], true)->setFriendly(true);
        $league->addMatch($match1)->addMatch($match2)->addMatch($match3)->addMatch($match4)->addMatch($match5)->addMatch($match6);

        $table = $league->getLeagueTable();
        $this->assertCount(3, $table->entries);
        $this->assertEquals('TA', $table->entries[0]->getTeamID());
        $this->assertEquals(2, $table->entries[0]->getWins());
        $this->assertEquals('TB', $table->entries[1]->getTeamID());
        $this->assertEquals(1, $table->entries[1]->getWins());
        $this->assertEquals('TC', $table->entries[2]->getTeamID());
        $this->assertEquals(0, $table->entries[2]->getWins());

        $this->assertEquals($league, $league_config->getLeague());
        $this->assertEquals($league_config, $config_points->getLeagueConfig());
        $this->assertEquals($league, $table->getLeague());
    }



    public function testLeagueGetTeamLookupsInvalid() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'leagues'))), 'complete-league.json');
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:LG:LG1:foo}')->getID());
    }
}
