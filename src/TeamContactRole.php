<?php

namespace VBCompetitions\Competitions;

/**
 * The role of the contact within a team.  There may me multiple contacts with the same role
 */
final class TeamContactRole
{
    /** A team treasurer */
    public const TREASURER = 'treasurer';
    /** A team secretary */
    public const SECRETARY = 'secretary';
    /** A team manager */
    public const MANAGER = 'manager';
    /** A team captain */
    public const CAPTAIN = 'captain';
    /** A team coach */
    public const COACH = 'coach';
    /** A team assistant coach */
    public const ASSISTANT_COACH = 'assistantCoach';
    /** A team medic */
    public const MEDIC = 'medic';

    static $valid_roles = [
        TeamContactRole::TREASURER,
        TeamContactRole::SECRETARY,
        TeamContactRole::MANAGER,
        TeamContactRole::CAPTAIN,
        TeamContactRole::COACH,
        TeamContactRole::ASSISTANT_COACH,
        TeamContactRole::MEDIC
    ];
}
