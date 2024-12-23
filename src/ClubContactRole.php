<?php

namespace VBCompetitions\Competitions;

/**
 * The role of the contact within a club.  There may me multiple contacts with the same role
 */
final class ClubContactRole
{
    /** A chair */
    public const CHAIR = 'chair';
    /** A vice chair */
    public const VICE = 'vice';
    /** A treasurer */
    public const TREASURER = 'treasurer';
    /** A secretary */
    public const SECRETARY = 'secretary';
    /** A welfare officer */
    public const WELFARE = 'welfare';
    /** A communications officer */
    public const COMMUNICATIONS = 'communications';
    /** A marketing officer */
    public const MARKETING = 'marketing';
    /** A volunteers officer */
    public const VOLUNTEER = 'volunteer';
    /** A logistics officer */
    public const LOGISTICS = 'logistics';
    /** A coaching officer */
    public const COACHING = 'coaching';

    static $valid_roles = [
        ClubContactRole::CHAIR,
        ClubContactRole::VICE,
        ClubContactRole::TREASURER,
        ClubContactRole::SECRETARY,
        ClubContactRole::WELFARE,
        ClubContactRole::COMMUNICATIONS,
        ClubContactRole::MARKETING,
        ClubContactRole::VOLUNTEER,
        ClubContactRole::LOGISTICS,
        ClubContactRole::COACHING
    ];
}
