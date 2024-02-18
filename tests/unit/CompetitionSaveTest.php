<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Club;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Contact;
use VBCompetitions\Competitions\Crossover;
use VBCompetitions\Competitions\Group;
use VBCompetitions\Competitions\GroupBreak;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\IfUnknown;
use VBCompetitions\Competitions\IfUnknownBreak;
use VBCompetitions\Competitions\IfUnknownMatch;
use VBCompetitions\Competitions\Knockout;
use VBCompetitions\Competitions\League;
use VBCompetitions\Competitions\LeagueTable;
use VBCompetitions\Competitions\LeagueTableEntry;
use VBCompetitions\Competitions\MatchManager;
use VBCompetitions\Competitions\MatchOfficials;
use VBCompetitions\Competitions\MatchTeam;
use VBCompetitions\Competitions\Player;
use VBCompetitions\Competitions\SetConfig;
use VBCompetitions\Competitions\Stage;

#[CoversClass(Club::class)]
#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(Contact::class)]
#[CoversClass(Crossover::class)]
#[CoversClass(Group::class)]
#[CoversClass(GroupBreak::class)]
#[CoversClass(GroupMatch::class)]
#[CoversClass(IfUnknown::class)]
#[CoversClass(IfUnknownBreak::class)]
#[CoversClass(IfUnknownMatch::class)]
#[CoversClass(Knockout::class)]
#[CoversClass(League::class)]
#[CoversClass(LeagueTable::class)]
#[CoversClass(LeagueTableEntry::class)]
#[CoversClass(MatchManager::class)]
#[CoversClass(MatchOfficials::class)]
#[CoversClass(MatchTeam::class)]
#[CoversClass(Player::class)]
#[CoversClass(SetConfig::class)]
#[CoversClass(Stage::class)]
final class CompetitionSaveTest extends TestCase {
    protected function tearDown(): void
    {
        $files = glob(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))).DIRECTORY_SEPARATOR.'*.json');
        foreach($files as $file){
            if(is_file($file)){
                unlink($file);
            }
        }
    }

    public function testCompetitionSaveCompetition() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition.json');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition.json');
        $this->assertIsArray($saved_competition->getTeams());
        $stage = $saved_competition->getStageById('L');
        $this->assertEquals('league', $stage->getName());
        $this->assertEquals('TM6', $saved_competition->getTeamByID('{L:RL:RLM14:winner}')->getID());
    }

    public function testCompetitionSaveWithNewName() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition.json');
        $this->assertIsArray($saved_competition->getTeams());
        $stage = $saved_competition->getStageById('L');
        $this->assertEquals('league', $stage->getName());
        $this->assertEquals('TM6', $saved_competition->getTeamByID('{L:RL:RLM14:winner}')->getID());
        $this->assertEquals('Saved Competition', $saved_competition->getName());

        $this->assertEquals($competition->getTeamByID('TM1')->getNotes(), $saved_competition->getTeamByID('TM1')->getNotes());
    }

    public function testCompetitionSaveWithDates() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-league-full-data.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-league-full-data.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-league-full-data.json');
        $this->assertIsArray($saved_competition->getTeams());
        $stage = $saved_competition->getStageById('L');
        $this->assertEquals('league', $stage->getName());
        $this->assertEquals('TM2', $saved_competition->getTeamByID('{L:RL:RLM4:winner}')->getID());
        $this->assertEquals('Saved Competition', $saved_competition->getName());
    }

    public function testCompetitionSaveCompetitionWithNotes() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-with-notes.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-with-notes.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-with-notes.json');
        $this->assertEquals('This is a note', $competition->getNotes());
        $this->assertEquals('This is a note', $saved_competition->getNotes());
    }

    public function testCompetitionSaveCompetitionWithoutNotes() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-without-notes.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-without-notes.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-without-notes.json');
        $this->assertNull($competition->getNotes());
        $this->assertNull($saved_competition->getNotes());
    }

    public function testCompetitionSaveCompetitionWithPlayers() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'players'))), 'players.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'players.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'players.json');

        $team = $saved_competition->getTeamByID('TM3');
        $this->assertEquals('Alice Alison', $team->getPlayerByID('P1')->getName());
        $this->assertEquals('junior', $team->getPlayerByID('P1')->getNotes());
        $this->assertEquals('Charlie Charleston', $team->getPlayerByID('P3')->getName());
        $this->assertEquals(7, $team->getPlayerByID('P3')->getNumber());

        $team = $competition->getTeamByID('TM2');
        $this->assertNull($team->getPlayerByID('P1')->getNumber());
        $this->assertNull($team->getPlayerByID('P1')->getNotes());
    }

    public function testCompetitionSaveCompetitionKnockoutSets() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-knockout-sets.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-knockout-sets.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-knockout-sets.json');

        $this->assertEquals('TM7', $competition->getTeamByID('{KO:CUP:FIN:winner}')->getID());
        $this->assertEquals('TM7', $saved_competition->getTeamByID('{KO:CUP:FIN:winner}')->getID());
    }

    public function testCompetitionSaveCompetitionKnockoutSetsStandings() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-knockout-sets-standings.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-knockout-sets-standings.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-knockout-sets-standings.json');

        $this->assertEquals('TM7', $competition->getTeamByID('{KO:CUP:FIN:winner}')->getID());
        $this->assertEquals('TM7', $saved_competition->getTeamByID('{KO:CUP:FIN:winner}')->getID());
    }

    public function testCompetitionSaveCompetitionWithIfUnknown() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ifunknown'))), 'incomplete-group-multi-stage.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'incomplete-group-multi-stage.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'incomplete-group-multi-stage.json');

        $this->assertEquals('There will be a knockout stage', $competition->getStageById('F')->getIfUnknown()->getDescription()[0]);
        $this->assertEquals('There will be a knockout stage', $saved_competition->getStageById('F')->getIfUnknown()->getDescription()[0]);
    }

    public function testCompetitionSaveCompetitionWithClubs() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'club'))), 'competition-with-clubs.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-with-clubs.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'competition-with-clubs.json');

        $this->assertEquals('Southampton', $competition->getClubById('SOU')->getName());
        $this->assertEquals('Southampton', $saved_competition->getClubById('SOU')->getName());

        $this->assertIsArray($saved_competition->getClubById('SOU')->getTeams());
        $this->assertCount(3, $saved_competition->getClubById('SOU')->getTeams());
    }

    public function testCompetitionSaveMatchWithManagerTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'manager'))), 'manager-team.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'manager-team.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'manager-team.json');

        $this->assertEquals('TM1', $saved_competition->getStageById('L')->getGroupById('LG')->getMatchById('LG1')->getManager()->getTeamID());
    }

    public function testCompetitionSaveMatchWithManagerPlayer() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'manager'))), 'manager-person.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'manager-person.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'manager-person.json');

        $this->assertEquals('Some Manager', $saved_competition->getStageById('L')->getGroupById('LG')->getMatchById('LG1')->getManager()->getManagerName());
    }

    public function testCompetitionSaveMatchWitOfficialsPeople() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'officials'))), 'officials-persons.json');
        $competition->setName('Saved Competition');
        $competition->saveToFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'officials-persons.json');
        $saved_competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'save'))), 'officials-persons.json');

        $this->assertEquals('A First', $saved_competition->getStageById('L')->getGroupById('LG')->getMatchById('LG1')->getOfficials()->getFirstRef());
    }
}
