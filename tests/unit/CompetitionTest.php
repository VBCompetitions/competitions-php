<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;

use Exception;
use Throwable;
use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Club;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\CompetitionContact;
use VBCompetitions\Competitions\CompetitionContactRole;
use VBCompetitions\Competitions\CompetitionTeam;
use VBCompetitions\Competitions\GroupMatch;
use VBCompetitions\Competitions\League;
use VBCompetitions\Competitions\LeagueConfig;
use VBCompetitions\Competitions\LeagueConfigPoints;
use VBCompetitions\Competitions\MatchTeam;
use VBCompetitions\Competitions\MatchOfficials;
use VBCompetitions\Competitions\Stage;
use stdClass;
use VBCompetitions\Competitions\MatchType;

#[CoversClass(Competition::class)]
#[CoversClass(CompetitionTeam::class)]
#[CoversClass(Stage::class)]
#[CoversClass(Club::class)]
final class CompetitionTest extends TestCase {
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
        $kv = new stdClass();
        $kv->key = 'someKey';
        $kv->value = 'someValue';
        $other->metadata = [$kv];
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
        $this->expectNotToPerformAssertions();
        set_error_handler(function () {
            return;
        });

        try {
            Competition::loadFromFile(realpath(__DIR__), 'competitions');
            restore_error_handler();
            $this->fail('File loading should have failed');
        } catch (Throwable $_) {
            restore_error_handler();
        }
    }

    public function testMetadataDuplicateKey() : void
    {
        $this->expectExceptionMessage('Metadata with key "someKey" already exists in the competition');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-duplicate-metadata-key.json');
    }

    public function testClubsDuplicateID() : void
    {
        $this->expectExceptionMessage('Club with ID "NOR" already exists in the competition');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-duplicate-club-ids.json');
    }

    public function testCompetitionDuplicateTeamIDs() : void
    {
        $this->expectExceptionMessage('Team with ID "TM1" already exists in the competition');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-duplicate-team-ids.json');
    }

    public function testCompetitionDuplicatePlayerID() : void
    {
        $this->expectExceptionMessage('Player with ID "P1" already exists in the competition');
        Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-duplicate-player-ids.json');
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
        $matchF1 = $competition->getStage('F')->getGroup('F')->getMatch('F1');
        $matchF2 = $competition->getStage('F')->getGroup('F')->getMatch('F2');

        $truthyTeam = $competition->getTeam($matchF1->getHomeTeam()->getID());
        $falsyTeam = $competition->getTeam($matchF1->getAwayTeam()->getID());

        $truthyTeamRef = $competition->getTeam($matchF2->getHomeTeam()->getID());
        $falsyTeamRef = $competition->getTeam($matchF2->getAwayTeam()->getID());

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
        $stage = $competition->getStage('L');
        $this->assertEquals('league', $stage->getName());
    }

    public function testCompetitionGetStageLookupFails() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition.json');
        $this->expectExceptionMessage('Stage with ID NO-STAGE not found');
        $competition->getStage('NO-STAGE');
    }

    public function testCompetitionGetTeamLookupsIncomplete() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition.json');

        $this->assertFalse($competition->isComplete());
        $this->assertTrue($competition->hasTeam('TM1'));
        $this->assertTrue($competition->hasTeam('TM8'));

        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:league:1}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:league:2}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:league:3}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:league:4}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:league:5}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:league:6}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:league:7}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:league:8}')->getID());

        $this->assertEquals('TM2', $competition->getTeam('{L:RL:RLM1:winner}')->getID());
        $this->assertEquals('TM1', $competition->getTeam('{L:RL:RLM1:loser}')->getID());

        $this->assertFalse($competition->hasTeam('NO-SUCH-TEAM'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('NO-SUCH-TEAM')->getID());

        $this->assertFalse($competition->hasTeam('{NO:SUCH:TEAM:REF}'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{NO:SUCH:TEAM:REF}')->getID());
    }

    public function testCompetitionGetTeamLookupsInvalid() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition.json');
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{L:RL:RLM1:foo}')->getID());
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{KO:CUP:QF1:foo}')->getID());
    }

    public function testCompetitionGetTeamLookupsComplete() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-complete.json');
        $this->assertEquals('1.0.0', $competition->getVersion());

        $this->assertTrue($competition->isComplete());
        $this->assertTrue($competition->hasTeam('TM1'));
        $this->assertTrue($competition->hasTeam('TM8'));

        $this->assertEquals('TM6', $competition->getTeam('{L:RL:league:1}')->getID());
        $this->assertEquals('TM5', $competition->getTeam('{L:RL:league:2}')->getID());
        $this->assertEquals('TM2', $competition->getTeam('{L:RL:league:3}')->getID());
        $this->assertEquals('TM4', $competition->getTeam('{L:RL:league:4}')->getID());
        $this->assertEquals('TM3', $competition->getTeam('{L:RL:league:5}')->getID());
        $this->assertEquals('TM7', $competition->getTeam('{L:RL:league:6}')->getID());
        $this->assertEquals('TM8', $competition->getTeam('{L:RL:league:7}')->getID());
        $this->assertEquals('TM1', $competition->getTeam('{L:RL:league:8}')->getID());

        $this->assertEquals('TM2', $competition->getTeam('{L:RL:RLM1:winner}')->getID());
        $this->assertEquals('TM1', $competition->getTeam('{L:RL:RLM1:loser}')->getID());

        $this->assertFalse($competition->hasTeam('NO-SUCH-TEAM'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('NO-SUCH-TEAM')->getID());

        $this->assertFalse($competition->hasTeam('{NO:SUCH:TEAM:REF}'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('{NO:SUCH:TEAM:REF}')->getID());
    }

    public function testCompetitionStageWithNoName() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-stages-no-name.json');

        $this->assertNull($competition->getStage('L0')->getName());
        $this->assertEquals('league', $competition->getStage('L1')->getName());
        $this->assertNull($competition->getStage('L2')->getName());
    }

    public function testCompetitionIncompleteWithTeamReferences() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'competitions'))), 'competition-half-done-with-references.json');

        $this->assertFalse($competition->getStage('Divisions')->getGroup('Division 1')->getMatch('D1M1')->isComplete());
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

    public function testCompetitionConstructorBadName() : void
    {
        try {
            new Competition('');
            $this->fail('Test should have caught a zero-length competition name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid competition name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        try {
            $name = 'a';
            for ($i=0; $i < 100; $i++) {
                $name .= '0123456789';
            }
            new Competition($name);
            $this->fail('Test should have caught a long competition name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid competition name: must be between 1 and 1000 characters long', $e->getMessage());
        }
    }

    public function testCompetitionAddTeam() : void
    {
        $competition_1 = new Competition('test competition 1');
        $competition_2 = new Competition('test competition 1');
        $this->assertCount(0, $competition_1->getTeams());
        $this->assertCount(0, $competition_2->getTeams());

        $team = new CompetitionTeam($competition_1, 'TM1', 'Team 1');
        $this->assertCount(0, $competition_1->getTeams());
        $this->assertCount(0, $competition_2->getTeams());

        try {
            $competition_2->addTeam($team);
            $this->fail('Test should have caught adding team to wrong competition');
        } catch (Exception $e) {
            $this->assertEquals('Team was initialised with a different Competition', $e->getMessage());
        }

        $competition_1->addTeam($team);
        $this->assertCount(1, $competition_1->getTeams());
        $this->assertCount(0, $competition_2->getTeams());

        $competition_1->addTeam($team);
        $this->assertCount(1, $competition_1->getTeams());
    }

    public function testCompetitionDeleteTeam() : void
    {
        $competition = new Competition('test competition');
        $team_1 = new CompetitionTeam($competition, 'T1', 'Team 1');
        $team_2 = new CompetitionTeam($competition, 'T2', 'Team 2');
        $team_3 = new CompetitionTeam($competition, 'T3', 'Team 3');
        $team_4 = new CompetitionTeam($competition, 'T4', 'Team 4');
        $competition->addTeam($team_1)->addTeam($team_2)->addTeam($team_3)->addTeam($team_4);
        $club_1 = new Club($competition, 'C1', 'Club 1');
        $club_2 = new Club($competition, 'C2', 'Club 2');
        $competition->addClub($club_1)->addClub($club_2);
        $club_1->addTeam($team_1)->addTeam($team_2);
        $club_2->addTeam($team_3)->addTeam($team_4);
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $league = new League($stage, 'G', MatchType::CONTINUOUS, false);
        $stage->addGroup($league);
        $match_1 = new GroupMatch($league, 'M1');
        $match_1->setHomeTeam(new MatchTeam($match_1, $team_1->getID()))->setAwayTeam(new MatchTeam($match_1, $team_2->getID()))->setOfficials(new MatchOfficials($match_1, $team_3->getID()));
        $match_2 = new GroupMatch($league, 'M2');
        $match_2->setHomeTeam(new MatchTeam($match_2, $team_2->getID()))->setAwayTeam(new MatchTeam($match_2, $team_1->getID()))->setOfficials(new MatchOfficials($match_2, $team_3->getID()));
        $league->addMatch($match_1)->addMatch($match_2);
        $league_config = new LeagueConfig($league);
        $league->setLeagueConfig($league_config);
        $league_config->setOrdering(['PTS', 'PD']);
        $league_config_points = new LeagueConfigPoints($league_config);
        $league_config->setPoints($league_config_points);

        // Team with known matches cannot be deleted
        try {
            $competition->deleteTeam($team_1->getID());
            $this->fail('Test should have caught deleting a team with matches');
        } catch (Exception $e) {
            $this->assertEquals('Team still has matches with IDs: {S:G:M1}, {S:G:M2}', $e->getMessage());
        }

        try {
            $competition->deleteTeam($team_2->getID());
            $this->fail('Test should have caught deleting a team with matches');
        } catch (Exception $e) {
            $this->assertEquals('Team still has matches with IDs: {S:G:M1}, {S:G:M2}', $e->getMessage());
        }

        try {
            $competition->deleteTeam($team_3->getID());
            $this->fail('Test should have caught deleting a team with officiating duties');
        } catch (Exception $e) {
            $this->assertEquals('Team still has matches with IDs: {S:G:M1}, {S:G:M2}', $e->getMessage());
        }

        $this->assertEquals(4, count($competition->getTeams()));
        $competition->deleteTeam($team_4->getID());
        $this->assertFalse($competition->hasTeam('T4'));
        $this->assertEquals(CompetitionTeam::UNKNOWN_TEAM_ID, $competition->getTeam('T4')->getID());
        $this->assertEquals(3, count($competition->getTeams()));

        $competition->deleteTeam('undefined-team-id');
    }

    public function testCompetitionDeleteClub() : void
    {
        $competition = new Competition('test competition');
        $team_1 = new CompetitionTeam($competition, 'T1', 'Team 1');
        $team_2 = new CompetitionTeam($competition, 'T2', 'Team 2');
        $team_3 = new CompetitionTeam($competition, 'T3', 'Team 3');
        $team_4 = new CompetitionTeam($competition, 'T4', 'Team 4');
        $competition->addTeam($team_1)->addTeam($team_2)->addTeam($team_3)->addTeam($team_4);
        $club_1 = new Club($competition, 'C1', 'Club 1');
        $club_2 = new Club($competition, 'C2', 'Club 2');
        $competition->addClub($club_1)->addClub($club_2);
        $club_1->addTeam($team_1)->addTeam($team_2);
        $club_2->addTeam($team_3);
        $stage = new Stage($competition, 'S');
        $competition->addStage($stage);
        $league = new League($stage, 'G', MatchType::CONTINUOUS, false);
        $stage->addGroup($league);
        $match_1 = new GroupMatch($league, 'M1');
        $match_1->setHomeTeam(new MatchTeam($match_1, $team_1->getID()))->setAwayTeam(new MatchTeam($match_1, $team_2->getID()))->setOfficials(new MatchOfficials($match_1, $team_3->getID()));
        $match_2 = new GroupMatch($league, 'M2');
        $match_2->setHomeTeam(new MatchTeam($match_2, $team_2->getID()))->setAwayTeam(new MatchTeam($match_2, $team_1->getID()))->setOfficials(new MatchOfficials($match_2, $team_3->getID()));
        $league->addMatch($match_1)->addMatch($match_2);
        $league_config = new LeagueConfig($league);
        $league->setLeagueConfig($league_config);
        $league_config->setOrdering(['PTS', 'PD']);
        $league_config_points = new LeagueConfigPoints($league_config);
        $league_config->setPoints($league_config_points);

        $this->assertEquals('C1', $team_1->getClub()->getID());
        $this->assertEquals('C1', $team_2->getClub()->getID());
        $this->assertNull($team_4->getClub());

        // Club with teams cannot be deleted
        try {
            $competition->deleteClub($club_1->getID());
            $this->fail('Test should have caught deleting a team still in a club');
        } catch (Exception $e) {
            $this->assertEquals('Club still contains teams with IDs: {T1}, {T2}', $e->getMessage());
        }

        try {
            $competition->deleteClub($club_2->getID());
            $this->fail('Test should have caught deleting a team still in a club');
        } catch (Exception $e) {
            $this->assertEquals('Club still contains teams with IDs: {T3}', $e->getMessage());
        }

        $club_1->deleteTeam('T1');
        $club_1->deleteTeam('T2');
        $this->assertEquals(2, count($competition->getClubs()));
        $competition->deleteClub($club_1->getID());
        $this->assertFalse($competition->hasClub('C1'));
        $this->assertEquals(1, count($competition->getClubs()));
        try {
            $competition->getClub('C1');
            $this->fail('Test should have caught getting a club that does not exist');
        } catch (Exception $e) {
            $this->assertEquals('Club with ID "C1" not found', $e->getMessage());
        }

        $competition->deleteClub('undefined-club-id');
    }

    public function testCompetitionSetters() : void
    {
        $competition = new Competition('test competition');

        try {
            $competition->setName('');
            $this->fail('Test should have caught setting an empty competition name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid competition name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        $name = 'a';
        for ($i=0; $i < 100; $i++) {
            $name .= '0123456789';
        }
        try {
            $competition->setName($name);
            $this->fail('Test should have caught setting a long competition name');
        } catch (Exception $e) {
            $this->assertEquals('Invalid competition name: must be between 1 and 1000 characters long', $e->getMessage());
        }

        try {
            $competition->setNotes('');
            $this->fail('Test should have caught setting a empty competition notes');
        } catch (Exception $e) {
            $this->assertEquals('Invalid competition notes: must be at least 1 character long', $e->getMessage());
        }
    }

    public function testCompetitionAddStage() : void
    {
        $competition_1 = new Competition('test competition 1');
        $competition_2 = new Competition('test competition 1');
        $this->assertCount(0, $competition_1->getStages());
        $this->assertCount(0, $competition_2->getStages());

        $stage = new Stage($competition_1, 'STG');
        $this->assertCount(0, $competition_1->getStages());
        $this->assertCount(0, $competition_2->getStages());

        try {
            $competition_2->addStage($stage);
            $this->fail('Test should have caught adding stage to wrong competition');
        } catch (Exception $e) {
            $this->assertEquals('Stage was initialised with a different Competition', $e->getMessage());
        }

        $competition_1->addStage($stage);
        $this->assertCount(1, $competition_1->getStages());
        $this->assertCount(0, $competition_2->getStages());
    }

    public function testCompetitionDeleteStage() : void
    {
        $competition = new Competition('test competition');
        $team_1 = new CompetitionTeam($competition, 'T1', 'Team 1');
        $team_2 = new CompetitionTeam($competition, 'T2', 'Team 2');
        $team_3 = new CompetitionTeam($competition, 'T3', 'Team 3');
        $competition->addTeam($team_1)->addTeam($team_2)->addTeam($team_3);
        $stage_1 = new Stage($competition, 'S1');
        $competition->addStage($stage_1);
        $league_1 = new League($stage_1, 'G1', MatchType::CONTINUOUS, false);
        $stage_1->addGroup($league_1);
        $match_1 = new GroupMatch($league_1, 'M1');
        $match_1->setHomeTeam(new MatchTeam($match_1, $team_1->getID()))->setAwayTeam(new MatchTeam($match_1, $team_2->getID()))->setOfficials(new MatchOfficials($match_1, $team_3->getID()));
        $league_1->addMatch($match_1);

        $stage_2 = new Stage($competition, 'S2');
        $competition->addStage($stage_2);
        $league_2 = new League($stage_2, 'G2', MatchType::CONTINUOUS, false);
        $stage_2->addGroup($league_2);
        $match_2 = new GroupMatch($league_1, 'M2');
        $match_2->setHomeTeam(new MatchTeam($match_2, $team_3->getID()))->setAwayTeam(new MatchTeam($match_2, '{S1:G1:M1:winner}'))->setOfficials(new MatchOfficials($match_2, '{S1:G1:M1:winner}=={S1:G1:M1:winner}?{S1:G1:M1:winner}:{S1:G1:M1:loser}'));
        $league_2->addMatch($match_2);

        try {
            $competition->deleteStage($stage_1->getID());
            $this->fail('Test should have caught deleting a stage with later references');
        } catch (Exception $e) {
            $this->assertEquals('Cannot delete stage with id "S1" as it is referenced in match {S2:G2:M2}', $e->getMessage());
        }

        $competition->deleteStage($stage_2->getID());
        $this->assertEquals('S1', $competition->getStage('S1')->getID());
        $this->assertEquals('S1', $competition->getStages()[0]->getID());
        try {
            $competition->getStage('S2');
            $this->fail('Test should have caught deleting the wrong stage');
        } catch (Exception $e) {
            $this->assertEquals('Stage with ID S2 not found', $e->getMessage());
        }

        $competition->deleteStage($stage_2->getID());
        $competition->deleteStage($stage_1->getID());
    }

    public function testCompetitionAddClub() : void
    {
        $competition_1 = new Competition('test competition 1');
        $competition_2 = new Competition('test competition 1');
        $this->assertCount(0, $competition_1->getClubs());
        $this->assertCount(0, $competition_2->getClubs());

        $stage = new Club($competition_1, 'CLB1', 'Club 1');
        $this->assertCount(0, $competition_1->getClubs());
        $this->assertCount(0, $competition_2->getClubs());

        try {
            $competition_2->addClub($stage);
            $this->fail('Test should have caught adding club to wrong competition');
        } catch (Exception $e) {
            $this->assertEquals('Club was initialised with a different Competition', $e->getMessage());
        }

        $competition_1->addClub($stage);
        $this->assertCount(1, $competition_1->getClubs());
        $this->assertCount(0, $competition_2->getClubs());
    }

    public function testCompetitionMetadataList() : void
    {
        $all = Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata'))));
        $this->assertCount(6, $all);
        $good_files = array_values(array_filter($all, fn($el): bool => $el->is_valid));
        $this->assertCount(5, $good_files);

        $season_matcher = new stdClass();
        $season_matcher->season = '2023-2024';
        $season_23_24 = Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata'))), $season_matcher);
        $this->assertCount(2, $season_23_24);
        $this->assertContains('competition-metadata-season-2324.json', array_map(fn($el): string => $el->file, $season_23_24));
        $this->assertContains('competition-metadata-season-2324-mixed.json', array_map(fn($el): string => $el->file, $season_23_24));

        $mixed_matcher = new stdClass();
        $mixed_matcher->season = '2023-2024';
        $mixed_matcher->league = 'mixed';
        $mixed_season = Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata'))), $mixed_matcher);
        $this->assertCount(1, $mixed_season);
        $this->assertContains('competition-metadata-season-2324-mixed.json', array_map(fn($el): string => $el->file, $mixed_season));

        $example_matcherA = new stdClass();
        $example_matcherA->season = '23/24';
        $matcherA_list = Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata', 'docs-example'))), $example_matcherA);
        $this->assertCount(3, $matcherA_list);
        $this->assertContains('competition-season.json', array_map(fn($el): string => $el->file, $matcherA_list));
        $this->assertContains('competition-season-archived.json', array_map(fn($el): string => $el->file, $matcherA_list));
        $this->assertContains('competition-season-not-archived.json', array_map(fn($el): string => $el->file, $matcherA_list));

        $example_matcherB = new stdClass();
        $example_matcherB->{'!archived'} = 'true';
        $matcherB_list = Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata', 'docs-example'))), $example_matcherB);
        $this->assertCount(3, $matcherB_list);
        $this->assertContains('competition-season.json', array_map(fn($el): string => $el->file, $matcherB_list));
        $this->assertContains('competition-season-not-archived.json', array_map(fn($el): string => $el->file, $matcherB_list));
        $this->assertContains('competition-no-metadata.json', array_map(fn($el): string => $el->file, $matcherB_list));

        $example_matcherC = new stdClass();
        $example_matcherC->season = '23/24';
        $example_matcherC->{'!archived'} = 'true';
        $matcherC_list = Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata', 'docs-example'))), $example_matcherC);
        $this->assertCount(2, $matcherC_list);
        $this->assertContains('competition-season.json', array_map(fn($el): string => $el->file, $matcherC_list));
        $this->assertContains('competition-season-not-archived.json', array_map(fn($el): string => $el->file, $matcherC_list));

        try {
            $short_key = '';
            $bad_match = new stdClass();
            $bad_match->$short_key = 'x';
            Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata'))), $bad_match);
            $this->fail('listing should fail on an empty key');
        } catch (Exception $e) {
            $this->assertEquals('Invalid metadata search key "": must be between 1 and 100 characters long', $e->getMessage());
        }

        $long_key = 'a';
        for ($i=0; $i < 100; $i++) {
            $long_key .= '0123456789';
        }
        try {
            $bad_match = new stdClass();
            $bad_match->$long_key = 'x';
            Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata'))), $bad_match);
            $this->fail('listing should fail on a long key');
        } catch (Exception $e) {
            $this->assertEquals('Invalid metadata search key "'.$long_key.'": must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            $bad_match = new stdClass();
            $bad_match->foo = '';
            Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata'))), $bad_match);
            $this->fail('listing should fail on an empty value');
        } catch (Exception $e) {
            $this->assertEquals('Invalid metadata search value "": must be between 1 and 1000 characters long', $e->getMessage());
        }

        $long_val = 'a';
        for ($i=0; $i < 100; $i++) {
            $long_val .= '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789';
        }
        try {
            $bad_match = new stdClass();
            $bad_match->foo = $long_val;
            Competition::competitionList(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'metadata'))), $bad_match);
            $this->fail('listing should fail on a long value');
        } catch (Exception $e) {
            $this->assertEquals('Invalid metadata search value "'.$long_val.'": must be between 1 and 1000 characters long', $e->getMessage());
        }
    }

    public function testCompetitionMetadataFunctions() : void
    {
        $competition = new Competition('test');
        $this->assertFalse($competition->hasMetadata());
        $this->assertNull($competition->getMetadata());
        $this->assertFalse($competition->hasMetadataByKey('foo'));
        $this->assertNull($competition->getMetadataByKey('foo'));
        $this->assertFalse($competition->hasMetadataByKey('bar'));
        $this->assertNull($competition->getMetadataByKey('bar'));

        $competition->setMetadataByKey('foo', 'bar');
        $this->assertTrue($competition->hasMetadata());
        $this->assertCount(1, $competition->getMetadata());
        $this->assertTrue($competition->hasMetadataByKey('foo'));
        $this->assertEquals('bar', $competition->getMetadataByKey('foo'));
        $this->assertFalse($competition->hasMetadataByKey('bar'));
        $this->assertNull($competition->getMetadataByKey('bar'));

        $competition->setMetadataByKey('bar', 'baz');
        $this->assertTrue($competition->hasMetadata());
        $this->assertCount(2, $competition->getMetadata());
        $this->assertTrue($competition->hasMetadataByKey('foo'));
        $this->assertEquals('bar', $competition->getMetadataByKey('foo'));
        $this->assertTrue($competition->hasMetadataByKey('bar'));
        $this->assertEquals('baz', $competition->getMetadataByKey('bar'));

        $competition->setMetadataByKey('foo', 'bar');
        $this->assertTrue($competition->hasMetadata());
        $this->assertCount(2, $competition->getMetadata());
        $this->assertTrue($competition->hasMetadataByKey('foo'));
        $this->assertEquals('bar', $competition->getMetadataByKey('foo'));
        $this->assertTrue($competition->hasMetadataByKey('bar'));
        $this->assertEquals('baz', $competition->getMetadataByKey('bar'));

        $competition->deleteMetadataByKey('foo');
        $this->assertTrue($competition->hasMetadata());
        $this->assertCount(1, $competition->getMetadata());
        $this->assertFalse($competition->hasMetadataByKey('foo'));
        $this->assertNull($competition->getMetadataByKey('foo'));
        $this->assertTrue($competition->hasMetadataByKey('bar'));
        $this->assertEquals('baz', $competition->getMetadataByKey('bar'));

        $competition->deleteMetadataByKey('bar');
        $this->assertFalse($competition->hasMetadata());
        $this->assertNull($competition->getMetadata());
        $this->assertFalse($competition->hasMetadataByKey('foo'));
        $this->assertNull($competition->getMetadataByKey('foo'));
        $this->assertFalse($competition->hasMetadataByKey('bar'));
        $this->assertNull($competition->getMetadataByKey('bar'));

        $competition->deleteMetadataByKey('foo');
        $this->assertFalse($competition->hasMetadata());
        $this->assertNull($competition->getMetadata());
        $this->assertFalse($competition->hasMetadataByKey('foo'));
        $this->assertNull($competition->getMetadataByKey('foo'));
        $this->assertFalse($competition->hasMetadataByKey('bar'));
        $this->assertNull($competition->getMetadataByKey('bar'));

        try {
            $competition->setMetadataByKey('', 'bar');
            $this->fail('adding metadata should fail on an empty key');
        } catch (Exception $e) {
            $this->assertEquals('Invalid metadata key: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            $long_key = 'a';
            for ($i=0; $i < 100; $i++) {
                $long_key .= '0123456789';
            }
            $competition->setMetadataByKey($long_key, 'bar');
            $this->fail('adding metadata should fail on a long key');
        } catch (Exception $e) {
            $this->assertEquals('Invalid metadata key: must be between 1 and 100 characters long', $e->getMessage());
        }

        try {
            $competition->setMetadataByKey('foo', '');
            $this->fail('adding metadata should fail on an empty value');
        } catch (Exception $e) {
            $this->assertEquals('Invalid metadata value: must be between 1 and 1000 characters long', $e->getMessage());
        }

        try {
            $long_value = 'a';
            for ($i=0; $i < 1000; $i++) {
                $long_value .= '0123456789';
            }
            $competition->setMetadataByKey('foo', $long_value);
            $this->fail('adding metadata should fail on a long value');
        } catch (Exception $e) {
            $this->assertEquals('Invalid metadata value: must be between 1 and 1000 characters long', $e->getMessage());
        }
    }

    public function testCompetitionContacts() : void
    {
        $competition1 = new Competition('test');
        $contact1 = new CompetitionContact($competition1, 'C1', [CompetitionContactRole::SECRETARY]);
        $competition1->addContact($contact1);

        $competition2 = new Competition('test2');
        $contact2 = new CompetitionContact($competition2, 'C1', [CompetitionContactRole::DIRECTOR]);

        try {
            $competition1->addContact($contact2);
            $this->fail('adding contact should fail with a macthing ID');
        } catch (Exception $e) {
            $this->assertEquals('competition contacts with duplicate IDs within a competition not allowed', $e->getMessage());
        }

        $this->assertCount(1, $competition1->getContacts());
        $competition1->deleteContact('C1');
        $this->assertFalse($competition1->hasContacts());
        $competition1->deleteContact('C1');
        $this->assertFalse($competition1->hasContacts());
    }
}
