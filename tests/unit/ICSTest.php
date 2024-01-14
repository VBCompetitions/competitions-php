<?php

declare(strict_types=1);

namespace VBCompetitions\Competitions\test;

use PHPUnit\Framework\Attributes\CoversClass;

use PHPUnit\Framework\TestCase;
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\ICS;

#[CoversClass(ICS::class)]
final class ICSTest extends TestCase {
    public function testICSCalendarHeaders() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-no-times.json');
        $ics = new ICS($competition);

        $this->assertEquals('text/calendar', $ics->getContentType());
        $this->assertEquals('attachment; filename=calendar.ics', $ics->getContentDisposition('TM1', 'calendar.ics'));
        $this->assertEquals('attachment; filename=Alice VC-SuperLeague.ics', $ics->getContentDisposition('TM1'));
    }

    public function testICSDispositionNoSuchTeam() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-no-times.json');
        $ics = new ICS($competition);

        $this->expectExceptionMessage('Team with ID "FOO" does not exist');
        $ics->getContentDisposition('FOO', 'calendar.ics');
    }

    public function testICSCalendarNoSuchTeam() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-no-times.json');
        $ics = new ICS($competition);

        $this->expectExceptionMessage('Team with ID "FOO" does not exist');
        $ics->getCalendar('unique-id', 'FOO');
    }

    public function testICSCalendarBodyIngoresMatchesWithNoDate() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-no-times-missing-dates.json');
        $ics = new ICS($competition);

        $this->expectExceptionMessage('error while generating calendar: match {L:RL:RLM9} has no date');
        $ics->getCalendar('example.com', 'TM1');
    }

    public function testICSCalendarBodyNoTimes() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-no-times.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231015\r\n";
        $expectedBody .= "UID:D20231015TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231022\r\n";
        $expectedBody .= "UID:D20231022TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231029\r\n";
        $expectedBody .= "UID:D20231029TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231105\r\n";
        $expectedBody .= "UID:D20231105TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id, 'TM1'));
    }

    public function testICSCalendarBodyWithDurationsOnly() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-with-warmup-times-and-durations.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T144500\r\n";
        $expectedBody .= "UID:D20231015TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T144500\r\n";
        $expectedBody .= "UID:D20231022TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T113000\r\n";
        $expectedBody .= "DTEND:20231029T161500\r\n";
        $expectedBody .= "UID:D20231029TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T113000\r\n";
        $expectedBody .= "DTEND:20231105T144500\r\n";
        $expectedBody .= "UID:D20231105TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id, 'TM1'));
    }

    public function testICSCalendarBodyWithStartTimesOnly() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-with-start-times-only.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231015\r\n";
        $expectedBody .= "UID:D20231015TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:15 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:45 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:45 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231022\r\n";
        $expectedBody .= "UID:D20231022TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:15 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:45 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:45 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231029\r\n";
        $expectedBody .= "UID:D20231029TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:45 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:45 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:15 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231105\r\n";
        $expectedBody .= "UID:D20231105TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:45 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:45 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id, 'TM1'));
    }

    public function testICSCalendarBodyWithStartTimesAndDurations() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-with-start-times-and-durations.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T150000\r\n";
        $expectedBody .= "UID:D20231015TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T150000\r\n";
        $expectedBody .= "UID:D20231022TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T113000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T113000\r\n";
        $expectedBody .= "DTEND:20231105T150000\r\n";
        $expectedBody .= "UID:D20231105TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id, 'TM1'));
    }

    public function testICSCalendarBodyWithoutVenue() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-with-no-venue.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T150000\r\n";
        $expectedBody .= "UID:D20231015TTM1-".$unique_id."\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T150000\r\n";
        $expectedBody .= "UID:D20231022TTM1-".$unique_id."\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T113000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029TTM1-".$unique_id."\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T113000\r\n";
        $expectedBody .= "DTEND:20231105T150000\r\n";
        $expectedBody .= "UID:D20231105TTM1-".$unique_id."\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id, 'TM1'));
    }

    public function testICSCalendarBodyWithoutOfficials() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-no-officials.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T150000\r\n";
        $expectedBody .= "UID:D20231015TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T150000\r\n";
        $expectedBody .= "UID:D20231022TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v David VC\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T113000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Frank VC\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T113000\r\n";
        $expectedBody .= "DTEND:20231105T130000\r\n";
        $expectedBody .= "UID:D20231105TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Heidi VC\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id, 'TM1'));
    }

    public function testICSCalendarBodyMultipleStages() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-across-multiple-stages.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T150000\r\n";
        $expectedBody .= "UID:D20231015TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T150000\r\n";
        $expectedBody .= "UID:D20231022TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T113000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T113000\r\n";
        $expectedBody .= "DTEND:20231105T150000\r\n";
        $expectedBody .= "UID:D20231105TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:11:30 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id, 'TM1'));
    }

    public function testICSCalendarBodyMultipleStagesAll() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-across-multiple-stages.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T163000\r\n";
        $expectedBody .= "UID:D20231015T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - Charlie VC v David VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Grace VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:00 - Break\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v David VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Erin VC v Grace VC \(Bob VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T163000\r\n";
        $expectedBody .= "UID:D20231022T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Frank VC v Heidi VC \(David VC ref\)\r\n";
        $expectedBody .= " 10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Charlie VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Frank VC v Grace VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 15:00 court 6 - Bob VC v Frank VC \(David VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T100000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Charlie VC v Grace VC \(Bob VC ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - David VC v Heidi VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Erin VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - David VC v Grace VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T100000\r\n";
        $expectedBody .= "DTEND:20231105T163000\r\n";
        $expectedBody .= "UID:D20231105T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Bob VC v Heidi VC \(David VC ref\)\r\n";
        $expectedBody .= " 10:00 court 5 - Charlie VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - David VC v Frank VC \(Bob VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v Grace VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:00 court 6 - David VC v Erin VC \(Grace VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231119T100000\r\n";
        $expectedBody .= "UID:D20231119T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Shelbyville Astrodome\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 12:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 14:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 16:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id));
    }

    public function testICSCalendarBodyMultipleStagesAllBreakFirst() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-across-multiple-stages-break-first.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231015\r\n";
        $expectedBody .= "UID:D20231015T".$unique_id."\r\n";
        $expectedBody .= "DESCRIPTION:09:00 - Break\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T163000\r\n";
        $expectedBody .= "UID:D20231015T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - Charlie VC v David VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Grace VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:00 - Break\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v David VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Erin VC v Grace VC \(Bob VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T163000\r\n";
        $expectedBody .= "UID:D20231022T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Frank VC v Heidi VC \(David VC ref\)\r\n";
        $expectedBody .= " 10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Charlie VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Frank VC v Grace VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 15:00 court 6 - Bob VC v Frank VC \(David VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T100000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Charlie VC v Grace VC \(Bob VC ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - David VC v Heidi VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Erin VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - David VC v Grace VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T100000\r\n";
        $expectedBody .= "DTEND:20231105T163000\r\n";
        $expectedBody .= "UID:D20231105T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Bob VC v Heidi VC \(David VC ref\)\r\n";
        $expectedBody .= " 10:00 court 5 - Charlie VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - David VC v Frank VC \(Bob VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v Grace VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:00 court 6 - David VC v Erin VC \(Grace VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231119T100000\r\n";
        $expectedBody .= "UID:D20231119T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Shelbyville Astrodome\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 12:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 14:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 16:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id));
    }

    public function testICSCalendarBodyMultipleStagesAllSplitVenue() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-across-multiple-stages-split-venue.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T130000\r\n";
        $expectedBody .= "UID:D20231015T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - Charlie VC v David VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Grace VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:00 - Break\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T133000\r\n";
        $expectedBody .= "DTEND:20231015T163000\r\n";
        $expectedBody .= "UID:D20231015T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:City Sports Hall\r\n";
        $expectedBody .= "DESCRIPTION:13:30 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v David VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Erin VC v Grace VC \(Bob VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T163000\r\n";
        $expectedBody .= "UID:D20231022T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Frank VC v Heidi VC \(David VC ref\)\r\n";
        $expectedBody .= " 10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Charlie VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Frank VC v Grace VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 15:00 court 6 - Bob VC v Frank VC \(David VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T100000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Charlie VC v Grace VC \(Bob VC ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - David VC v Heidi VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Erin VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - David VC v Grace VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T100000\r\n";
        $expectedBody .= "DTEND:20231105T163000\r\n";
        $expectedBody .= "UID:D20231105T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Bob VC v Heidi VC \(David VC ref\)\r\n";
        $expectedBody .= " 10:00 court 5 - Charlie VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - David VC v Frank VC \(Bob VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v Grace VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:00 court 6 - David VC v Erin VC \(Grace VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231119T100000\r\n";
        $expectedBody .= "UID:D20231119T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Shelbyville Astrodome\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 12:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 14:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= " 16:00 court 1 - UNKNOWN v UNKNOWN \(UNKNOWN ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id));
    }

    public function testICSCalendarBodyWithStartTimesAndDurationsAll() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-with-start-times-and-durations.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T163000\r\n";
        $expectedBody .= "UID:D20231015T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - Charlie VC v David VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Grace VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:00 - Break\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v David VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Erin VC v Grace VC \(Bob VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T163000\r\n";
        $expectedBody .= "UID:D20231022T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Frank VC v Heidi VC \(David VC ref\)\r\n";
        $expectedBody .= " 10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Charlie VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Frank VC v Grace VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 15:00 court 6 - Bob VC v Frank VC \(David VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T100000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Charlie VC v Grace VC \(Bob VC ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - David VC v Heidi VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Erin VC \(Grace VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - David VC v Grace VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T100000\r\n";
        $expectedBody .= "DTEND:20231105T163000\r\n";
        $expectedBody .= "UID:D20231105T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Bob VC v Heidi VC \(David VC ref\)\r\n";
        $expectedBody .= " 10:00 court 5 - Charlie VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= " 11:30 court 6 - David VC v Frank VC \(Bob VC ref\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v Grace VC \(Frank VC ref\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " 15:00 court 6 - David VC v Erin VC \(Grace VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id));
    }

    public function testICSCalendarBodyWithFullOfficials() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-with-individual-refs.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231015T100000\r\n";
        $expectedBody .= "DTEND:20231015T163000\r\n";
        $expectedBody .= "UID:D20231015T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(First ref: J Blogs, Second ref: A Ref\)\r\n";
        $expectedBody .= " 10:00 court 6 - Charlie VC v David VC \(First ref: J Blogs, Scorer: B Dunn\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Frank VC \(First ref: J Blogs, Second ref: A Ref, Scorer: B Dunn\)\r\n";
        $expectedBody .= " 11:30 court 6 - Grace VC v Heidi VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 13:00 - Break\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Charlie VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v David VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 15:00 court 5 - Erin VC v Grace VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231022T100000\r\n";
        $expectedBody .= "DTEND:20231022T163000\r\n";
        $expectedBody .= "UID:D20231022T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Frank VC v Heidi VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 10:00 court 5 - Alice VC v David VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Charlie VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 11:30 court 5 - Erin VC v Heidi VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 13:30 court 6 - Frank VC v Grace VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 13:30 court 5 - Alice VC v Erin VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 15:00 court 6 - Bob VC v Frank VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231029T100000\r\n";
        $expectedBody .= "DTEND:20231029T163000\r\n";
        $expectedBody .= "UID:D20231029T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Charlie VC v Grace VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 10:00 court 6 - David VC v Heidi VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Frank VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 11:30 court 6 - Bob VC v Erin VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Heidi VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 13:30 court 6 - David VC v Grace VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 15:00 court 5 - Alice VC v Grace VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART:20231105T100000\r\n";
        $expectedBody .= "DTEND:20231105T163000\r\n";
        $expectedBody .= "UID:D20231105T".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 6 - Bob VC v Heidi VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 10:00 court 5 - Charlie VC v Erin VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 11:30 court 6 - David VC v Frank VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 11:30 court 5 - Alice VC v Heidi VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 13:30 court 6 - Bob VC v Grace VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 13:30 court 5 - Charlie VC v Frank VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= " 15:00 court 6 - David VC v Erin VC \(First ref: J Blogs\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id));
    }

    public function testICSCalendarFirstMatchStartTime() : void
    {
        $competition = new Competition(realpath(join(DIRECTORY_SEPARATOR, array(__DIR__, 'ics'))), 'competition-first-match-start-times.json');
        $ics = new ICS($competition);

        $unique_id = 'example.com';

        $expectedBody = "/^BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-\/\/vbcompetitionsdotcom\/\/VBC Calendar 1.0\/\/EN\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231015\r\n";
        $expectedBody .= "UID:D20231015TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v Bob VC \(Erin VC ref\)\r\n";
        $expectedBody .= " court 5 - Erin VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= " court 5 - Alice VC v Charlie VC \(Frank VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231022\r\n";
        $expectedBody .= "UID:D20231022TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:10:00 court 5 - Alice VC v David VC \(Erin VC ref\)\r\n";
        $expectedBody .= " court 5 - Erin VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " court 5 - Alice VC v Erin VC \(Heidi VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231029\r\n";
        $expectedBody .= "UID:D20231029TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:court 5 - Alice VC v Frank VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " court 5 - Charlie VC v Heidi VC \(Alice VC ref\)\r\n";
        $expectedBody .= " court 5 - Alice VC v Grace VC \(Erin VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "BEGIN:VEVENT\r\n";
        $expectedBody .= "SUMMARY:Alice VC SuperLeague matches\r\n";
        $expectedBody .= "DTSTAMP:\d{8}T\d{6}\r\n";
        $expectedBody .= "DTSTART;VALUE=DATE:20231105\r\n";
        $expectedBody .= "UID:D20231105TTM1-".$unique_id."\r\n";
        $expectedBody .= "LOCATION:Springfield Town Sports Centre\r\n";
        $expectedBody .= "DESCRIPTION:court 5 - Alice VC v Heidi VC \(Charlie VC ref\)\r\n";
        $expectedBody .= " court 5 - Charlie VC v Frank VC \(Alice VC ref\)\r\n";
        $expectedBody .= "END:VEVENT\r\n";

        $expectedBody .= "END:VCALENDAR\r\n$/";

        $this->assertMatchesRegularExpression($expectedBody, $ics->getCalendar($unique_id, 'TM1'));
    }
}
