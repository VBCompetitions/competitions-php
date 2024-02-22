<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;

use Exception;
use Throwable;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\Stage;
use VBCompetitions\Competitions\club;
use stdClass;

#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(Stage::class)]
#[CoversClass(Club::class)]
final class CompetitionTest extends TestCase {
    const EMPTY_COMPETITION = '{"name":"Test Competition", "teams": [], "stages": []}';

    public function testCompetitionList() : void
    {
        $expectedList = [];

        $four_team = new stdClass();
        $four_team->file = '4-teams_1-league-continuous-and-time-limit.json';
        $four_team->is_valid = true;
        $four_team->is_complete = false;
        $four_team->name = 'Four Team Tournament';
        array_push($expectedList, $four_team);

        $four_team_complete = new stdClass();
        $four_team_complete->file = '4-teams-complete.json';
        $four_team_complete->is_valid = true;
        $four_team_complete->is_complete = true;
        $four_team_complete->name = 'Four Team Tournament';
        array_push($expectedList, $four_team_complete);

        $other = new stdClass();
        $other->file = 'other.json';
        $other->is_valid = true;
        $other->is_complete = false;
        $other->name = 'other';
        array_push($expectedList, $other);

        $invalid_competition = new stdClass();
        $invalid_competition->errorMessage = 'Competition data failed schema validation:\\n[#/required] [#] The required properties (name, teams, stages) are missing\r\n';
        array_push($expectedList, $invalid_competition);

        $competitionList = Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions', 'list'))));

        $this->assertIsArray($competitionList);
        $this->assertEquals(4, count($competitionList));
        foreach ($competitionList as $competition) {
            if ($competition->is_valid) {
                switch ($competition->file) {
                    case '4-teams_1-league-continuous-and-time-limit.json':
                        $this->assertEqualsCanonicalizing($four_team, $competition, "Error: The competition returned is: " . json_encode($competition, JSON_PRETTY_PRINT));
                        break;
                    case '4-teams-complete.json':
                        $this->assertEqualsCanonicalizing($four_team_complete, $competition, "Error: The competition returned is: " . json_encode($competition, JSON_PRETTY_PRINT));
                        break;
                    case 'other.json':
                        $this->assertEqualsCanonicalizing($other, $competition, "Error: The competition returned is: " . json_encode($competition, JSON_PRETTY_PRINT));
                        break;

                    default:
                        $this->fail('Unexpected file found in list iof files');
                }
            } else {
                $this->assertEquals('invalid-competition.json', $competition->file);
                $this->assertMatchesRegularExpression('/Competition data failed schema validation/', $competition->error_message);
            }
        }
    }

    public function testCompetitionLoad_InvalidData() : void
    {
        $this->expectExceptionMessageMatches('/Competition data failed schema validation.*\[#\/required\] \[#\] The required properties \(name, teams, stages\) are missing/s');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'invalid-competition.json');
    }

    public function testCompetitionLoad_InvalidVersion() : void
    {
        $this->expectExceptionMessage('Document version 0.0.1 not supported');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'bad-version-competition.json');
    }

    public function testCompetitionLoad_InvalidJSON() : void
    {
        $this->expectExceptionMessage('Document does not contain valid JSON');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-not-json.json');
    }

    public function testCompetitionLoad_NotAFile() : void
    {
        set_error_handler(function () {
            return;
        });

        try {
            Competition::loadFromFile(realpath(__DIR__), 'competitions');
            restore_error_handler();
            $this->fail('File loading should have failed');
        } catch (Throwable $th) {
            restore_error_handler();
            $this->assertEquals('Failed to load file', $th->getMessage());
        }
    }

    public function testCompetitionDuplicateTeamIDs() : void
    {
        $this->expectExceptionMessage('Team with ID "TM1" already exists in the competition');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-duplicate-team-ids.json');
    }

    public function testCompetitionDuplicateStageIDs() : void
    {
        $this->expectExceptionMessage('Stage with ID "L" already exists in the competition');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-duplicate-stage-ids.json');
    }

    public function testCompetitionDuplicateGroupIDs() : void
    {
        $this->expectExceptionMessage('Groups in a Stage with duplicate IDs not allowed: {L:RL}');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-duplicate-group-ids.json');
    }

    public function testCompetitionGetTeamByIDTernaries() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-with-references.json');
        $matchF1 = $competition->getStageById('F')->getGroupById('F')->getMatchById('F1');
        $matchF2 = $competition->getStageById('F')->getGroupById('F')->getMatchById('F2');

        $truthyTeam = $competition->getTeamByID($matchF1->getHomeTeam()->getID());
        $falsyTeam = $competition->getTeamByID($matchF1->getAwayTeam()->getID());

        $truthyTeamRef = $competition->getTeamByID($matchF2->getHomeTeam()->getID());
        $falsyTeamRef = $competition->getTeamByID($matchF2->getAwayTeam()->getID());

        $this->assertEquals('TM1', $truthyTeam->getID());
        $this->assertEquals('TM2', $falsyTeam->getID());
        $this->assertEquals('TM6', $truthyTeamRef->getID());
        $this->assertEquals('TM7', $falsyTeamRef->getID());
    }

    public function testCompetitionGetStageLookups() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition.json');
        $this->assertEquals(1, count($competition->getStages()));
        $this->assertEquals('L', $competition->getStages()[0]->getID());
        $stage = $competition->getStageById('L');
        $this->assertEquals('league', $stage->getName());
    }

    public function testCompetitionGetStageLookupFails() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition.json');
        $this->expectExceptionMessage('Stage with ID NO-STAGE not found');
        $competition->getStageById('NO-STAGE');
    }

    public function testCompetitionGetTeamLookupsIncomplete() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition.json');

        $this->assertTrue($competition->teamIdExists('TM1'));
        $this->assertTrue($competition->teamIdExists('TM8'));

        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:RL:league:1}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:RL:league:2}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:RL:league:3}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:RL:league:4}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:RL:league:5}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:RL:league:6}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:RL:league:7}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{L:RL:league:8}')->getID());

        $this->assertEquals('TM2', $competition->getTeamByID('{L:RL:RLM1:winner}')->getID());
        $this->assertEquals('TM1', $competition->getTeamByID('{L:RL:RLM1:loser}')->getID());

        $this->assertFalse($competition->teamIdExists('NO-SUCH-TEAM'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('NO-SUCH-TEAM')->getID());

        $this->assertFalse($competition->teamIdExists('{NO:SUCH:TEAM:REF}'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{NO:SUCH:TEAM:REF}')->getID());
    }

    public function testCompetitionGetTeamLookupsComplete() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-complete.json');
        $this->assertEquals('1.0.0', $competition->getVersion());

        $this->assertTrue($competition->teamIdExists('TM1'));
        $this->assertTrue($competition->teamIdExists('TM8'));

        $this->assertEquals('TM6', $competition->getTeamByID('{L:RL:league:1}')->getID());
        $this->assertEquals('TM5', $competition->getTeamByID('{L:RL:league:2}')->getID());
        $this->assertEquals('TM2', $competition->getTeamByID('{L:RL:league:3}')->getID());
        $this->assertEquals('TM4', $competition->getTeamByID('{L:RL:league:4}')->getID());
        $this->assertEquals('TM3', $competition->getTeamByID('{L:RL:league:5}')->getID());
        $this->assertEquals('TM7', $competition->getTeamByID('{L:RL:league:6}')->getID());
        $this->assertEquals('TM8', $competition->getTeamByID('{L:RL:league:7}')->getID());
        $this->assertEquals('TM1', $competition->getTeamByID('{L:RL:league:8}')->getID());

        $this->assertEquals('TM2', $competition->getTeamByID('{L:RL:RLM1:winner}')->getID());
        $this->assertEquals('TM1', $competition->getTeamByID('{L:RL:RLM1:loser}')->getID());

        $this->assertFalse($competition->teamIdExists('NO-SUCH-TEAM'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('NO-SUCH-TEAM')->getID());

        $this->assertFalse($competition->teamIdExists('{NO:SUCH:TEAM:REF}'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeamByID('{NO:SUCH:TEAM:REF}')->getID());
    }

    public function testCompetitionStageWithNoName() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-stages-no-name.json');

        $this->assertNull($competition->getStageById('L0')->getName());
        $this->assertEquals('league', $competition->getStageById('L1')->getName());
        $this->assertNull($competition->getStageById('L2')->getName());
    }

    public function testCompetitionIncompleteWithTeamReferences() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-half-done-with-references.json');

        $this->assertFalse($competition->getStageById('Divisions')->getGroupById('Division 1')->getMatchById('D1M1')->isComplete());
    }

    public function testCompetitionValidHomeTeamRef() : void
    {
        // An earlier version only checked for a starting '{'. Under certain structures, if a team reference was missing a closing '}'
        // in a second stage, the code would go into an infinite loop on calling teamMayHaveMatches, thinking there was a team
        // reference but unable to resolve that reference
        $this->expectExceptionMessage('Invalid team reference for homeTeam in match with ID "SF1": "{P:A:league:1"');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'bad-home-team-ref.json');
    }

    public function testCompetitionValidAwayTeamRef() : void
    {
        $this->expectExceptionMessage('Invalid team reference for awayTeam in match with ID "SF1": "{P:A:league:3"');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'bad-away-team-ref.json');
    }

    public function testCompetitionValidOfficialTeamRef() : void
    {
        $this->expectExceptionMessage('Invalid team reference for officials in match with ID "SF1": "{P:A:league:2"');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'bad-officials-team-ref.json');
    }

    public function testCompetitionValidHomeTeamID() : void
    {
        try {
            Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'bad-home-team-id.json');
            $this->fail('Test should have caught a bad homeTeam ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID for homeTeam in match with ID "PA1"', $e->getMessage());
            $this->assertEquals('Team with ID "TM10" does not exist', $e->getPrevious()->getMessage());
        }
    }

    public function testCompetitionValidAwayTeamID() : void
    {
        try {
            Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'bad-away-team-id.json');
            $this->fail('Test should have caught a bad awayTeam ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID for awayTeam in match with ID "PA1"', $e->getMessage());
            $this->assertEquals('Team with ID "TM10" does not exist', $e->getPrevious()->getMessage());
        }
    }

    public function testCompetitionValidOfficialTeamID() : void
    {
        try {
            Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'bad-officials-team-id.json');
            $this->fail('Test should have caught a bad officials team ID');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID for officials in match with ID "PA3"', $e->getMessage());
            $this->assertEquals('Team with ID "TM10" does not exist', $e->getPrevious()->getMessage());
        }
    }

    public function testCompetitionValidateTeamID() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-complete.json');

        $competition->validateTeamID('{L:RL:league:1}', 'match_id', 'field');
        $competition->validateTeamID('TM1', 'match_id', 'field');
        $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
        $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?TM1:{L:RL:league:2}', 'match_id', 'field');
        $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:TM2', 'match_id', 'field');
        $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?TM1:TM2', 'match_id', 'field');

        try {
            $competition->validateTeamID('{L:RL:league:0}', 'match_id', 'field');
            $this->fail('Test should have caught an invalid league entry');
        } catch (Exception $e) {
            $this->assertEquals('Invalid League position: reference must be a positive integer', $e->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:a}', 'match_id', 'field');
            $this->fail('Test should have caught an invalid league entry');
        } catch (Exception $e) {
            $this->assertEquals('Invalid League position: reference must be an integer', $e->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:9}', 'match_id', 'field');
            $this->fail('Test should have caught an invalid league entry');
        } catch (Exception $e) {
            $this->assertEquals('Invalid League position: position is bigger than the number of teams', $e->getMessage());
        }
    }

    public function testCompetitionValidateTeamIDBadTernaryLeftPart() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-complete.json');

        try {
            $competition->validateTeamID('{L:RL:league:1=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary left part reference for field in match with ID "match_id": "{L:RL:league:1"', $e->getMessage());
            $this->assertEquals('Invalid team reference format "{L:RL:league:1", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:RLM1:winer}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary left part reference for field in match with ID "match_id": "{L:RL:RLM1:winer}"', $e->getMessage());
            $this->assertEquals('Invalid Match result in reference {L:RL:RLM1:winer}: reference must be one of "winner"|"loser" in stage:group:match with IDs "L:RL:RLM1"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid team ID for field in match with ID "match_id"', $e->getMessage());
        }

        try {
            $competition->validateTeamID('{S:league:2}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary left part reference for field in match with ID "match_id": "{S:league:2}"', $e->getMessage());
            $this->assertEquals('Invalid team reference format "{S:league:2}", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{NAN:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary left part reference for field in match with ID "match_id": "{NAN:RL:league:1}"', $e->getMessage());
            $this->assertEquals('Invalid Stage part: Stage with ID "NAN" does not exist', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:NAN:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary left part reference for field in match with ID "match_id": "{L:NAN:league:1}"', $e->getMessage());
            $this->assertEquals('Invalid Group part: Group with ID "NAN" does not exist in stage with ID "L"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:NAN:winner}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary left part reference for field in match with ID "match_id": "{L:RL:NAN:winner}"', $e->getMessage());
            $this->assertEquals('Invalid Match part in reference {L:RL:NAN:winner} : Match with ID "NAN" does not exist in stage:group with IDs "L:RL"', $e->getPrevious()->getMessage());
        }
    }

    public function testCompetitionValidateTeamIDBadTernaryRightPart() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-complete.json');

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary right part reference for field in match with ID "match_id": "{L:RL:RLM1:winner"', $e->getMessage());
            $this->assertEquals('Invalid team reference format "{L:RL:RLM1:winner", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winer}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary right part reference for field in match with ID "match_id": "{L:RL:RLM1:winer}"', $e->getMessage());
            $this->assertEquals('Invalid Match result in reference {L:RL:RLM1:winer}: reference must be one of "winner"|"loser" in stage:group:match with IDs "L:RL:RLM1"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}==L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary right part reference for field in match with ID "match_id": "L:RL:RLM1:winner}"', $e->getMessage());
            $this->assertEquals('Invalid team reference format "L:RL:RLM1:winner}", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={S:league:2}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary right part reference for field in match with ID "match_id": "{S:league:2}"', $e->getMessage());
            $this->assertEquals('Invalid team reference format "{S:league:2}", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={NAN:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary right part reference for field in match with ID "match_id": "{NAN:RL:RLM1:winner}"', $e->getMessage());
            $this->assertEquals('Invalid Stage part: Stage with ID "NAN" does not exist', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:NAN:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary right part reference for field in match with ID "match_id": "{L:NAN:RLM1:winner}"', $e->getMessage());
            $this->assertEquals('Invalid Group part: Group with ID "NAN" does not exist in stage with ID "L"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:NAN:winner}?{L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary right part reference for field in match with ID "match_id": "{L:RL:NAN:winner}"', $e->getMessage());
            $this->assertEquals('Invalid Match part in reference {L:RL:NAN:winner} : Match with ID "NAN" does not exist in stage:group with IDs "L:RL"', $e->getPrevious()->getMessage());
        }
    }

    public function testCompetitionValidateTeamIDBadTernaryTrueTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-complete.json');

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary true team reference for field in match with ID "match_id": "{L"', $e->getMessage());
                $this->assertEquals('Team with ID "{L" does not exist', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:RLM1:winer}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary true team reference for field in match with ID "match_id": "{L:RL:RLM1:winer}"', $e->getMessage());
            $this->assertEquals('Invalid Match result in reference {L:RL:RLM1:winer}: reference must be one of "winner"|"loser" in stage:group:match with IDs "L:RL:RLM1"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?L:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary true team reference for field in match with ID "match_id": "L"', $e->getMessage());
            $this->assertEquals('Team with ID "L" does not exist', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{S:league:2}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary true team reference for field in match with ID "match_id": "{S:league:2}"', $e->getMessage());
            $this->assertEquals('Invalid team reference format "{S:league:2}", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{NAN:RL:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary true team reference for field in match with ID "match_id": "{NAN:RL:league:1}"', $e->getMessage());
            $this->assertEquals('Invalid Stage part: Stage with ID "NAN" does not exist', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:NAN:league:1}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary true team reference for field in match with ID "match_id": "{L:NAN:league:1}"', $e->getMessage());
            $this->assertEquals('Invalid Group part: Group with ID "NAN" does not exist in stage with ID "L"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:NAN:winner}:{L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary true team reference for field in match with ID "match_id": "{L:RL:NAN:winner}"', $e->getMessage());
            $this->assertEquals('Invalid Match part in reference {L:RL:NAN:winner} : Match with ID "NAN" does not exist in stage:group with IDs "L:RL"', $e->getPrevious()->getMessage());
        }
    }

    public function testCompetitionValidateTeamIDBadTernaryFalseTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-complete.json');

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:league:2', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary false team reference for field in match with ID "match_id": "{L:RL:league:2"', $e->getMessage());
            $this->assertEquals('Invalid team reference format "{L:RL:league:2", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:RLM1:winer}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary false team reference for field in match with ID "match_id": "{L:RL:RLM1:winer}"', $e->getMessage());
            $this->assertEquals('Invalid Match result in reference {L:RL:RLM1:winer}: reference must be one of "winner"|"loser" in stage:group:match with IDs "L:RL:RLM1"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:L:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary false team reference for field in match with ID "match_id": "L:RL:league:2}"', $e->getMessage());
            $this->assertEquals('Team with ID "L:RL:league:2}" does not exist', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{S:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary false team reference for field in match with ID "match_id": "{S:league:2}"', $e->getMessage());
            $this->assertEquals('Invalid team reference format "{S:league:2}", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{NAN:RL:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary false team reference for field in match with ID "match_id": "{NAN:RL:league:2}"', $e->getMessage());
            $this->assertEquals('Invalid Stage part: Stage with ID "NAN" does not exist', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:NAN:league:2}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary false team reference for field in match with ID "match_id": "{L:NAN:league:2}"', $e->getMessage());
            $this->assertEquals('Invalid Group part: Group with ID "NAN" does not exist in stage with ID "L"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?{L:RL:league:1}:{L:RL:NAN:winner}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary false team reference for field in match with ID "match_id": "{L:RL:NAN:winner}"', $e->getMessage());
            $this->assertEquals('Invalid Match part in reference {L:RL:NAN:winner} : Match with ID "NAN" does not exist in stage:group with IDs "L:RL"', $e->getPrevious()->getMessage());
        }

        try {
            $competition->validateTeamID('{L:RL:league:1}=={L:RL:RLM1:winner}?TM1:{L:RL:NAN:winner}', 'match_id', 'field');
            $this->fail('Test should have caught a bad ternary reference');
        } catch (Exception $e) {
            $this->assertEquals('Invalid ternary false team reference for field in match with ID "match_id": "{L:RL:NAN:winner}"', $e->getMessage());
            $this->assertEquals('Invalid Match part in reference {L:RL:NAN:winner} : Match with ID "NAN" does not exist in stage:group with IDs "L:RL"', $e->getPrevious()->getMessage());
        }
    }

    public function testCompetitionTernaryReferenceToThisStageGroup() : void
    {
        $this->expectNotToPerformAssertions();
        // We should be able to load a competition including ternaries that refer the group those ternary references are in
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-ternary-refers-to-this-stage-group.json');
    }

    public function testCompetitionWithNotes() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-with-notes.json');
        $this->assertEquals('This is a note', $competition->getNotes());
        $competition->setNotes('This is a new note');
        $this->assertEquals('This is a new note', $competition->getNotes());
    }

    public function testCompetitionWithoutNotes() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-without-notes.json');
        $this->assertNull($competition->getNotes());
    }

    // public function testCompetitionAddTeam() : void
    // {
    //     $competition = new Competition(CompetitionTest::EMPTY_COMPETITION);
    //     $this->assertCount(0, $competition->getTeams());

    //     $competition->addTeam('TM1', 'Team 1');
    //     $this->assertCount(1, $competition->getTeams());
    //     $this->assertEquals('Team 1', $competition->getTeamByID('TM1')->getName());
    //     $this->assertEquals('Team 1', $competition->getTeams()[0]->getName());

    //     try {
    //         $competition->addTeam('', 'Team 2');
    //         $this->fail('Team creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Invalid team ID: must be between 1 and 100 characters long', $th->getMessage());
    //     }

    //     try {
    //         $competition->addTeam('01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891', 'Team 2');
    //         $this->fail('Team creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Invalid team ID: must be between 1 and 100 characters long', $th->getMessage());
    //     }

    //     try {
    //         $competition->addTeam('TM:', 'Team 2');
    //         $this->fail('Team creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Invalid team ID: must contain only ASCII printable characters excluding " : { } ? =', $th->getMessage());
    //     }

    //     try {
    //         $competition->addTeam('TM1', 'Team 1');
    //         $this->fail('Team creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Team with ID "TM1" already exists', $th->getMessage());
    //     }

    //     try {
    //         $competition->addTeam('TM2', '');
    //         $this->fail('Team creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Invalid team name: must be between 1 and 1000 characters long', $th->getMessage());
    //     }

    //     try {
    //         $team_name = '';
    //         for ($i=0; $i < 101; $i++) {
    //             $team_name .= '0123456789';
    //         }
    //         $competition->addTeam('TM2', $team_name);
    //         $this->fail('Team creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Invalid team name: must be between 1 and 1000 characters long', $th->getMessage());
    //     }
    // }

    // public function testCompetitionAddStage() : void
    // {
    //     $competition = new Competition(CompetitionTest::EMPTY_COMPETITION);
    //     $this->assertCount(0, $competition->getStages());

    //     $competition->addStage('STG1')->setName('Stage 1');
    //     $this->assertCount(1, $competition->getStages());
    //     $this->assertEquals('Stage 1', $competition->getStageByID('STG1')->getName());
    //     $this->assertEquals('Stage 1', $competition->getStages()[0]->getName());

    //     try {
    //         $competition->addStage('');
    //         $this->fail('Stage creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Invalid stage ID: must be between 1 and 100 characters long', $th->getMessage());
    //     }

    //     try {
    //         $competition->addStage('01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567891');
    //         $this->fail('Stage creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Invalid stage ID: must be between 1 and 100 characters long', $th->getMessage());
    //     }

    //     try {
    //         $competition->addStage('STG:');
    //         $this->fail('Stage creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Invalid stage ID: must contain only ASCII printable characters excluding " : { } ? =', $th->getMessage());
    //     }

    //     try {
    //         $competition->addStage('STG1');
    //         $this->fail('Stage creation should fail');
    //     } catch (Throwable $th) {
    //         $this->assertEquals('Stage with ID "STG1" already exists', $th->getMessage());
    //     }
    // }
}
