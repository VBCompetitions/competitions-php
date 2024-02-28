<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;

use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\HTML;
use stdClass;

#[CoversClass(HTML::class)]
final class HTMLTest extends TestCase {
    public function testHTMLMatchesForHTMLPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $matches = HTML::getMatchesForHTML($competition->getStageById('L')->getGroupById('LG'));

        $this->assertEquals('vbc-match vbc-match-group-LG', $matches->class);

        $this->assertCount(8, $matches->headings);

        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->headings[0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->headings[0]->class);
        $this->assertEquals('MatchNo', $matches->headings[0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->headings[1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->headings[1]->class);
        $this->assertEquals('Court', $matches->headings[1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->headings[2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-group-LG', $matches->headings[2]->class);
        $this->assertEquals('Start', $matches->headings[2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->headings[3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->headings[3]->class);
        $this->assertEquals('Duration', $matches->headings[3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->headings[4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->headings[4]->class);
        $this->assertEquals('Home Team', $matches->headings[4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SCORE, $matches->headings[5]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG', $matches->headings[5]->class);
        $this->assertEquals('Score', $matches->headings[5]->text);
        $this->assertEquals(2, $matches->headings[5]->colspan);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->headings[6]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->headings[6]->class);
        $this->assertEquals('Away Team', $matches->headings[6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->headings[7]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->headings[7]->class);
        $this->assertEquals('Officials', $matches->headings[7]->text);

        $this->assertCount(7, $matches->rows);

        $this->assertCount(9, $matches->rows[0]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[0][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[0][0]->class);
        $this->assertEquals('LG1', $matches->rows[0][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[0][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[0][1]->class);
        $this->assertEquals('1', $matches->rows[0][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[0][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[0][2]->class);
        $this->assertEquals('09:20', $matches->rows[0][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[0][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[0][3]->class);
        $this->assertEquals('0:20', $matches->rows[0][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[0][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[0][4]->class);
        $this->assertEquals('Team 2', $matches->rows[0][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[0][5]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[0][5]->class);
        $this->assertEquals('21', $matches->rows[0][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[0][6]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[0][6]->class);
        $this->assertEquals('22', $matches->rows[0][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[0][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[0][7]->class);
        $this->assertEquals('Team 4', $matches->rows[0][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[0][8]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[0][8]->class);
        $this->assertEquals('Team 1', $matches->rows[0][8]->text);

        $this->assertCount(9, $matches->rows[1]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[1][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[1][0]->class);
        $this->assertEquals('LG2', $matches->rows[1][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[1][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[1][1]->class);
        $this->assertEquals('1', $matches->rows[1][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[1][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[1][2]->class);
        $this->assertEquals('10:20', $matches->rows[1][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[1][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[1][3]->class);
        $this->assertEquals('0:20', $matches->rows[1][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[1][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[1][4]->class);
        $this->assertEquals('Team 1', $matches->rows[1][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[1][5]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[1][5]->class);
        $this->assertEquals('22', $matches->rows[1][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[1][6]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[1][6]->class);
        $this->assertEquals('24', $matches->rows[1][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[1][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[1][7]->class);
        $this->assertEquals('Team 3', $matches->rows[1][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[1][8]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[1][8]->class);
        $this->assertEquals('Team 2', $matches->rows[1][8]->text);

        $this->assertCount(9, $matches->rows[2]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[2][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[2][0]->class);
        $this->assertEquals('LG3', $matches->rows[2][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[2][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[2][1]->class);
        $this->assertEquals('1', $matches->rows[2][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[2][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[2][2]->class);
        $this->assertEquals('11:20', $matches->rows[2][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[2][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[2][3]->class);
        $this->assertEquals('0:20', $matches->rows[2][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[2][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[2][4]->class);
        $this->assertEquals('Team 2', $matches->rows[2][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[2][5]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[2][5]->class);
        $this->assertEquals('23', $matches->rows[2][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[2][6]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[2][6]->class);
        $this->assertEquals('26', $matches->rows[2][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[2][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[2][7]->class);
        $this->assertEquals('Team 3', $matches->rows[2][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[2][8]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[2][8]->class);
        $this->assertEquals('Team 4', $matches->rows[2][8]->text);

        $this->assertCount(4, $matches->rows[3]);
        $this->assertEquals(HTML::MATCH_COLUMN_BREAK, $matches->rows[3][0]->column_id);
        $this->assertEquals('vbc-match-break', $matches->rows[3][0]->class);
        $this->assertEquals('', $matches->rows[3][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[3][1]->column_id);
        $this->assertEquals('vbc-match-start', $matches->rows[3][1]->class);
        $this->assertEquals('12:20', $matches->rows[3][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[3][2]->column_id);
        $this->assertEquals('vbc-match-duration', $matches->rows[3][2]->class);
        $this->assertEquals('1:00', $matches->rows[3][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_BREAK, $matches->rows[3][3]->column_id);
        $this->assertEquals('vbc-match-break', $matches->rows[3][3]->class);
        $this->assertEquals('Lunch break', $matches->rows[3][3]->text);

        $this->assertCount(9, $matches->rows[4]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[4][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[4][0]->class);
        $this->assertEquals('LG4', $matches->rows[4][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[4][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[4][1]->class);
        $this->assertEquals('1', $matches->rows[4][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[4][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[4][2]->class);
        $this->assertEquals('13:20', $matches->rows[4][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[4][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[4][3]->class);
        $this->assertEquals('0:20', $matches->rows[4][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[4][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[4][4]->class);
        $this->assertEquals('Team 1', $matches->rows[4][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[4][5]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[4][5]->class);
        $this->assertEquals('24', $matches->rows[4][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[4][6]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[4][6]->class);
        $this->assertEquals('28', $matches->rows[4][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[4][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[4][7]->class);
        $this->assertEquals('Team 4', $matches->rows[4][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[4][8]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[4][8]->class);
        $this->assertEquals('', $matches->rows[4][8]->text);

        $this->assertCount(9, $matches->rows[5]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[5][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[5][0]->class);
        $this->assertEquals('LG5', $matches->rows[5][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[5][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[5][1]->class);
        $this->assertEquals('1', $matches->rows[5][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[5][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[5][2]->class);
        $this->assertEquals('14:20', $matches->rows[5][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[5][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[5][3]->class);
        $this->assertEquals('0:20', $matches->rows[5][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[5][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[5][4]->class);
        $this->assertEquals('Team 3', $matches->rows[5][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[5][5]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[5][5]->class);
        $this->assertEquals('25', $matches->rows[5][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[5][6]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[5][6]->class);
        $this->assertEquals('30', $matches->rows[5][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[5][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[5][7]->class);
        $this->assertEquals('Team 4', $matches->rows[5][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[5][8]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[5][8]->class);
        $this->assertEquals('Team 2', $matches->rows[5][8]->text);

        $this->assertCount(9, $matches->rows[6]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[6][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[6][0]->class);
        $this->assertEquals('LG6', $matches->rows[6][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[6][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[6][1]->class);
        $this->assertEquals('1', $matches->rows[6][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[6][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[6][2]->class);
        $this->assertEquals('15:20', $matches->rows[6][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[6][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[6][3]->class);
        $this->assertEquals('0:20', $matches->rows[6][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[6][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[6][4]->class);
        $this->assertEquals('Team 1', $matches->rows[6][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[6][5]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[6][5]->class);
        $this->assertEquals('26', $matches->rows[6][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[6][6]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[6][6]->class);
        $this->assertEquals('32', $matches->rows[6][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[6][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[6][7]->class);
        $this->assertEquals('Team 2', $matches->rows[6][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[6][8]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[6][8]->class);
        $this->assertEquals('Team 4', $matches->rows[6][8]->text);
    }

    public function testHTMLMatchesForHTMLWithEverything() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league-everything.json');
        $matches = HTML::getMatchesForHTML($competition->getStageById('L')->getGroupById('LG'));

        $this->assertEquals('vbc-match vbc-match-group-LG', $matches->class);

        $this->assertCount(14, $matches->headings);

        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->headings[0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->headings[0]->class);
        $this->assertEquals('MatchNo', $matches->headings[0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->headings[1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->headings[1]->class);
        $this->assertEquals('Court', $matches->headings[1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_VENUE, $matches->headings[2]->column_id);
        $this->assertEquals('vbc-match-venue vbc-match-group-LG', $matches->headings[2]->class);
        $this->assertEquals('Venue', $matches->headings[2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DATE, $matches->headings[3]->column_id);
        $this->assertEquals('vbc-match-date vbc-match-group-LG', $matches->headings[3]->class);
        $this->assertEquals('Date', $matches->headings[3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_WARMUP, $matches->headings[4]->column_id);
        $this->assertEquals('vbc-match-warmup vbc-match-group-LG', $matches->headings[4]->class);
        $this->assertEquals('Warmup', $matches->headings[4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->headings[5]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-group-LG', $matches->headings[5]->class);
        $this->assertEquals('Start', $matches->headings[5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->headings[6]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->headings[6]->class);
        $this->assertEquals('Duration', $matches->headings[6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->headings[7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->headings[7]->class);
        $this->assertEquals('Home Team', $matches->headings[7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SCORE, $matches->headings[8]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG', $matches->headings[8]->class);
        $this->assertEquals('Score', $matches->headings[8]->text);
        $this->assertEquals(2, $matches->headings[8]->colspan);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->headings[9]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->headings[9]->class);
        $this->assertEquals('Away Team', $matches->headings[9]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->headings[10]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->headings[10]->class);
        $this->assertEquals('Officials', $matches->headings[10]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MVP, $matches->headings[11]->column_id);
        $this->assertEquals('vbc-match-mvp vbc-match-group-LG', $matches->headings[11]->class);
        $this->assertEquals('MVP', $matches->headings[11]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MANAGER, $matches->headings[12]->column_id);
        $this->assertEquals('vbc-match-manager vbc-match-group-LG', $matches->headings[12]->class);
        $this->assertEquals('Manager', $matches->headings[12]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_NOTES, $matches->headings[13]->column_id);
        $this->assertEquals('vbc-match-notes vbc-match-group-LG', $matches->headings[13]->class);
        $this->assertEquals('Notes', $matches->headings[13]->text);


        $this->assertCount(10, $matches->rows);

        // Blank line
        $this->assertCount(14, $matches->rows[0]);
        for ($i = 0; $i < 14; $i++) {
            $this->assertEquals(HTML::MATCH_COLUMN_BLANK, $matches->rows[0][$i]->column_id);
            $this->assertEquals('', $matches->rows[0][$i]->class);
            $this->assertEquals('&nbsp;', $matches->rows[0][$i]->text);
        }

        // Match LG1
        $this->assertCount(15, $matches->rows[1]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[1][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[1][0]->class);
        $this->assertEquals('LG1', $matches->rows[1][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[1][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[1][1]->class);
        $this->assertEquals('1', $matches->rows[1][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_VENUE, $matches->rows[1][2]->column_id);
        $this->assertEquals('vbc-match-venue vbc-match-group-LG', $matches->rows[1][2]->class);
        $this->assertEquals('City Sports Centre', $matches->rows[1][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DATE, $matches->rows[1][3]->column_id);
        $this->assertEquals('vbc-match-date vbc-match-played vbc-match-group-LG', $matches->rows[1][3]->class);
        $this->assertEquals('2023-06-21', $matches->rows[1][3]->text);
        $this->assertEquals('3', $matches->rows[1][3]->rowspan);
        $this->assertEquals(HTML::MATCH_COLUMN_WARMUP, $matches->rows[1][4]->column_id);
        $this->assertEquals('vbc-match-warmup vbc-match-played vbc-match-group-LG', $matches->rows[1][4]->class);
        $this->assertEquals('09:10', $matches->rows[1][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[1][5]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[1][5]->class);
        $this->assertEquals('09:20', $matches->rows[1][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[1][6]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[1][6]->class);
        $this->assertEquals('0:20', $matches->rows[1][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[1][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[1][7]->class);
        $this->assertEquals('Team 2', $matches->rows[1][7]->text);
        $this->assertEquals('A Adams', $matches->rows[1][7]->metadata["mvp"]);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[1][8]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[1][8]->class);
        $this->assertEquals('21', $matches->rows[1][8]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[1][9]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[1][9]->class);
        $this->assertEquals('22', $matches->rows[1][9]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[1][10]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[1][10]->class);
        $this->assertEquals('Team 4', $matches->rows[1][10]->text);
        $this->assertEquals('D Dodds', $matches->rows[1][10]->metadata["mvp"]);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[1][11]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[1][11]->class);
        $this->assertEquals('Team 1', $matches->rows[1][11]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MVP, $matches->rows[1][12]->column_id);
        $this->assertEquals('vbc-match-mvp vbc-match-group-LG', $matches->rows[1][12]->class);
        $this->assertEquals('A Adams', $matches->rows[1][12]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MANAGER, $matches->rows[1][13]->column_id);
        $this->assertEquals('vbc-match-manager vbc-match-group-LG', $matches->rows[1][13]->class);
        $this->assertEquals('Team 1', $matches->rows[1][13]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_NOTES, $matches->rows[1][14]->column_id);
        $this->assertEquals('vbc-match-notes vbc-match-group-LG', $matches->rows[1][14]->class);
        $this->assertEquals('some notes', $matches->rows[1][14]->text);

        // Match LG2
        $this->assertCount(14, $matches->rows[2]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[2][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[2][0]->class);
        $this->assertEquals('LG2', $matches->rows[2][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[2][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[2][1]->class);
        $this->assertEquals('1', $matches->rows[2][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_VENUE, $matches->rows[2][2]->column_id);
        $this->assertEquals('vbc-match-venue vbc-match-group-LG', $matches->rows[2][2]->class);
        $this->assertEquals('City Sports Centre', $matches->rows[2][2]->text);
        $this->assertFalse(isset($matches->rows[2][3]));
        $this->assertEquals(HTML::MATCH_COLUMN_WARMUP, $matches->rows[2][4]->column_id);
        $this->assertEquals('vbc-match-warmup vbc-match-played vbc-match-group-LG', $matches->rows[2][4]->class);
        $this->assertEquals('10:10', $matches->rows[2][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[2][5]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[2][5]->class);
        $this->assertEquals('10:20', $matches->rows[2][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[2][6]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[2][6]->class);
        $this->assertEquals('0:20', $matches->rows[1][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[2][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[2][7]->class);
        $this->assertEquals('Team 1', $matches->rows[2][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[2][8]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[2][8]->class);
        $this->assertEquals('22', $matches->rows[2][8]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[2][9]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[2][9]->class);
        $this->assertEquals('24', $matches->rows[2][9]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[2][10]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[2][10]->class);
        $this->assertEquals('Team 3', $matches->rows[2][10]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[2][11]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[2][11]->class);
        $this->assertEquals('Team 2', $matches->rows[2][11]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MVP, $matches->rows[2][12]->column_id);
        $this->assertEquals('vbc-match-mvp vbc-match-group-LG', $matches->rows[2][12]->class);
        $this->assertEquals('B Betts', $matches->rows[2][12]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MANAGER, $matches->rows[2][13]->column_id);
        $this->assertEquals('vbc-match-manager vbc-match-group-LG', $matches->rows[2][13]->class);
        $this->assertEquals('Team 2', $matches->rows[2][13]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_NOTES, $matches->rows[2][14]->column_id);
        $this->assertEquals('vbc-match-notes vbc-match-group-LG', $matches->rows[2][14]->class);
        $this->assertEquals('some notes', $matches->rows[2][14]->text);

        // Match LG3
        $this->assertCount(14, $matches->rows[3]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[3][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[3][0]->class);
        $this->assertEquals('LG3', $matches->rows[3][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[3][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[3][1]->class);
        $this->assertEquals('1', $matches->rows[3][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_VENUE, $matches->rows[3][2]->column_id);
        $this->assertEquals('vbc-match-venue vbc-match-group-LG', $matches->rows[3][2]->class);
        $this->assertEquals('City Sports Centre', $matches->rows[3][2]->text);
        $this->assertFalse(isset($matches->rows[3][3]));
        $this->assertEquals(HTML::MATCH_COLUMN_WARMUP, $matches->rows[3][4]->column_id);
        $this->assertEquals('vbc-match-warmup vbc-match-played vbc-match-group-LG', $matches->rows[3][4]->class);
        $this->assertEquals('11:10', $matches->rows[3][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[3][5]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[3][5]->class);
        $this->assertEquals('11:20', $matches->rows[3][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[3][6]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[3][6]->class);
        $this->assertEquals('0:20', $matches->rows[3][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[3][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[3][7]->class);
        $this->assertEquals('Team 2', $matches->rows[3][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[3][8]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[3][8]->class);
        $this->assertEquals('23', $matches->rows[3][8]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[3][9]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[3][9]->class);
        $this->assertEquals('26', $matches->rows[3][9]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[3][10]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[3][10]->class);
        $this->assertEquals('Team 3', $matches->rows[3][10]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[3][11]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[3][11]->class);
        $this->assertEquals('Team 4', $matches->rows[3][11]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MVP, $matches->rows[3][12]->column_id);
        $this->assertEquals('vbc-match-mvp vbc-match-group-LG', $matches->rows[3][12]->class);
        $this->assertEquals('C Crosier', $matches->rows[3][12]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MANAGER, $matches->rows[3][13]->column_id);
        $this->assertEquals('vbc-match-manager vbc-match-group-LG', $matches->rows[3][13]->class);
        $this->assertEquals('Team 4', $matches->rows[3][13]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_NOTES, $matches->rows[3][14]->column_id);
        $this->assertEquals('vbc-match-notes vbc-match-group-LG', $matches->rows[3][14]->class);
        $this->assertEquals('some notes', $matches->rows[3][14]->text);

        // Blank line
        $this->assertCount(14, $matches->rows[4]);
        for ($i = 0; $i < 14; $i++) {
            $this->assertEquals(HTML::MATCH_COLUMN_BLANK, $matches->rows[4][$i]->column_id);
            $this->assertEquals('', $matches->rows[4][$i]->class);
            $this->assertEquals('&nbsp;', $matches->rows[4][$i]->text);
        }

        // Break
        $this->assertCount(6, $matches->rows[5]);
        $this->assertEquals(HTML::MATCH_COLUMN_BREAK, $matches->rows[5][0]->column_id);
        $this->assertEquals('vbc-match-break', $matches->rows[5][0]->class);
        $this->assertEquals('', $matches->rows[5][0]->text);
        $this->assertEquals('4', $matches->rows[5][0]->colspan);
        $this->assertEquals(HTML::MATCH_COLUMN_DATE, $matches->rows[5][1]->column_id);
        $this->assertEquals('vbc-match-date', $matches->rows[5][1]->class);
        $this->assertEquals('2023-06-21', $matches->rows[5][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_BREAK, $matches->rows[5][2]->column_id);
        $this->assertEquals('vbc-match-break', $matches->rows[5][2]->class);
        $this->assertEquals('', $matches->rows[5][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[5][3]->column_id);
        $this->assertEquals('vbc-match-start', $matches->rows[5][3]->class);
        $this->assertEquals('12:20', $matches->rows[5][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[5][4]->column_id);
        $this->assertEquals('vbc-match-duration', $matches->rows[5][4]->class);
        $this->assertEquals('1:00', $matches->rows[5][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_BREAK, $matches->rows[5][5]->column_id);
        $this->assertEquals('vbc-match-break', $matches->rows[5][5]->class);
        $this->assertEquals('Lunch break', $matches->rows[5][5]->text);
        $this->assertEquals('8', $matches->rows[5][5]->colspan);

        // Blank line
        $this->assertCount(14, $matches->rows[6]);
        for ($i = 0; $i < 14; $i++) {
            $this->assertEquals(HTML::MATCH_COLUMN_BLANK, $matches->rows[6][$i]->column_id);
            $this->assertEquals('', $matches->rows[6][$i]->class);
            $this->assertEquals('&nbsp;', $matches->rows[6][$i]->text);
        }

        // Match LG4
        $this->assertCount(15, $matches->rows[7]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[7][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[7][0]->class);
        $this->assertEquals('LG4', $matches->rows[7][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[7][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[7][1]->class);
        $this->assertEquals('1', $matches->rows[7][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_VENUE, $matches->rows[7][2]->column_id);
        $this->assertEquals('vbc-match-venue vbc-match-group-LG', $matches->rows[7][2]->class);
        $this->assertEquals('City Sports Centre', $matches->rows[7][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DATE, $matches->rows[7][3]->column_id);
        $this->assertEquals('vbc-match-date vbc-match-played vbc-match-group-LG', $matches->rows[7][3]->class);
        $this->assertEquals('2023-06-21', $matches->rows[7][3]->text);
        $this->assertEquals('3', $matches->rows[7][3]->rowspan);
        $this->assertEquals(HTML::MATCH_COLUMN_WARMUP, $matches->rows[7][4]->column_id);
        $this->assertEquals('vbc-match-warmup vbc-match-played vbc-match-group-LG', $matches->rows[7][4]->class);
        $this->assertEquals('13:10', $matches->rows[7][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[7][5]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[7][5]->class);
        $this->assertEquals('13:20', $matches->rows[7][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[7][6]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[7][6]->class);
        $this->assertEquals('0:20', $matches->rows[7][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[7][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[7][7]->class);
        $this->assertEquals('Team 1', $matches->rows[7][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[7][8]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[7][8]->class);
        $this->assertEquals('24', $matches->rows[7][8]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[7][9]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[7][9]->class);
        $this->assertEquals('28', $matches->rows[7][9]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[7][10]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[7][10]->class);
        $this->assertEquals('Team 4', $matches->rows[7][10]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[7][11]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[7][11]->class);
        $this->assertEquals('Team 3', $matches->rows[7][11]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MVP, $matches->rows[7][12]->column_id);
        $this->assertEquals('vbc-match-mvp vbc-match-group-LG', $matches->rows[7][12]->class);
        $this->assertEquals('D Dodds', $matches->rows[7][12]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MANAGER, $matches->rows[7][13]->column_id);
        $this->assertEquals('vbc-match-manager vbc-match-group-LG', $matches->rows[7][13]->class);
        $this->assertEquals('Team 3', $matches->rows[7][13]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_NOTES, $matches->rows[7][14]->column_id);
        $this->assertEquals('vbc-match-notes vbc-match-group-LG', $matches->rows[7][14]->class);
        $this->assertEquals('some notes', $matches->rows[7][14]->text);

        // Match LG5
        $this->assertCount(14, $matches->rows[8]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[8][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[8][0]->class);
        $this->assertEquals('LG5', $matches->rows[8][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[8][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[8][1]->class);
        $this->assertEquals('1', $matches->rows[8][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_VENUE, $matches->rows[8][2]->column_id);
        $this->assertEquals('vbc-match-venue vbc-match-group-LG', $matches->rows[8][2]->class);
        $this->assertEquals('City Sports Centre', $matches->rows[8][2]->text);
        $this->assertFalse(isset($matches->rows[8][3]));
        $this->assertEquals(HTML::MATCH_COLUMN_WARMUP, $matches->rows[8][4]->column_id);
        $this->assertEquals('vbc-match-warmup vbc-match-played vbc-match-group-LG', $matches->rows[8][4]->class);
        $this->assertEquals('14:10', $matches->rows[8][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[8][5]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[8][5]->class);
        $this->assertEquals('14:20', $matches->rows[8][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[8][6]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[8][6]->class);
        $this->assertEquals('0:20', $matches->rows[8][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[8][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[8][7]->class);
        $this->assertEquals('Team 3', $matches->rows[8][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[8][8]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[8][8]->class);
        $this->assertEquals('25', $matches->rows[8][8]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[8][9]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[8][9]->class);
        $this->assertEquals('30', $matches->rows[8][9]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[8][10]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[8][10]->class);
        $this->assertEquals('Team 4', $matches->rows[8][10]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[8][11]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[8][11]->class);
        $this->assertEquals('Team 2', $matches->rows[8][11]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MVP, $matches->rows[8][12]->column_id);
        $this->assertEquals('vbc-match-mvp vbc-match-group-LG', $matches->rows[8][12]->class);
        $this->assertEquals('E Edwards', $matches->rows[8][12]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MANAGER, $matches->rows[8][13]->column_id);
        $this->assertEquals('vbc-match-manager vbc-match-group-LG', $matches->rows[8][13]->class);
        $this->assertEquals('Team 2', $matches->rows[8][13]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_NOTES, $matches->rows[8][14]->column_id);
        $this->assertEquals('vbc-match-notes vbc-match-group-LG', $matches->rows[8][14]->class);
        $this->assertEquals('some notes', $matches->rows[8][14]->text);

        // Match LG6
        $this->assertCount(14, $matches->rows[9]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[9][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[9][0]->class);
        $this->assertEquals('LG6', $matches->rows[9][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[9][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[9][1]->class);
        $this->assertEquals('1', $matches->rows[9][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_VENUE, $matches->rows[9][2]->column_id);
        $this->assertEquals('vbc-match-venue vbc-match-group-LG', $matches->rows[9][2]->class);
        $this->assertEquals('City Sports Centre', $matches->rows[9][2]->text);
        $this->assertFalse(isset($matches->rows[9][3]));
        $this->assertEquals(HTML::MATCH_COLUMN_WARMUP, $matches->rows[9][4]->column_id);
        $this->assertEquals('vbc-match-warmup vbc-match-played vbc-match-group-LG', $matches->rows[9][4]->class);
        $this->assertEquals('15:10', $matches->rows[9][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[9][5]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[9][5]->class);
        $this->assertEquals('15:20', $matches->rows[9][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[9][6]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[9][6]->class);
        $this->assertEquals('0:20', $matches->rows[9][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[9][7]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[9][7]->class);
        $this->assertEquals('Team 1', $matches->rows[9][7]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_SCORE, $matches->rows[9][8]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-loser', $matches->rows[9][8]->class);
        $this->assertEquals('26', $matches->rows[9][8]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_SCORE, $matches->rows[9][9]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG vbc-match-winner', $matches->rows[9][9]->class);
        $this->assertEquals('32', $matches->rows[9][9]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[9][10]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[9][10]->class);
        $this->assertEquals('Team 2', $matches->rows[9][10]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[9][11]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[9][11]->class);
        $this->assertEquals('Team 4', $matches->rows[9][11]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MVP, $matches->rows[9][12]->column_id);
        $this->assertEquals('vbc-match-mvp vbc-match-group-LG', $matches->rows[9][12]->class);
        $this->assertEquals('F Franks', $matches->rows[9][12]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_MANAGER, $matches->rows[9][13]->column_id);
        $this->assertEquals('vbc-match-manager vbc-match-group-LG', $matches->rows[9][13]->class);
        $this->assertEquals('Joe Bloggs', $matches->rows[9][13]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_NOTES, $matches->rows[9][14]->column_id);
        $this->assertEquals('vbc-match-notes vbc-match-group-LG', $matches->rows[9][14]->class);
        $this->assertEquals('some notes', $matches->rows[9][14]->text);
    }

    public function testHTMLMatchesForHTMLSetsPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $matches = HTML::getMatchesForHTML($competition->getStageById('LS')->getGroupById('LG'));

        $this->assertEquals('vbc-match vbc-match-group-LG', $matches->class);

        $this->assertCount(8, $matches->headings);

        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->headings[0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->headings[0]->class);
        $this->assertEquals('MatchNo', $matches->headings[0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->headings[1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->headings[1]->class);
        $this->assertEquals('Court', $matches->headings[1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->headings[2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-group-LG', $matches->headings[2]->class);
        $this->assertEquals('Start', $matches->headings[2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->headings[3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->headings[3]->class);
        $this->assertEquals('Duration', $matches->headings[3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->headings[4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->headings[4]->class);
        $this->assertEquals('Home Team', $matches->headings[4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SCORE, $matches->headings[5]->column_id);
        $this->assertEquals('vbc-match-score vbc-match-group-LG', $matches->headings[5]->class);
        $this->assertEquals('Score', $matches->headings[5]->text);
        $this->assertEquals(2, $matches->headings[5]->colspan);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->headings[6]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->headings[6]->class);
        $this->assertEquals('Away Team', $matches->headings[6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->headings[7]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->headings[7]->class);
        $this->assertEquals('Officials', $matches->headings[7]->text);

        $this->assertCount(7, $matches->rows);

        $this->assertCount(8, $matches->rows[0]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[0][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[0][0]->class);
        $this->assertEquals('LG1', $matches->rows[0][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[0][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[0][1]->class);
        $this->assertEquals('1', $matches->rows[0][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[0][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[0][2]->class);
        $this->assertEquals('09:20', $matches->rows[0][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[0][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[0][3]->class);
        $this->assertEquals('0:20', $matches->rows[0][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[0][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[0][4]->class);
        $this->assertEquals('Team 2', $matches->rows[0][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SETS_SCORE, $matches->rows[0][5]->column_id);
        $this->assertEquals('vbc-match-score-sets vbc-match-group-LG', $matches->rows[0][5]->class);
        $this->assertEquals('<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG  vbc-match-loser">0</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">2</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">21&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span>   21&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span></table>', $matches->rows[0][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[0][6]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[0][6]->class);
        $this->assertEquals('Team 4', $matches->rows[0][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[0][7]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[0][7]->class);
        $this->assertEquals('Team 1', $matches->rows[0][7]->text);

        $this->assertCount(8, $matches->rows[1]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[1][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[1][0]->class);
        $this->assertEquals('LG2', $matches->rows[1][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[1][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[1][1]->class);
        $this->assertEquals('1', $matches->rows[1][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[1][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[1][2]->class);
        $this->assertEquals('10:20', $matches->rows[1][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[1][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[1][3]->class);
        $this->assertEquals('0:20', $matches->rows[1][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[1][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[1][4]->class);
        $this->assertEquals('Team 1', $matches->rows[1][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SETS_SCORE, $matches->rows[1][5]->column_id);
        $this->assertEquals('vbc-match-score-sets vbc-match-group-LG', $matches->rows[1][5]->class);
        $this->assertEquals('<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG  vbc-match-loser">0</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">2</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">22&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span>   22&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span></table>', $matches->rows[1][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[1][6]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[1][6]->class);
        $this->assertEquals('Team 3', $matches->rows[1][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[1][7]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[1][7]->class);
        $this->assertEquals('Team 2', $matches->rows[1][7]->text);

        $this->assertCount(8, $matches->rows[2]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[2][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[2][0]->class);
        $this->assertEquals('LG3', $matches->rows[2][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[2][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[2][1]->class);
        $this->assertEquals('1', $matches->rows[2][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[2][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[2][2]->class);
        $this->assertEquals('11:20', $matches->rows[2][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[2][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[2][3]->class);
        $this->assertEquals('0:20', $matches->rows[2][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[2][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[2][4]->class);
        $this->assertEquals('Team 2', $matches->rows[2][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SETS_SCORE, $matches->rows[2][5]->column_id);
        $this->assertEquals('vbc-match-score-sets vbc-match-group-LG', $matches->rows[2][5]->class);
        $this->assertEquals('<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG  vbc-match-loser">0</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">2</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">23&#8209;<span class="vbc-match-winner vbc-match-group-LG">26</span>   23&#8209;<span class="vbc-match-winner vbc-match-group-LG">26</span></table>', $matches->rows[2][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[2][6]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[2][6]->class);
        $this->assertEquals('Team 3', $matches->rows[2][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[2][7]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[2][7]->class);
        $this->assertEquals('Team 4', $matches->rows[2][7]->text);

        $this->assertCount(4, $matches->rows[3]);
        $this->assertEquals(HTML::MATCH_COLUMN_BREAK, $matches->rows[3][0]->column_id);
        $this->assertEquals('vbc-match-break', $matches->rows[3][0]->class);
        $this->assertEquals('', $matches->rows[3][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[3][1]->column_id);
        $this->assertEquals('vbc-match-start', $matches->rows[3][1]->class);
        $this->assertEquals('12:20', $matches->rows[3][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[3][2]->column_id);
        $this->assertEquals('vbc-match-duration', $matches->rows[3][2]->class);
        $this->assertEquals('1:00', $matches->rows[3][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_BREAK, $matches->rows[3][3]->column_id);
        $this->assertEquals('vbc-match-break', $matches->rows[3][3]->class);
        $this->assertEquals('Lunch break', $matches->rows[3][3]->text);

        $this->assertCount(8, $matches->rows[4]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[4][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[4][0]->class);
        $this->assertEquals('LG4', $matches->rows[4][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[4][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[4][1]->class);
        $this->assertEquals('1', $matches->rows[4][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[4][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[4][2]->class);
        $this->assertEquals('13:20', $matches->rows[4][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[4][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[4][3]->class);
        $this->assertEquals('0:20', $matches->rows[4][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[4][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[4][4]->class);
        $this->assertEquals('Team 1', $matches->rows[4][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SETS_SCORE, $matches->rows[4][5]->column_id);
        $this->assertEquals('vbc-match-score-sets vbc-match-group-LG', $matches->rows[4][5]->class);
        $this->assertEquals('<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG  vbc-match-loser">0</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">2</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">24&#8209;<span class="vbc-match-winner vbc-match-group-LG">26</span>   22&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span></table>', $matches->rows[4][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[4][6]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[4][6]->class);
        $this->assertEquals('Team 4', $matches->rows[4][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[4][7]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[4][7]->class);
        $this->assertEquals('Team 3', $matches->rows[4][7]->text);

        $this->assertCount(8, $matches->rows[5]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[5][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[5][0]->class);
        $this->assertEquals('LG5', $matches->rows[5][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[5][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[5][1]->class);
        $this->assertEquals('1', $matches->rows[5][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[5][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[5][2]->class);
        $this->assertEquals('14:20', $matches->rows[5][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[5][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[5][3]->class);
        $this->assertEquals('0:20', $matches->rows[5][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[5][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[5][4]->class);
        $this->assertEquals('Team 3', $matches->rows[5][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SETS_SCORE, $matches->rows[5][5]->column_id);
        $this->assertEquals('vbc-match-score-sets vbc-match-group-LG', $matches->rows[5][5]->class);
        $this->assertEquals('<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG  vbc-match-loser">0</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">2</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">15&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span>   15&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span></table>', $matches->rows[5][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[5][6]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[5][6]->class);
        $this->assertEquals('Team 4', $matches->rows[5][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[5][7]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[5][7]->class);
        $this->assertEquals('Team 2', $matches->rows[5][7]->text);

        $this->assertCount(8, $matches->rows[6]);
        $this->assertEquals(HTML::MATCH_COLUMN_ID, $matches->rows[6][0]->column_id);
        $this->assertEquals('vbc-match-id vbc-match-group-LG', $matches->rows[6][0]->class);
        $this->assertEquals('LG6', $matches->rows[6][0]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_COURT, $matches->rows[6][1]->column_id);
        $this->assertEquals('vbc-match-court vbc-match-group-LG', $matches->rows[6][1]->class);
        $this->assertEquals('1', $matches->rows[6][1]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_START, $matches->rows[6][2]->column_id);
        $this->assertEquals('vbc-match-start vbc-match-played vbc-match-group-LG', $matches->rows[6][2]->class);
        $this->assertEquals('15:20', $matches->rows[6][2]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_DURATION, $matches->rows[6][3]->column_id);
        $this->assertEquals('vbc-match-duration vbc-match-group-LG', $matches->rows[6][3]->class);
        $this->assertEquals('0:20', $matches->rows[6][3]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_HOME_TEAM, $matches->rows[6][4]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[6][4]->class);
        $this->assertEquals('Team 1', $matches->rows[6][4]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_SETS_SCORE, $matches->rows[6][5]->column_id);
        $this->assertEquals('vbc-match-score-sets vbc-match-group-LG', $matches->rows[6][5]->class);
        $this->assertEquals('<table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG  vbc-match-loser">0</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">2</td><td></td></tr><tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">13&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span>   20&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span></table>', $matches->rows[6][5]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_AWAY_TEAM, $matches->rows[6][6]->column_id);
        $this->assertEquals('vbc-match-team vbc-match-group-LG', $matches->rows[6][6]->class);
        $this->assertEquals('Team 2', $matches->rows[6][6]->text);
        $this->assertEquals(HTML::MATCH_COLUMN_OFFICIALS, $matches->rows[6][7]->column_id);
        $this->assertEquals('vbc-match-officials vbc-match-group-LG', $matches->rows[6][7]->class);
        $this->assertEquals('Team 4', $matches->rows[6][7]->text);
    }

    public function testHTMLMatchesHTMLPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'));

        $expected_table = '<table class="vbc-match vbc-match-group-LG">';
        $expected_table .= '<tr><th class="vbc-match-id vbc-match-group-LG">MatchNo</th><th class="vbc-match-court vbc-match-group-LG">Court</th><th class="vbc-match-start vbc-match-group-LG">Start</th><th class="vbc-match-duration vbc-match-group-LG">Duration</th><th class="vbc-match-team vbc-match-group-LG">Home Team</th><th class="vbc-match-score vbc-match-group-LG" colspan="2">Score</th><th class="vbc-match-team vbc-match-group-LG">Away Team</th><th class="vbc-match-officials vbc-match-group-LG">Officials</th></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">09:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">21</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">22</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 1</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">10:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">22</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">24</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG3</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">11:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">23</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">26</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-break" colspan="3"></td><td class="vbc-match-start">12:20</td><td class="vbc-match-duration">1:00</td><td class="vbc-match-break" colspan="5">Lunch break</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">13:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">24</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">28</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG"></td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG5</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">14:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">25</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">30</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">15:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">26</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">32</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_table .= '</table>';

        $this->assertEquals(
            $expected_table,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLNoMatches() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'empty.json');
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'));

        $expected_table = '<table class="vbc-match vbc-match-group-LG">';
        $expected_table .= '<tr><th class="vbc-match-id vbc-match-group-LG">MatchNo</th><th class="vbc-match-team vbc-match-group-LG">Home Team</th><th class="vbc-match-score vbc-match-group-LG" colspan="2">Score</th><th class="vbc-match-team vbc-match-group-LG">Away Team</th></tr>';
        $expected_table .= '</table>';

        $this->assertEquals(
            $expected_table,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLNoMerge() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $matches_config = new stdClass();
        $matches_config->merge = [];
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'), $matches_config);

        $expected_table = '<table class="vbc-match vbc-match-group-LG">';
        $expected_table .= '<tr><th class="vbc-match-id vbc-match-group-LG">MatchNo</th><th class="vbc-match-court vbc-match-group-LG">Court</th><th class="vbc-match-start vbc-match-group-LG">Start</th><th class="vbc-match-duration vbc-match-group-LG">Duration</th><th class="vbc-match-team vbc-match-group-LG">Home Team</th><th class="vbc-match-score vbc-match-group-LG" colspan="2">Score</th><th class="vbc-match-team vbc-match-group-LG">Away Team</th><th class="vbc-match-officials vbc-match-group-LG">Officials</th></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">09:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">21</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">22</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 1</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">10:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">22</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">24</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG3</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">11:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">23</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">26</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-break" colspan="3"></td><td class="vbc-match-start">12:20</td><td class="vbc-match-duration">1:00</td><td class="vbc-match-break" colspan="5">Lunch break</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">13:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">24</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">28</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG"></td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG5</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">14:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">25</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">30</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">15:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">26</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">32</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_table .= '</table>';

        $this->assertEquals(
            $expected_table,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLEverythingMerging() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league-everything.json');
        $matches_config = new stdClass();
        $matches_config->merge = [HTML::MATCH_COLUMN_NOTES];
        $matches_config->headings = [HTML::MATCH_COLUMN_ID, HTML::MATCH_COLUMN_HOME_TEAM, HTML::MATCH_COLUMN_AWAY_TEAM, HTML::MATCH_COLUMN_NOTES, HTML::MATCH_COLUMN_OFFICIALS];
        $matches_config->headingMap = [
            HTML::MATCH_COLUMN_ID => 'Match',
            HTML::MATCH_COLUMN_HOME_TEAM => 'Team Name',
            HTML::MATCH_COLUMN_AWAY_TEAM => 'League Points'
        ];
        $matches_config->includeTeamMVPs = true;
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'), $matches_config, 'TM1');

        $expected_matches = '<table class="vbc-match vbc-match-group-LG">';
        $expected_matches .= '<tr><th class="vbc-match-id vbc-match-group-LG">Match</th><th class="vbc-match-team vbc-match-group-LG">Team Name</th><th class="vbc-match-team vbc-match-group-LG">League Points</th><th class="vbc-match-notes vbc-match-group-LG">Notes</th><th class="vbc-match-officials vbc-match-group-LG">Officials</th></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-team vbc-match-group-LG">Team 2<br><span class="vbc-match-team-mvp">MVP: A Adams</span></td><td class="vbc-match-team vbc-match-group-LG">Team 4<br><span class="vbc-match-team-mvp">MVP: D Dodds</span></td><td class="vbc-match-notes vbc-match-group-LG" rowspan="3">some notes</td><td class="vbc-match-officials vbc-match-group-LG vbc-this-team">Team 1</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG3</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-break" colspan="6">Lunch break</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-notes vbc-match-group-LG" rowspan="3">some notes</td><td class="vbc-match-officials vbc-match-group-LG">Team 3</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG5</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_matches .= '</table>';

        $this->assertEquals(
            $expected_matches,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLEverything() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league-everything.json');
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'), null, 'TM1');
        $expected_table = '<table class="vbc-match vbc-match-group-LG">';
        $expected_table .= '<tr><th class="vbc-match-id vbc-match-group-LG">MatchNo</th><th class="vbc-match-court vbc-match-group-LG">Court</th><th class="vbc-match-venue vbc-match-group-LG">Venue</th><th class="vbc-match-date vbc-match-group-LG">Date</th><th class="vbc-match-warmup vbc-match-group-LG">Warmup</th><th class="vbc-match-start vbc-match-group-LG">Start</th><th class="vbc-match-duration vbc-match-group-LG">Duration</th><th class="vbc-match-team vbc-match-group-LG">Home Team</th><th class="vbc-match-score vbc-match-group-LG" colspan="2">Score</th><th class="vbc-match-team vbc-match-group-LG">Away Team</th><th class="vbc-match-officials vbc-match-group-LG">Officials</th><th class="vbc-match-mvp vbc-match-group-LG">MVP</th><th class="vbc-match-manager vbc-match-group-LG">Manager</th><th class="vbc-match-notes vbc-match-group-LG">Notes</th></tr>';
        $expected_table .= '<tr><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-venue vbc-match-group-LG">City Sports Centre</td><td class="vbc-match-date vbc-match-played vbc-match-group-LG" rowspan="3">2023-06-21</td><td class="vbc-match-warmup vbc-match-played vbc-match-group-LG">09:10</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">09:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">21</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">22</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-mvp vbc-match-group-LG">A Adams</td><td class="vbc-match-manager vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-notes vbc-match-group-LG">some notes</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-venue vbc-match-group-LG">City Sports Centre</td><td class="vbc-match-warmup vbc-match-played vbc-match-group-LG">10:10</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">10:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">22</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">24</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td><td class="vbc-match-mvp vbc-match-group-LG">B Betts</td><td class="vbc-match-manager vbc-match-group-LG">Team 2</td><td class="vbc-match-notes vbc-match-group-LG">some notes</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG3</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-venue vbc-match-group-LG">City Sports Centre</td><td class="vbc-match-warmup vbc-match-played vbc-match-group-LG">11:10</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">11:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">23</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">26</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td><td class="vbc-match-mvp vbc-match-group-LG">C Crosier</td><td class="vbc-match-manager vbc-match-group-LG">Team 4</td><td class="vbc-match-notes vbc-match-group-LG">some notes</td></tr>';
        $expected_table .= '<tr><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-break" colspan="4"></td><td class="vbc-match-date">2023-06-21</td><td class="vbc-match-break" colspan="2"></td><td class="vbc-match-start">12:20</td><td class="vbc-match-duration">1:00</td><td class="vbc-match-break" colspan="8">Lunch break</td></tr>';
        $expected_table .= '<tr><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td><td class="">&nbsp;</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-venue vbc-match-group-LG">City Sports Centre</td><td class="vbc-match-date vbc-match-played vbc-match-group-LG" rowspan="3">2023-06-21</td><td class="vbc-match-warmup vbc-match-played vbc-match-group-LG">13:10</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">13:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">24</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">28</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 3</td><td class="vbc-match-mvp vbc-match-group-LG">D Dodds</td><td class="vbc-match-manager vbc-match-group-LG">Team 3</td><td class="vbc-match-notes vbc-match-group-LG">some notes</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG5</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-venue vbc-match-group-LG">City Sports Centre</td><td class="vbc-match-warmup vbc-match-played vbc-match-group-LG">14:10</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">14:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">25</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">30</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td><td class="vbc-match-mvp vbc-match-group-LG">E Edwards</td><td class="vbc-match-manager vbc-match-group-LG">Team 2</td><td class="vbc-match-notes vbc-match-group-LG">some notes</td></tr>';
        $expected_table .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-venue vbc-match-group-LG">City Sports Centre</td><td class="vbc-match-warmup vbc-match-played vbc-match-group-LG">15:10</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">15:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">26</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">32</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td><td class="vbc-match-mvp vbc-match-group-LG">F Franks</td><td class="vbc-match-manager vbc-match-group-LG">Joe Bloggs</td><td class="vbc-match-notes vbc-match-group-LG">some notes</td></tr>';
        $expected_table .= '</table>';

        $this->assertEquals(
            $expected_table,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLEverythingControlledColumns() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league-everything.json');
        $matches_config = new stdClass();
        $matches_config->headings = [HTML::MATCH_COLUMN_ID, HTML::MATCH_COLUMN_HOME_TEAM, HTML::MATCH_COLUMN_AWAY_TEAM];
        $matches_config->headingMap = [
            HTML::MATCH_COLUMN_ID => 'Match',
            HTML::MATCH_COLUMN_HOME_TEAM => 'Team Name',
            HTML::MATCH_COLUMN_AWAY_TEAM => 'League Points'
        ];
        $matches_config->includeTeamMVPs = true;
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'), $matches_config, 'TM1');

        $expected_matches = '<table class="vbc-match vbc-match-group-LG">';
        $expected_matches .= '<tr><th class="vbc-match-id vbc-match-group-LG">Match</th><th class="vbc-match-team vbc-match-group-LG">Team Name</th><th class="vbc-match-team vbc-match-group-LG">League Points</th></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-team vbc-match-group-LG">Team 2<br><span class="vbc-match-team-mvp">MVP: A Adams</span></td><td class="vbc-match-team vbc-match-group-LG">Team 4<br><span class="vbc-match-team-mvp">MVP: D Dodds</span></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG3</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-break" colspan="4">Lunch break</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG5</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td></tr>';
        $expected_matches .= '</table>';

        $this->assertEquals(
            $expected_matches,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLEverythingControlledColumnsJustThisTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league-everything.json');
        $matches_config = new stdClass();
        $matches_config->headings = [HTML::MATCH_COLUMN_ID, HTML::MATCH_COLUMN_HOME_TEAM, HTML::MATCH_COLUMN_AWAY_TEAM, HTML::MATCH_COLUMN_OFFICIALS];
        $matches_config->headingMap = [
            HTML::MATCH_COLUMN_ID => 'Match',
            HTML::MATCH_COLUMN_HOME_TEAM => 'Team Name',
            HTML::MATCH_COLUMN_AWAY_TEAM => 'League Points',
            HTML::MATCH_COLUMN_OFFICIALS => 'Referees'
        ];
        $matches_config->includeTeamMVPs = true;
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'), $matches_config, 'TM1', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);

        $expected_matches = '<table class="vbc-match vbc-match-group-LG">';
        $expected_matches .= '<tr><th class="vbc-match-id vbc-match-group-LG">Match</th><th class="vbc-match-team vbc-match-group-LG">Team Name</th><th class="vbc-match-team vbc-match-group-LG">League Points</th><th class="vbc-match-officials vbc-match-group-LG">Referees</th></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-team vbc-match-group-LG">Team 2<br><span class="vbc-match-team-mvp">MVP: A Adams</span></td><td class="vbc-match-team vbc-match-group-LG">Team 4<br><span class="vbc-match-team-mvp">MVP: D Dodds</span></td><td class="vbc-match-officials vbc-match-group-LG vbc-this-team">Team 1</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 3</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">Team 1</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_matches .= '</table>';

        $this->assertEquals(
            $expected_matches,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLOtherConfig() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league-everything.json');
        $matches_config = new stdClass();
        $matches_config->headings = [HTML::MATCH_COLUMN_ID, HTML::MATCH_COLUMN_BLANK, HTML::MATCH_COLUMN_HOME_TEAM, HTML::MATCH_COLUMN_AWAY_TEAM, HTML::MATCH_COLUMN_OFFICIALS, HTML::MATCH_COLUMN_MANAGER];
        $matches_config->headingMap = [
            HTML::MATCH_COLUMN_ID => 'Match',
            HTML::MATCH_COLUMN_HOME_TEAM => 'Team Name',
            HTML::MATCH_COLUMN_AWAY_TEAM => 'League Points',
            HTML::MATCH_COLUMN_OFFICIALS => 'Referees',
            HTML::MATCH_COLUMN_MANAGER => 'Managers',
        ];
        $matches_config->lookupTeamIDs = false;
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'), $matches_config, 'TM1', VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);

        $expected_matches = '<table class="vbc-match vbc-match-group-LG">';
        $expected_matches .= '<tr><th class="vbc-match-id vbc-match-group-LG">Match</th><th class="vbc-match-blank vbc-match-group-LG"></th><th class="vbc-match-team vbc-match-group-LG">Team Name</th><th class="vbc-match-team vbc-match-group-LG">League Points</th><th class="vbc-match-officials vbc-match-group-LG">Referees</th><th class="vbc-match-manager vbc-match-group-LG">Managers</th></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-blank vbc-match-group-LG"></td><td class="vbc-match-team vbc-match-group-LG">TM2</td><td class="vbc-match-team vbc-match-group-LG">TM4</td><td class="vbc-match-officials vbc-match-group-LG vbc-this-team">TM1</td><td class="vbc-match-manager vbc-match-group-LG vbc-this-team">TM1</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-blank vbc-match-group-LG"></td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">TM1</td><td class="vbc-match-team vbc-match-group-LG">TM3</td><td class="vbc-match-officials vbc-match-group-LG">TM2</td><td class="vbc-match-manager vbc-match-group-LG">TM2</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-blank vbc-match-group-LG"></td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">TM1</td><td class="vbc-match-team vbc-match-group-LG">TM4</td><td class="vbc-match-officials vbc-match-group-LG">TM3</td><td class="vbc-match-manager vbc-match-group-LG">TM3</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-blank vbc-match-group-LG"></td><td class="vbc-match-team vbc-match-group-LG vbc-this-team">TM1</td><td class="vbc-match-team vbc-match-group-LG">TM2</td><td class="vbc-match-officials vbc-match-group-LG">TM4</td><td class="vbc-match-manager vbc-match-group-LG">Joe Bloggs</td></tr>';
        $expected_matches .= '</table>';

        $this->assertEquals(
            $expected_matches,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLBreak() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'break.json');
        $matches_config = new stdClass();
        $matches_config->headings = [HTML::MATCH_COLUMN_ID, HTML::MATCH_COLUMN_DURATION, HTML::MATCH_COLUMN_HOME_TEAM, HTML::MATCH_COLUMN_AWAY_TEAM];
        $matches_config->headingMap = [
            HTML::MATCH_COLUMN_ID => 'Match',
            HTML::MATCH_COLUMN_DURATION => 'Duration',
            HTML::MATCH_COLUMN_HOME_TEAM => 'Team Name',
            HTML::MATCH_COLUMN_AWAY_TEAM => 'League Points',
        ];
        $matches_config->lookupTeamIDs = false;
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'), $matches_config);

        $expected_matches = '<table class="vbc-match vbc-match-group-LG">';
        $expected_matches .= '<tr><th class="vbc-match-id vbc-match-group-LG">Match</th><th class="vbc-match-team vbc-match-group-LG">Team Name</th><th class="vbc-match-team vbc-match-group-LG">League Points</th></tr>';
        $expected_matches .= '<tr><td class="vbc-match-break" colspan="2"></td><td class="vbc-match-duration">1:00</td><td class="vbc-match-break" colspan="3">Lunch break</td></tr>';
        $expected_matches .= '</table>';

        $this->assertEquals(
            $expected_matches,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLDrawsAllowed() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'incomplete-league-draws.json');
        $matches_html = HTML::getMatchesHTML($competition->getStageById('L')->getGroupById('LG'));

        $expected_matches = '<table class="vbc-match vbc-match-group-LG">';
        $expected_matches .= '<tr><th class="vbc-match-id vbc-match-group-LG">MatchNo</th><th class="vbc-match-court vbc-match-group-LG">Court</th><th class="vbc-match-start vbc-match-group-LG">Start</th><th class="vbc-match-duration vbc-match-group-LG">Duration</th><th class="vbc-match-team vbc-match-group-LG">Home Team</th><th class="vbc-match-score vbc-match-group-LG" colspan="2">Score</th><th class="vbc-match-team vbc-match-group-LG">Away Team</th><th class="vbc-match-officials vbc-match-group-LG">Officials</th></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">09:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">21</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">22</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 1</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">10:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG">22</td><td class="vbc-match-score vbc-match-group-LG">22</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG3</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">11:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">23</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">26</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-break" colspan="3"></td><td class="vbc-match-start">12:20</td><td class="vbc-match-duration">1:00</td><td class="vbc-match-break" colspan="5">Lunch break</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-group-LG">13:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG">0</td><td class="vbc-match-score vbc-match-group-LG">0</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 3</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG5</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-group-LG">14:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-score vbc-match-group-LG">0</td><td class="vbc-match-score vbc-match-group-LG">0</td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-group-LG">15:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score vbc-match-group-LG">0</td><td class="vbc-match-score vbc-match-group-LG">0</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td></tr>';
        $expected_matches .= '</table>';

        $this->assertEquals(
            $expected_matches,
            $matches_html
        );
    }

    public function testHTMLMatchesHTMLSetsDrawsAllowed() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'incomplete-league-draws.json');
        $matches_html = HTML::getMatchesHTML($competition->getStageById('LS')->getGroupById('LG'));

        $expected_matches = '<table class="vbc-match vbc-match-group-LG">';
        $expected_matches .= '<tr><th class="vbc-match-id vbc-match-group-LG">MatchNo</th><th class="vbc-match-court vbc-match-group-LG">Court</th><th class="vbc-match-start vbc-match-group-LG">Start</th><th class="vbc-match-duration vbc-match-group-LG">Duration</th><th class="vbc-match-team vbc-match-group-LG">Home Team</th><th class="vbc-match-score vbc-match-group-LG" colspan="2">Score</th><th class="vbc-match-team vbc-match-group-LG">Away Team</th><th class="vbc-match-officials vbc-match-group-LG">Officials</th><th class="vbc-match-manager vbc-match-group-LG">Manager</th></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG1</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">09:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score-sets vbc-match-group-LG" colspan="2"><table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG  vbc-match-loser">0</td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">2</td><td></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">21&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span>   21&#8209;<span class="vbc-match-winner vbc-match-group-LG">25</span></table></td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 1</td><td class="vbc-match-manager vbc-match-group-LG"></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG2</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">10:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score-sets vbc-match-group-LG" colspan="2"><table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG vbc-match-winner">1</td><td class="vbc-match-score vbc-match-group-LG  vbc-match-loser">0</td><td></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4"><span class="vbc-match-winner vbc-match-group-LG">25</span>&#8209;22   10&#8209;10</table></td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 2</td><td class="vbc-match-manager vbc-match-group-LG"></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG3</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-played vbc-match-group-LG">11:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-score-sets vbc-match-group-LG" colspan="2"><table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">1</td><td class="vbc-match-score vbc-match-group-LG vbc-match-loser">1</td><td></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">23&#8209;<span class="vbc-match-winner vbc-match-group-LG">26</span>   <span class="vbc-match-winner vbc-match-group-LG">26</span>&#8209;23   8&#8209;8</table></td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-officials vbc-match-group-LG">Team 4</td><td class="vbc-match-manager vbc-match-group-LG"></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-break" colspan="3"></td><td class="vbc-match-start">12:20</td><td class="vbc-match-duration">1:00</td><td class="vbc-match-break" colspan="6">Lunch break</td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG4</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-group-LG">13:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score-sets vbc-match-group-LG" colspan="2"><table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-loser vbc-match-group-LG">1</td><td class="vbc-match-score vbc-match-loser">1</td><td></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">24&#8209;<span class="vbc-match-winner vbc-match-group-LG">26</span>   <span class="vbc-match-winner vbc-match-group-LG">25</span>&#8209;22   5&#8209;5</table></td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">Team 3</td><td class="vbc-match-manager vbc-match-group-LG"></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG5</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-group-LG">14:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 3</td><td class="vbc-match-score-sets vbc-match-group-LG" colspan="2"><table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-loser vbc-match-group-LG">0</td><td class="vbc-match-score vbc-match-loser">0</td><td></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">0&#8209;0</table></td><td class="vbc-match-team vbc-match-group-LG">Team 4</td><td class="vbc-match-officials vbc-match-group-LG">First: A Alison, Scorer: C Cones</td><td class="vbc-match-manager vbc-match-group-LG"></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-id vbc-match-group-LG">LG6</td><td class="vbc-match-court vbc-match-group-LG">1</td><td class="vbc-match-start vbc-match-group-LG">15:20</td><td class="vbc-match-duration vbc-match-group-LG">0:20</td><td class="vbc-match-team vbc-match-group-LG">Team 1</td><td class="vbc-match-score-sets vbc-match-group-LG" colspan="2"><table class="vbc-score"><tr><td></td><td class="vbc-match-score vbc-match-loser vbc-match-group-LG">0</td><td class="vbc-match-score vbc-match-loser">0</td><td></td></tr>';
        $expected_matches .= '<tr><td class="vbc-match-score-sets vbc-match-group-LG" colspan="4">0&#8209;0</table></td><td class="vbc-match-team vbc-match-group-LG">Team 2</td><td class="vbc-match-officials vbc-match-group-LG">First: A Alison, Second: B Bigs, Scorer: C Cones</td><td class="vbc-match-manager vbc-match-group-LG">Team 3</td></tr>';
        $expected_matches .= '</table>';

        $this->assertEquals(
            $expected_matches,
            $matches_html
        );
    }

    public function testHTMLLeagueForHTMLPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $league_table = HTML::getLeagueTableForHTML($competition->getStageById('L')->getGroupById('LG'));

        $this->assertEquals('vbc-league-table vbc-league-table-group-LG', $league_table->class);

        $this->assertCount(9, $league_table->headings);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->headings[0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-group-LG', $league_table->headings[0]->class);
        $this->assertEquals('Pos', $league_table->headings[0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->headings[1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->headings[1]->class);
        $this->assertEquals('Team', $league_table->headings[1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->headings[2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-group-LG', $league_table->headings[2]->class);
        $this->assertEquals('P', $league_table->headings[2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->headings[3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-group-LG', $league_table->headings[3]->class);
        $this->assertEquals('W', $league_table->headings[3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->headings[4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-group-LG', $league_table->headings[4]->class);
        $this->assertEquals('L', $league_table->headings[4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->headings[5]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-group-LG', $league_table->headings[5]->class);
        $this->assertEquals('PF', $league_table->headings[5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->headings[6]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-group-LG', $league_table->headings[6]->class);
        $this->assertEquals('PA', $league_table->headings[6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->headings[7]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-group-LG', $league_table->headings[7]->class);
        $this->assertEquals('PD', $league_table->headings[7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->headings[8]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-group-LG', $league_table->headings[8]->class);
        $this->assertEquals('PTS', $league_table->headings[8]->text);

        $this->assertCount(4, $league_table->rows);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[0][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][0]->class);
        $this->assertEquals('1.', $league_table->rows[0][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[0][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[0][1]->class);
        $this->assertEquals('Team 4', $league_table->rows[0][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->rows[0][2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][2]->class);
        $this->assertEquals('3', $league_table->rows[0][2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->rows[0][3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][3]->class);
        $this->assertEquals('3', $league_table->rows[0][3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->rows[0][4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][4]->class);
        $this->assertEquals('0', $league_table->rows[0][4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->rows[0][5]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][5]->class);
        $this->assertEquals('80', $league_table->rows[0][5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->rows[0][6]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][6]->class);
        $this->assertEquals('70', $league_table->rows[0][6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->rows[0][7]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][7]->class);
        $this->assertEquals('10', $league_table->rows[0][7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[0][8]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][8]->class);
        $this->assertEquals('9', $league_table->rows[0][8]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[1][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][0]->class);
        $this->assertEquals('2.', $league_table->rows[1][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[1][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[1][1]->class);
        $this->assertEquals('Team 3', $league_table->rows[1][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->rows[1][2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][2]->class);
        $this->assertEquals('3', $league_table->rows[1][2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->rows[1][3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][3]->class);
        $this->assertEquals('2', $league_table->rows[1][3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->rows[1][4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][4]->class);
        $this->assertEquals('1', $league_table->rows[1][4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->rows[1][5]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][5]->class);
        $this->assertEquals('75', $league_table->rows[1][5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->rows[1][6]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][6]->class);
        $this->assertEquals('75', $league_table->rows[1][6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->rows[1][7]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][7]->class);
        $this->assertEquals('0', $league_table->rows[1][7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[1][8]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][8]->class);
        $this->assertEquals('6', $league_table->rows[1][8]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[2][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][0]->class);
        $this->assertEquals('3.', $league_table->rows[2][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[2][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[2][1]->class);
        $this->assertEquals('Team 2', $league_table->rows[2][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->rows[2][2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][2]->class);
        $this->assertEquals('3', $league_table->rows[2][2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->rows[2][3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][3]->class);
        $this->assertEquals('1', $league_table->rows[2][3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->rows[2][4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][4]->class);
        $this->assertEquals('2', $league_table->rows[2][4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->rows[2][5]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][5]->class);
        $this->assertEquals('76', $league_table->rows[2][5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->rows[2][6]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][6]->class);
        $this->assertEquals('74', $league_table->rows[2][6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->rows[2][7]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][7]->class);
        $this->assertEquals('2', $league_table->rows[2][7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[2][8]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][8]->class);
        $this->assertEquals('3', $league_table->rows[2][8]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[3][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][0]->class);
        $this->assertEquals('4.', $league_table->rows[3][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[3][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[3][1]->class);
        $this->assertEquals('Team 1', $league_table->rows[3][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->rows[3][2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][2]->class);
        $this->assertEquals('3', $league_table->rows[3][2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->rows[3][3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][3]->class);
        $this->assertEquals('0', $league_table->rows[3][3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->rows[3][4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][4]->class);
        $this->assertEquals('3', $league_table->rows[3][4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->rows[3][5]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][5]->class);
        $this->assertEquals('72', $league_table->rows[3][5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->rows[3][6]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][6]->class);
        $this->assertEquals('84', $league_table->rows[3][6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->rows[3][7]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][7]->class);
        $this->assertEquals('-12', $league_table->rows[3][7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[3][8]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][8]->class);
        $this->assertEquals('0', $league_table->rows[3][8]->text);
    }

    public function testHTMLLeagueForHTMLThisTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $league_table = HTML::getLeagueTableForHTML($competition->getStageById('L')->getGroupById('LG'), null, 'TM4');

        $this->assertEquals('vbc-league-table vbc-league-table-group-LG', $league_table->class);

        $this->assertCount(4, $league_table->rows);

        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG vbc-this-team', $league_table->rows[0][1]->class);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[1][1]->class);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[2][1]->class);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[3][1]->class);
    }

    public function testHTMLLeagueForHTMLConfigControlledColumns() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $league_table_config = new stdClass();
        $league_table_config->headings = [HTML::LEAGUE_COLUMN_POSITION, HTML::LEAGUE_COLUMN_TEAM, HTML::LEAGUE_COLUMN_LEAGUE_POINTS];
        $league_table_config->headingMap = [
            HTML::LEAGUE_COLUMN_POSITION => 'Position',
            HTML::LEAGUE_COLUMN_TEAM => 'Team Name',
            HTML::LEAGUE_COLUMN_LEAGUE_POINTS => 'League Points'
        ];
        $league_table = HTML::getLeagueTableForHTML($competition->getStageById('L')->getGroupById('LG'), $league_table_config);

        $this->assertEquals('vbc-league-table vbc-league-table-group-LG', $league_table->class);

        $this->assertCount(3, $league_table->headings);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->headings[0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-group-LG', $league_table->headings[0]->class);
        $this->assertEquals('Position', $league_table->headings[0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->headings[1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->headings[1]->class);
        $this->assertEquals('Team Name', $league_table->headings[1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->headings[2]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-group-LG', $league_table->headings[2]->class);
        $this->assertEquals('League Points', $league_table->headings[2]->text);

        $this->assertCount(4, $league_table->rows);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[0][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][0]->class);
        $this->assertEquals('1.', $league_table->rows[0][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[0][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[0][1]->class);
        $this->assertEquals('Team 4', $league_table->rows[0][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[0][2]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][2]->class);
        $this->assertEquals('9', $league_table->rows[0][2]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[1][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][0]->class);
        $this->assertEquals('2.', $league_table->rows[1][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[1][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[1][1]->class);
        $this->assertEquals('Team 3', $league_table->rows[1][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[1][2]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][2]->class);
        $this->assertEquals('6', $league_table->rows[1][2]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[2][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][0]->class);
        $this->assertEquals('3.', $league_table->rows[2][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[2][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[2][1]->class);
        $this->assertEquals('Team 2', $league_table->rows[2][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[2][2]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][2]->class);
        $this->assertEquals('3', $league_table->rows[2][2]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[3][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][0]->class);
        $this->assertEquals('4.', $league_table->rows[3][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[3][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[3][1]->class);
        $this->assertEquals('Team 1', $league_table->rows[3][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[3][2]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][2]->class);
        $this->assertEquals('0', $league_table->rows[3][2]->text);
    }

    public function testHTMLLeagueForHTMLSetsPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $league_table = HTML::getLeagueTableForHTML($competition->getStageById('LS')->getGroupById('LG'));

        $this->assertEquals('vbc-league-table vbc-league-table-group-LG', $league_table->class);

        $this->assertCount(13, $league_table->headings);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->headings[0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-group-LG', $league_table->headings[0]->class);
        $this->assertEquals('Pos', $league_table->headings[0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->headings[1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->headings[1]->class);
        $this->assertEquals('Team', $league_table->headings[1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->headings[2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-group-LG', $league_table->headings[2]->class);
        $this->assertEquals('P', $league_table->headings[2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->headings[3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-group-LG', $league_table->headings[3]->class);
        $this->assertEquals('W', $league_table->headings[3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->headings[4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-group-LG', $league_table->headings[4]->class);
        $this->assertEquals('L', $league_table->headings[4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_DRAWS, $league_table->headings[5]->column_id);
        $this->assertEquals('vbc-league-table-draws vbc-league-table-group-LG', $league_table->headings[5]->class);
        $this->assertEquals('D', $league_table->headings[5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_FOR, $league_table->headings[6]->column_id);
        $this->assertEquals('vbc-league-table-sf vbc-league-table-group-LG', $league_table->headings[6]->class);
        $this->assertEquals('SF', $league_table->headings[6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_AGAINST, $league_table->headings[7]->column_id);
        $this->assertEquals('vbc-league-table-sa vbc-league-table-group-LG', $league_table->headings[7]->class);
        $this->assertEquals('SA', $league_table->headings[7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_DIFFERENCE, $league_table->headings[8]->column_id);
        $this->assertEquals('vbc-league-table-sd vbc-league-table-group-LG', $league_table->headings[8]->class);
        $this->assertEquals('SD', $league_table->headings[8]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->headings[9]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-group-LG', $league_table->headings[9]->class);
        $this->assertEquals('PF', $league_table->headings[9]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->headings[10]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-group-LG', $league_table->headings[10]->class);
        $this->assertEquals('PA', $league_table->headings[10]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->headings[11]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-group-LG', $league_table->headings[11]->class);
        $this->assertEquals('PD', $league_table->headings[11]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->headings[12]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-group-LG', $league_table->headings[12]->class);
        $this->assertEquals('PTS', $league_table->headings[12]->text);

        $this->assertCount(4, $league_table->rows);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[0][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][0]->class);
        $this->assertEquals('1.', $league_table->rows[0][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[0][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[0][1]->class);
        $this->assertEquals('Team 4', $league_table->rows[0][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->rows[0][2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][2]->class);
        $this->assertEquals('3', $league_table->rows[0][2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->rows[0][3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][3]->class);
        $this->assertEquals('3', $league_table->rows[0][3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->rows[0][4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][4]->class);
        $this->assertEquals('0', $league_table->rows[0][4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_DRAWS, $league_table->rows[0][5]->column_id);
        $this->assertEquals('vbc-league-table-draws vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][5]->class);
        $this->assertEquals('0', $league_table->rows[0][5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_FOR, $league_table->rows[0][6]->column_id);
        $this->assertEquals('vbc-league-table-sf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][6]->class);
        $this->assertEquals('6', $league_table->rows[0][6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_AGAINST, $league_table->rows[0][7]->column_id);
        $this->assertEquals('vbc-league-table-sa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][7]->class);
        $this->assertEquals('0', $league_table->rows[0][7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_DIFFERENCE, $league_table->rows[0][8]->column_id);
        $this->assertEquals('vbc-league-table-sd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][8]->class);
        $this->assertEquals('6', $league_table->rows[0][8]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->rows[0][9]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][9]->class);
        $this->assertEquals('151', $league_table->rows[0][9]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->rows[0][10]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][10]->class);
        $this->assertEquals('118', $league_table->rows[0][10]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->rows[0][11]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][11]->class);
        $this->assertEquals('33', $league_table->rows[0][11]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[0][12]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[0][12]->class);
        $this->assertEquals('9', $league_table->rows[0][12]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[1][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][0]->class);
        $this->assertEquals('2.', $league_table->rows[1][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[1][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[1][1]->class);
        $this->assertEquals('Team 3', $league_table->rows[1][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->rows[1][2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][2]->class);
        $this->assertEquals('3', $league_table->rows[1][2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->rows[1][3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][3]->class);
        $this->assertEquals('2', $league_table->rows[1][3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->rows[1][4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][4]->class);
        $this->assertEquals('1', $league_table->rows[1][4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_DRAWS, $league_table->rows[1][5]->column_id);
        $this->assertEquals('vbc-league-table-draws vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][5]->class);
        $this->assertEquals('0', $league_table->rows[1][5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_FOR, $league_table->rows[1][6]->column_id);
        $this->assertEquals('vbc-league-table-sf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][6]->class);
        $this->assertEquals('4', $league_table->rows[1][6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_AGAINST, $league_table->rows[1][7]->column_id);
        $this->assertEquals('vbc-league-table-sa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][7]->class);
        $this->assertEquals('2', $league_table->rows[1][7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_DIFFERENCE, $league_table->rows[1][8]->column_id);
        $this->assertEquals('vbc-league-table-sd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][8]->class);
        $this->assertEquals('2', $league_table->rows[1][8]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->rows[1][9]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][9]->class);
        $this->assertEquals('132', $league_table->rows[1][9]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->rows[1][10]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][10]->class);
        $this->assertEquals('140', $league_table->rows[1][10]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->rows[1][11]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][11]->class);
        $this->assertEquals('-8', $league_table->rows[1][11]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[1][12]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[1][12]->class);
        $this->assertEquals('6', $league_table->rows[1][12]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[2][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][0]->class);
        $this->assertEquals('3.', $league_table->rows[2][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[2][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[2][1]->class);
        $this->assertEquals('Team 2', $league_table->rows[2][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->rows[2][2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][2]->class);
        $this->assertEquals('3', $league_table->rows[2][2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->rows[2][3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][3]->class);
        $this->assertEquals('1', $league_table->rows[2][3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->rows[2][4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][4]->class);
        $this->assertEquals('2', $league_table->rows[2][4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_DRAWS, $league_table->rows[2][5]->column_id);
        $this->assertEquals('vbc-league-table-draws vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][5]->class);
        $this->assertEquals('0', $league_table->rows[2][5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_FOR, $league_table->rows[2][6]->column_id);
        $this->assertEquals('vbc-league-table-sf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][6]->class);
        $this->assertEquals('2', $league_table->rows[2][6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_AGAINST, $league_table->rows[2][7]->column_id);
        $this->assertEquals('vbc-league-table-sa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][7]->class);
        $this->assertEquals('4', $league_table->rows[2][7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_DIFFERENCE, $league_table->rows[2][8]->column_id);
        $this->assertEquals('vbc-league-table-sd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][8]->class);
        $this->assertEquals('-2', $league_table->rows[2][8]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->rows[2][9]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][9]->class);
        $this->assertEquals('138', $league_table->rows[2][9]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->rows[2][10]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][10]->class);
        $this->assertEquals('135', $league_table->rows[2][10]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->rows[2][11]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][11]->class);
        $this->assertEquals('3', $league_table->rows[2][11]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[2][12]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[2][12]->class);
        $this->assertEquals('3', $league_table->rows[2][12]->text);

        $this->assertEquals(HTML::LEAGUE_COLUMN_POSITION, $league_table->rows[3][0]->column_id);
        $this->assertEquals('vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][0]->class);
        $this->assertEquals('4.', $league_table->rows[3][0]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_TEAM, $league_table->rows[3][1]->column_id);
        $this->assertEquals('vbc-league-table-team vbc-league-table-group-LG', $league_table->rows[3][1]->class);
        $this->assertEquals('Team 1', $league_table->rows[3][1]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_PLAYED, $league_table->rows[3][2]->column_id);
        $this->assertEquals('vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][2]->class);
        $this->assertEquals('3', $league_table->rows[3][2]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_WINS, $league_table->rows[3][3]->column_id);
        $this->assertEquals('vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][3]->class);
        $this->assertEquals('0', $league_table->rows[3][3]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LOSSES, $league_table->rows[3][4]->column_id);
        $this->assertEquals('vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][4]->class);
        $this->assertEquals('3', $league_table->rows[3][4]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_DRAWS, $league_table->rows[3][5]->column_id);
        $this->assertEquals('vbc-league-table-draws vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][5]->class);
        $this->assertEquals('0', $league_table->rows[3][5]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_FOR, $league_table->rows[3][6]->column_id);
        $this->assertEquals('vbc-league-table-sf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][6]->class);
        $this->assertEquals('0', $league_table->rows[3][6]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_AGAINST, $league_table->rows[3][7]->column_id);
        $this->assertEquals('vbc-league-table-sa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][7]->class);
        $this->assertEquals('6', $league_table->rows[3][7]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_SETS_DIFFERENCE, $league_table->rows[3][8]->column_id);
        $this->assertEquals('vbc-league-table-sd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][8]->class);
        $this->assertEquals('-6', $league_table->rows[3][8]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_FOR, $league_table->rows[3][9]->column_id);
        $this->assertEquals('vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][9]->class);
        $this->assertEquals('123', $league_table->rows[3][9]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_AGAINST, $league_table->rows[3][10]->column_id);
        $this->assertEquals('vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][10]->class);
        $this->assertEquals('151', $league_table->rows[3][10]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_POINTS_DIFFERENCE, $league_table->rows[3][11]->column_id);
        $this->assertEquals('vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][11]->class);
        $this->assertEquals('-28', $league_table->rows[3][11]->text);
        $this->assertEquals(HTML::LEAGUE_COLUMN_LEAGUE_POINTS, $league_table->rows[3][12]->column_id);
        $this->assertEquals('vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG', $league_table->rows[3][12]->class);
        $this->assertEquals('0', $league_table->rows[3][12]->text);
    }

    public function testHTMLLeagueHTMLPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $league_table_html = HTML::getLeagueTableHTML($competition->getStageById('L')->getGroupById('LG'));

        $expected_table = '<table class="vbc-league-table vbc-league-table-group-LG">';
        $expected_table .= '<tr><th class="vbc-league-table-pos vbc-league-table-group-LG">Pos</th><th class="vbc-league-table-team vbc-league-table-group-LG">Team</th><th class="vbc-league-table-played vbc-league-table-group-LG">P</th><th class="vbc-league-table-wins vbc-league-table-group-LG">W</th><th class="vbc-league-table-losses vbc-league-table-group-LG">L</th><th class="vbc-league-table-pf vbc-league-table-group-LG">PF</th><th class="vbc-league-table-pa vbc-league-table-group-LG">PA</th><th class="vbc-league-table-pd vbc-league-table-group-LG">PD</th><th class="vbc-league-table-pts vbc-league-table-group-LG">PTS</th></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">1.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 4</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">80</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">70</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">10</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">9</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">2.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 3</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">1</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">75</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">75</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">6</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">3.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 2</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">1</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">76</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">74</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">3</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">4.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 1</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">72</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">84</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">-12</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">0</td></tr>';
        $expected_table .= '</table>';
        $expected_table .= '<p>Position is decided by wins, then points difference</p>';

        $this->assertEquals(
            $expected_table,
            $league_table_html
        );
    }

    public function testHTMLLeagueHTMLThisTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $league_table_html = HTML::getLeagueTableHTML($competition->getStageById('L')->getGroupById('LG'), null, 'TM3');

        $expected_table = '<table class="vbc-league-table vbc-league-table-group-LG">';
        $expected_table .= '<tr><th class="vbc-league-table-pos vbc-league-table-group-LG">Pos</th><th class="vbc-league-table-team vbc-league-table-group-LG">Team</th><th class="vbc-league-table-played vbc-league-table-group-LG">P</th><th class="vbc-league-table-wins vbc-league-table-group-LG">W</th><th class="vbc-league-table-losses vbc-league-table-group-LG">L</th><th class="vbc-league-table-pf vbc-league-table-group-LG">PF</th><th class="vbc-league-table-pa vbc-league-table-group-LG">PA</th><th class="vbc-league-table-pd vbc-league-table-group-LG">PD</th><th class="vbc-league-table-pts vbc-league-table-group-LG">PTS</th></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">1.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 4</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">80</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">70</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">10</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">9</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">2.</td><td class="vbc-league-table-team vbc-league-table-group-LG vbc-this-team">Team 3</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">1</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">75</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">75</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">6</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">3.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 2</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">1</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">76</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">74</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">3</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">4.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 1</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">72</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">84</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">-12</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">0</td></tr>';
        $expected_table .= '</table>';
        $expected_table .= '<p>Position is decided by wins, then points difference</p>';

        $this->assertEquals(
            $expected_table,
            $league_table_html
        );
    }

    public function testHTMLLeagueHTMLSetsPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $league_table_html = HTML::getLeagueTableHTML($competition->getStageById('LS')->getGroupById('LG'));

        $expected_table = '<table class="vbc-league-table vbc-league-table-group-LG">';
        $expected_table .= '<tr><th class="vbc-league-table-pos vbc-league-table-group-LG">Pos</th><th class="vbc-league-table-team vbc-league-table-group-LG">Team</th><th class="vbc-league-table-played vbc-league-table-group-LG">P</th><th class="vbc-league-table-wins vbc-league-table-group-LG">W</th><th class="vbc-league-table-losses vbc-league-table-group-LG">L</th><th class="vbc-league-table-draws vbc-league-table-group-LG">D</th><th class="vbc-league-table-sf vbc-league-table-group-LG">SF</th><th class="vbc-league-table-sa vbc-league-table-group-LG">SA</th><th class="vbc-league-table-sd vbc-league-table-group-LG">SD</th><th class="vbc-league-table-pf vbc-league-table-group-LG">PF</th><th class="vbc-league-table-pa vbc-league-table-group-LG">PA</th><th class="vbc-league-table-pd vbc-league-table-group-LG">PD</th><th class="vbc-league-table-pts vbc-league-table-group-LG">PTS</th></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">1.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 4</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-draws vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-sf vbc-league-table-num vbc-league-table-group-LG">6</td><td class="vbc-league-table-sa vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-sd vbc-league-table-num vbc-league-table-group-LG">6</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">151</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">118</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">33</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">9</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">2.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 3</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">1</td><td class="vbc-league-table-draws vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-sf vbc-league-table-num vbc-league-table-group-LG">4</td><td class="vbc-league-table-sa vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-sd vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">132</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">140</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">-8</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">6</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">3.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 2</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">1</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-draws vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-sf vbc-league-table-num vbc-league-table-group-LG">2</td><td class="vbc-league-table-sa vbc-league-table-num vbc-league-table-group-LG">4</td><td class="vbc-league-table-sd vbc-league-table-num vbc-league-table-group-LG">-2</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">138</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">135</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">3</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">4.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 1</td><td class="vbc-league-table-played vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-wins vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-losses vbc-league-table-num vbc-league-table-group-LG">3</td><td class="vbc-league-table-draws vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-sf vbc-league-table-num vbc-league-table-group-LG">0</td><td class="vbc-league-table-sa vbc-league-table-num vbc-league-table-group-LG">6</td><td class="vbc-league-table-sd vbc-league-table-num vbc-league-table-group-LG">-6</td><td class="vbc-league-table-pf vbc-league-table-num vbc-league-table-group-LG">123</td><td class="vbc-league-table-pa vbc-league-table-num vbc-league-table-group-LG">151</td><td class="vbc-league-table-pd vbc-league-table-num vbc-league-table-group-LG">-28</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">0</td></tr>';
        $expected_table .= '</table>';
        $expected_table .= '<p>Position is decided by wins, then sets difference, then points difference</p>';

        $this->assertEquals(
            $expected_table,
            $league_table_html
        );
    }

    public function testHTMLLeagueHTMLSetsControlledColumns() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-league.json');
        $league_table_config = new stdClass();
        $league_table_config->headings = [HTML::LEAGUE_COLUMN_POSITION, HTML::LEAGUE_COLUMN_TEAM, HTML::LEAGUE_COLUMN_LEAGUE_POINTS];
        $league_table_config->headingMap = [
            HTML::LEAGUE_COLUMN_POSITION => 'Position',
            HTML::LEAGUE_COLUMN_TEAM => 'Team Name',
            HTML::LEAGUE_COLUMN_LEAGUE_POINTS => 'League Points'
        ];
        $league_table_html = HTML::getLeagueTableHTML($competition->getStageById('LS')->getGroupById('LG'), $league_table_config, 'TM2');

        $expected_table = '<table class="vbc-league-table vbc-league-table-group-LG">';
        $expected_table .= '<tr><th class="vbc-league-table-pos vbc-league-table-group-LG">Position</th><th class="vbc-league-table-team vbc-league-table-group-LG">Team Name</th><th class="vbc-league-table-pts vbc-league-table-group-LG">League Points</th></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">1.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 4</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">9</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">2.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 3</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">6</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">3.</td><td class="vbc-league-table-team vbc-league-table-group-LG vbc-this-team">Team 2</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">3</td></tr>';
        $expected_table .= '<tr><td class="vbc-league-table-pos vbc-league-table-num vbc-league-table-group-LG">4.</td><td class="vbc-league-table-team vbc-league-table-group-LG">Team 1</td><td class="vbc-league-table-pts vbc-league-table-num vbc-league-table-group-LG">0</td></tr>';
        $expected_table .= '</table>';
        $expected_table .= '<p>Position is decided by wins, then sets difference, then points difference</p>';

        $this->assertEquals(
            $expected_table,
            $league_table_html
        );
    }

    public function testHTMLKnockoutStandingForHTMLPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-knockout.json');
        $final_standing = HTML::getFinalStandingForHTML($competition->getStageById('KO')->getGroupById('CUP'));

        $this->assertEquals('vbc-knockout', $final_standing->class);

        $this->assertCount(2, $final_standing->headings);
        $this->assertEquals('pos', $final_standing->headings[0]->column_id);
        $this->assertEquals('vbc-knockout-pos', $final_standing->headings[0]->class);
        $this->assertEquals('Pos', $final_standing->headings[0]->text);
        $this->assertEquals('team', $final_standing->headings[1]->column_id);
        $this->assertEquals('vbc-knockout-team', $final_standing->headings[1]->class);
        $this->assertEquals('Team', $final_standing->headings[1]->text);

        $this->assertCount(4, $final_standing->rows);

        $this->assertEquals('pos', $final_standing->rows[0][0]->column_id);
        $this->assertEquals('vbc-knockout-pos', $final_standing->rows[0][0]->class);
        $this->assertEquals('1st', $final_standing->rows[0][0]->text);
        $this->assertEquals('team', $final_standing->rows[0][1]->column_id);
        $this->assertEquals('vbc-knockout-team', $final_standing->rows[0][1]->class);
        $this->assertEquals('Grace VC', $final_standing->rows[0][1]->text);

        $this->assertEquals('pos', $final_standing->rows[1][0]->column_id);
        $this->assertEquals('vbc-knockout-pos', $final_standing->rows[1][0]->class);
        $this->assertEquals('2nd', $final_standing->rows[1][0]->text);
        $this->assertEquals('team', $final_standing->rows[1][1]->column_id);
        $this->assertEquals('vbc-knockout-team', $final_standing->rows[1][1]->class);
        $this->assertEquals('Frank VC', $final_standing->rows[1][1]->text);

        $this->assertEquals('pos', $final_standing->rows[2][0]->column_id);
        $this->assertEquals('vbc-knockout-pos', $final_standing->rows[2][0]->class);
        $this->assertEquals('3rd', $final_standing->rows[2][0]->text);
        $this->assertEquals('team', $final_standing->rows[2][1]->column_id);
        $this->assertEquals('vbc-knockout-team', $final_standing->rows[2][1]->class);
        $this->assertEquals('Charlie VC', $final_standing->rows[2][1]->text);

        $this->assertEquals('pos', $final_standing->rows[3][0]->column_id);
        $this->assertEquals('vbc-knockout-pos', $final_standing->rows[3][0]->class);
        $this->assertEquals('4th', $final_standing->rows[3][0]->text);
        $this->assertEquals('team', $final_standing->rows[3][1]->column_id);
        $this->assertEquals('vbc-knockout-team', $final_standing->rows[3][1]->class);
        $this->assertEquals('Bob VC', $final_standing->rows[3][1]->text);
    }

    public function testHTMLKnockoutStandingForHTMLThisTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-knockout.json');
        $final_standing = HTML::getFinalStandingForHTML($competition->getStageById('KO')->getGroupById('CUP'), 'TM7');

        $this->assertCount(4, $final_standing->rows);

        $this->assertEquals('vbc-knockout-team vbc-this-team', $final_standing->rows[0][1]->class);
        $this->assertEquals('vbc-knockout-team', $final_standing->rows[1][1]->class);
        $this->assertEquals('vbc-knockout-team', $final_standing->rows[2][1]->class);
        $this->assertEquals('vbc-knockout-team', $final_standing->rows[3][1]->class);
    }

    public function testHTMLKnockoutStandingForHTMLNoStanding() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-knockout-nostanding.json');
        $final_standing = HTML::getFinalStandingForHTML($competition->getStageById('KO')->getGroupById('CUP'));

        $this->assertEquals('vbc-knockout', $final_standing->class);

        $this->assertCount(2, $final_standing->headings);
        $this->assertEquals('pos', $final_standing->headings[0]->column_id);
        $this->assertEquals('vbc-knockout-pos', $final_standing->headings[0]->class);
        $this->assertEquals('Pos', $final_standing->headings[0]->text);
        $this->assertEquals('team', $final_standing->headings[1]->column_id);
        $this->assertEquals('vbc-knockout-team', $final_standing->headings[1]->class);
        $this->assertEquals('Team', $final_standing->headings[1]->text);

        $this->assertCount(0, $final_standing->rows);
    }

    public function testHTMLKnockoutStandingHTMLPlain() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-knockout.json');
        $final_standing_html = HTML::getFinalStandingHTML($competition->getStageById('KO')->getGroupById('CUP'));

        $expected_table = '<table class="vbc-knockout">';
        $expected_table .= '<tr><th class="vbc-knockout-pos">Pos</th><th class="vbc-knockout-team">Team</th></tr>';
        $expected_table .= '<tr><td class="vbc-knockout-pos">1st</td><td class="vbc-knockout-team">Grace VC</td></tr>';
        $expected_table .= '<tr><td class="vbc-knockout-pos">2nd</td><td class="vbc-knockout-team">Frank VC</td></tr>';
        $expected_table .= '<tr><td class="vbc-knockout-pos">3rd</td><td class="vbc-knockout-team">Charlie VC</td></tr>';
        $expected_table .= '<tr><td class="vbc-knockout-pos">4th</td><td class="vbc-knockout-team">Bob VC</td></tr>';
        $expected_table .= '</table>';

        $this->assertEquals(
            $expected_table,
            $final_standing_html
        );
    }

    public function testHTMLKnockoutStandingHTMLThisTeam() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-knockout.json');
        $final_standing_html = HTML::getFinalStandingHTML($competition->getStageById('KO')->getGroupById('CUP'), 'TM7');

        $expected_table = '<table class="vbc-knockout">';
        $expected_table .= '<tr><th class="vbc-knockout-pos">Pos</th><th class="vbc-knockout-team">Team</th></tr>';
        $expected_table .= '<tr><td class="vbc-knockout-pos">1st</td><td class="vbc-knockout-team vbc-this-team">Grace VC</td></tr>';
        $expected_table .= '<tr><td class="vbc-knockout-pos">2nd</td><td class="vbc-knockout-team">Frank VC</td></tr>';
        $expected_table .= '<tr><td class="vbc-knockout-pos">3rd</td><td class="vbc-knockout-team">Charlie VC</td></tr>';
        $expected_table .= '<tr><td class="vbc-knockout-pos">4th</td><td class="vbc-knockout-team">Bob VC</td></tr>';
        $expected_table .= '</table>';

        $this->assertEquals(
            $expected_table,
            $final_standing_html
        );
    }

    public function testHTMLKnockoutStandingHTMLNoStanding() : void
    {
        $competition = Competition::loadFromFile(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'html'))), 'complete-knockout-nostanding.json');
        $final_standing_html = HTML::getFinalStandingHTML($competition->getStageById('KO')->getGroupById('CUP'));

        $this->assertEquals(
            '',
            $final_standing_html
        );
    }
}
