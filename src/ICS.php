<?php

namespace VBCompetitions\Competitions;

use Exception;
use DateInterval;

class ICS {
    private Competition $c;

    function __construct(Competition $competition)
    {
        $this->c = $competition;
    }

    public function getContentType() : string
    {
        return 'text/calendar';
    }

    public function getContentDisposition(string $team_id, string $filename = null) : string
    {
        if (!$this->c->teamIdExists($team_id)) {
            throw new Exception('Team with ID "'.$team_id.'" does not exist', 1);
        }

        if (is_null($filename)) {
            $filename = $this->c->getTeamByID($team_id)->getName() . '-' . $this->c->getName() . '.ics';
        }
        // TODO - allow filename override
        // If using the name then need to limit the valid chars in a team name?
        // Or need to filter and remove bad chars
        // need to handle competition name being undefined
        return 'attachment; filename='. $filename;
    }

    /**
     * when team_id is specified, only get matches that team is definitely playing in or officiating.  when team_id is null, return all matches, including ones where the playing teams are not yet known
     */
    public function getCalendar(string $unique_id, string $team_id = null) : string
    {
        if (!is_null($team_id) && !$this->c->teamIdExists($team_id)) {
            throw new Exception('Team with ID "'.$team_id.'" does not exist');
        }

        // How to group into dates? We don't have a "getDates" function for a team - maybe we should?  We solved it in a different way for displaying
        // What if competition has no dates? we'd have to throw...
        $cal = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//vbcompetitionsdotcom//VBC Calendar 1.0//EN\r\n";

        $matches_grouped_by_dates = [];
        foreach ($this->c->getStages() as $stage) {
            $matches = $stage->getMatches($team_id, VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);
            foreach ($matches as $match) {
                $date = $match->getDate();

                if (is_null($date)) {
                    if ($match instanceof GroupMatch) {
                        throw new Exception('Match {'.$stage->getID().':'.$match->getGroup()->getID().':'.$match->getID().'} has no date');
                    } else {
                        // If a break has no date then we can't reliably work out the date, so ignore it
                        continue;
                    }
                }

                if (!array_key_exists($date, $matches_grouped_by_dates)) {
                    $matches_grouped_by_dates[$date] = [];
                }
                array_push($matches_grouped_by_dates[$date], $match);
            }
        }

        $now = new \DateTime('NOW');

        foreach ($matches_grouped_by_dates as $date => $fixtures) {
            $cal .= "BEGIN:VEVENT\r\n";
            $cal .= 'SUMMARY:';
            if (!is_null($team_id)) {
                $cal .= $this->c->getTeamByID($team_id)->getName().' ';
            }
            $cal .= $this->c->getName()." matches\r\n";
            $cal .= 'DTSTAMP:' . date_format($now,'Ymd\THis') . "\r\n";
            $fixture_count = count($fixtures);
            $all_fixtures_have_warmup = $fixture_count > 0;
            $all_fixtures_have_start = $fixture_count > 0;
            $all_fixtures_have_duration = $fixture_count > 0;
            for ($i = 0; $i < $fixture_count; $i++) {
                if ($fixtures[$i] instanceof GroupBreak) {
                    continue;
                }
                if (is_null($fixtures[$i]->getWarmup())) {
                    $all_fixtures_have_warmup = false;
                }
                if (is_null($fixtures[$i]->getStart())) {
                    $all_fixtures_have_start = false;
                }
                if (is_null($fixtures[$i]->getDuration())) {
                    $all_fixtures_have_duration = false;
                }
            }
            if ($all_fixtures_have_warmup && $fixture_count > 0) {
                $cal .= 'DTSTART:'.str_replace('-', '', $date).'T'.str_replace(':', '', $fixtures[0]->getWarmup())."00\r\n";
                if ($all_fixtures_have_duration) {
                    if ($all_fixtures_have_start) {
                        $start_time = $fixtures[array_key_last($fixtures)]->getStart();
                    } else {
                        $start_time = $fixtures[array_key_last($fixtures)]->getWarmup();
                    }
                    $duration_parts = explode(':', $fixtures[array_key_last($fixtures)]->getDuration(), 2);
                    $duration = new DateInterval('PT'.$duration_parts[0].'H'.$duration_parts[1].'M');
                    // DTEND:20231105T150000
                    // from
                    // "date": "2023-11-05", "warmup": "13:30", "start": "13:45", "duration": "1:15"
                    $end_time = date_add(date_create($date." ".$start_time), $duration);
                    $cal .= 'DTEND:'.date_format($end_time,'Ymd\THis')."\r\n";
                }
            } else {
                $cal .= 'DTSTART;VALUE=DATE:'.str_replace('-', '', $date)."\r\n";
            }
            $cal .= 'UID:D'.str_replace('-', '', $date).'T';
            if (!is_null($team_id)){
                $cal .= $team_id.'-';
            }
            $cal .= $unique_id."\r\n";
            if ($fixture_count > 0 && !is_null($fixtures[0]->getVenue())) {
                $cal .= 'LOCATION:'.$fixtures[0]->getVenue()."\r\n";
            }
            $cal .= 'DESCRIPTION:';
            if ($fixture_count > 0) {
                if ($fixtures[0] instanceof GroupMatch) {
                    $cal .= $this->getMatchDescription($fixtures[0], $all_fixtures_have_warmup, $all_fixtures_have_start)."\r\n";
                } else {
                    $cal .= $this->getBreakDescription($fixtures[0]->getName());
                }
            }
            for ($i = 1; $i < count($fixtures); $i++) {
                if ($fixtures[$i] instanceof GroupMatch) {
                    $cal .= ' '.$this->getMatchDescription($fixtures[$i], $all_fixtures_have_warmup, $all_fixtures_have_start)."\r\n";
                } else {
                    $cal .= ' '.$this->getBreakDescription($fixtures[$i]);
                }
            }
            $cal .= "END:VEVENT\r\n";
        }

        $cal .= "END:VCALENDAR\r\n";
        return $cal;
    }

    private function getBreakDescription(BreakInterface $break) : string
    {
        $description = '';
        if (!is_null($break->getStart())) {
            $description .= $break->getStart().' - ';
        }
        if (!is_null($break->getName())) {
            $description .= $break->getName();
        }
        return $description."\r\n";
    }

    private function getMatchDescription(MatchInterface $match, bool $all_fixtures_have_warmup, bool $all_fixtures_have_start) : string
    {
        $description = '';
        // Ignore IfUnknownMatch ?
        // TODO
        // Actually, should IfUnknown be used in favour when teams are unknown?  Yes!
        if (!$match instanceof GroupMatch) {
            return $description;
        }

        // TODO - should we just only print times when they exist?  It'd help with HVA-style events
        // where only the first match has a time...
        // Yes!
        if ($all_fixtures_have_warmup) {
            $description .= $match->getWarmup().' ';
        } else if ($all_fixtures_have_start) {
            $description .= $match->getStart().' ';
        }
        if (!is_null($match->getCourt())) {
            $description .= 'court '.$match->getCourt().' ';
        }
        $description .= '- '.$this->c->getTeamByID($match->getHomeTeam()->getID())->getName().' v '.$this->c->getTeamByID($match->getAwayTeam()->getID())->getName();

        if (!property_exists($match, 'officials')) {
            return $description;
        } else if (!is_null($match->getOfficials())) {
            if (property_exists($match->getOfficials(), 'team')) {
                $description .= ' ('.$this->c->getTeamByID($match->getOfficials()->team)->getName().' ref)';
            } else {
                $description .= ' (First ref: '.$match->getOfficials()->first;
                $description .= property_exists($match->getOfficials(), 'second') ? ', Second ref: '.$match->getOfficials()->second : '';
                $description .= property_exists($match->getOfficials(), 'scorer') ? ', Scorer: '.$match->getOfficials()->scorer : '';
                $description .= ')';
            }
        }

        return $description;
    }
}
