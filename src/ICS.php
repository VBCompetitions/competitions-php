<?php

namespace VBCompetitions\Competitions;

use Exception;
use DateInterval;

class ICS {
    private Competition $c;

    /**
     * Creates an ICS calendar object that can generate ICS calendars from a competition
     *
     * @param Competition $competition The competition associated with the calendar
     */
    function __construct(Competition $competition)
    {
        $this->c = $competition;
    }

    /**
     * Return the HTTP content-type of the calendar.  Hard-coded to "text/calendar"
     *
     * @return string the standard ICS content type text/calendar
     */
    public function getContentType() : string
    {
        return 'text/calendar';
    }

    /**
     * Return the content disposition of the ICS calendar, i.e. name for the file to be downloaded.
     * If $filename is defined then use that, otherwise use the team name
     *
     * @param string $team_id the team ID of the team the calendar is for
     * @param string $filename the filename for the calendar
     *
     * @return string the ICS file's content disposition, i.e. "attachment; filename={filename}"
     */
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
     * Generate the calendar body.  When $team_id is specified, the calendar will only include matches that team is definitely playing
     * in or officiating.  when $team_id is null, the calendar will include all matches in the competition, including ones where the
     * playing teams are not yet known
     *
     * @param string $unique_id some unique string such as the domain name of the page generating the calendars.  This is used
     *                          in the UID field such that the UID will contain the date and time of the calendar entry, plus this
     *                          string
     * @param string $team_id the ID for the team taht the calendar is for.  When not specified the calendar will contain all matches
     *
     * @return string the body of the calendar
     */
    public function getCalendar(string $unique_id, string $team_id = null) : string
    {
        if (!is_null($team_id) && !$this->c->teamIdExists($team_id)) {
            throw new Exception('Team with ID "'.$team_id.'" does not exist');
        }

        $cal = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//vbcompetitionsdotcom//VBC Calendar 1.0//EN\r\n";

        $matches_grouped_by_date_and_venue = [];
        foreach ($this->c->getStages() as $stage) {
            $matches = $stage->getMatches($team_id, VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);
            $last_seen_venue = 'unknown';
            foreach ($matches as $match) {
                $date = $match->getDate();
                $venue = 'unknown';

                if (is_null($date)) {
                    if ($match instanceof GroupMatch) {
                        throw new Exception('error while generating calendar: match {'.$stage->getID().':'.$match->getGroup()->getID().':'.$match->getID().'} has no date');
                    } else {
                        // If a break has no date then we can't reliably work out the date, but it's also not a breaking problem,
                        // so don't throw an exception and just don't include it in the calendar entry
                        continue;
                    }
                }

                if (!array_key_exists($date, $matches_grouped_by_date_and_venue)) {
                    $matches_grouped_by_date_and_venue[$date] = [];
                    $last_seen_venue = 'unknown';
                }

                if ($match instanceof GroupMatch) {
                    if (!is_null($match->getVenue())) {
                        $venue = $match->getVenue();
                        $last_seen_venue = $venue;
                    }
                } else {
                    $venue = $last_seen_venue;
                }

                if (!array_key_exists($venue, $matches_grouped_by_date_and_venue[$date])) {
                    $matches_grouped_by_date_and_venue[$date][$venue] = [];
                }

                array_push($matches_grouped_by_date_and_venue[$date][$venue], $match);
            }
        }

        $now = new \DateTime('NOW');

        foreach ($matches_grouped_by_date_and_venue as $date => $matches_on_date) {
            foreach ($matches_on_date as $venue => $matches_on_date_at_venue) {
                $cal .= "BEGIN:VEVENT\r\n";
                $cal .= 'SUMMARY:';
                if (!is_null($team_id)) {
                    $cal .= $this->c->getTeamByID($team_id)->getName().' ';
                }
                $cal .= $this->c->getName()." matches\r\n";
                $cal .= 'DTSTAMP:' . date_format($now,'Ymd\THis') . "\r\n";
                $match_count = count($matches_on_date_at_venue);
                $all_fixtures_have_warmup = $match_count > 0;
                $all_fixtures_have_start = $match_count > 0;
                $all_fixtures_have_duration = $match_count > 0;
                for ($i = 0; $i < $match_count; $i++) {
                    if ($matches_on_date_at_venue[$i] instanceof GroupBreak) {
                        continue;
                    }
                    if (is_null($matches_on_date_at_venue[$i]->getWarmup())) {
                        $all_fixtures_have_warmup = false;
                    }
                    if (is_null($matches_on_date_at_venue[$i]->getStart())) {
                        $all_fixtures_have_start = false;
                    }
                    if (is_null($matches_on_date_at_venue[$i]->getDuration())) {
                        $all_fixtures_have_duration = false;
                    }
                }
                if ($all_fixtures_have_warmup && $match_count > 0 && $matches_on_date_at_venue[0] instanceof GroupMatch) {
                    $cal .= 'DTSTART:'.str_replace('-', '', $date).'T'.str_replace(':', '', $matches_on_date_at_venue[0]->getWarmup())."00\r\n";
                    if ($all_fixtures_have_duration) {
                        for ($i = count($matches_on_date_at_venue) - 1; $i >= 0; $i--) {
                            if ($matches_on_date_at_venue[$i] instanceof GroupMatch) {
                                $last_match = $matches_on_date_at_venue[$i];
                                if ($all_fixtures_have_start) {
                                    $start_time = $last_match->getStart();
                                } else {
                                    $start_time = $last_match->getWarmup();
                                }
                                $duration_parts = explode(':', $last_match->getDuration(), 2);
                                $duration = new DateInterval('PT'.$duration_parts[0].'H'.$duration_parts[1].'M');
                                $end_time = date_add(date_create($date." ".$start_time), $duration);
                                $cal .= 'DTEND:'.date_format($end_time,'Ymd\THis')."\r\n";
                                break;
                            }
                        }
                    }
                } else {
                    $cal .= 'DTSTART;VALUE=DATE:'.str_replace('-', '', $date)."\r\n";
                }
                $cal .= 'UID:D'.str_replace('-', '', $date).'T';
                if (!is_null($team_id)){
                    $cal .= $team_id.'-';
                }
                $cal .= $unique_id."\r\n";
                if ($venue !== 'unknown') {
                    $cal .= 'LOCATION:'.$venue."\r\n";
                }
                $cal .= 'DESCRIPTION:';
                for ($i = 0; $i < count($matches_on_date_at_venue); $i++) {
                    if ($matches_on_date_at_venue[$i] instanceof GroupMatch) {
                        $cal .= ($i === 0 ? '' : ' ').$this->getMatchDescription($matches_on_date_at_venue[$i], $all_fixtures_have_warmup, $all_fixtures_have_start)."\r\n";
                    } else {
                        $cal .= ($i === 0 ? '' : ' ').$this->getBreakDescription($matches_on_date_at_venue[$i]);
                    }
                }
                $cal .= "END:VEVENT\r\n";
            }
        }

        $cal .= "END:VCALENDAR\r\n";
        return $cal;
    }

    /**
     * Function to generate the event description for the break
     *
     * @param BreakInterface $break the break to generate the description for
     *
     * @return string the description of the break
     */
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

    /**
     * Function to generate the event description for the match
     *
     * @param MatchInterface $match the match to generate the description for
     *
     * @return string the description of the match
     */
    private function getMatchDescription(MatchInterface $match) : string
    {
        // TODO if the teams are unknown then use IfUnknown in favour

        $description = '';

        if (!is_null($match->getWarmup())) {
            $description .= $match->getWarmup().' ';
        } else if (!is_null($match->getStart())) {
            $description .= $match->getStart().' ';
        }
        if (!is_null($match->getCourt())) {
            $description .= 'court '.$match->getCourt().' ';
        }
        $description .= '- '.$this->c->getTeamByID($match->getHomeTeam()->getID())->getName().' v '.$this->c->getTeamByID($match->getAwayTeam()->getID())->getName();

        if (!is_null($match->getOfficials())) {
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
