<?php

namespace VBCompetitions\Competitions;

/**
 * The role of the contact within a competition.  There may me multiple contacts with the same role
 */
final class CompetitionContactRole
{
    /** A competition director */
    public const DIRECTOR = 'director';
    /** A fixtures officer */
    public const FIXTURES = 'fixtures';
    /** A logistics officer */
    public const LOGISTICS = 'logistics';
    /** A communications officer */
    public const COMMUNICATIONS = 'communications';
    /** An officials officer */
    public const OFFICIALS = 'officials';
    /** A results officer */
    public const RESULTS = 'results';
    /** A marketing officer */
    public const MARKETING = 'marketing';
    /** A safety officer */
    public const SAFETY = 'safety';
    /** A volunteers organiser */
    public const VOLUNTEER = 'volunteer';
    /** A welfare organiser */
    public const WELFARE = 'welfare';
    /** A hospitality officer */
    public const HOSPITALITY  = 'hospitality';
    /** A ceremonies officer */
    public const CEREMONIES = 'ceremonies';
    /** A secretary */
    public const SECRETARY = 'secretary';
    /** A treasurer */
    public const TREASURER = 'treasurer';
    /** A medic */
    public const MEDIC = 'medic';

    static $valid_roles = [
        CompetitionContactRole::DIRECTOR,
        CompetitionContactRole::FIXTURES,
        CompetitionContactRole::LOGISTICS,
        CompetitionContactRole::COMMUNICATIONS,
        CompetitionContactRole::OFFICIALS,
        CompetitionContactRole::RESULTS,
        CompetitionContactRole::MARKETING,
        CompetitionContactRole::SAFETY,
        CompetitionContactRole::VOLUNTEER,
        CompetitionContactRole::WELFARE,
        CompetitionContactRole::HOSPITALITY ,
        CompetitionContactRole::CEREMONIES,
        CompetitionContactRole::SECRETARY,
        CompetitionContactRole::TREASURER,
        CompetitionContactRole::MEDIC
    ];
}
