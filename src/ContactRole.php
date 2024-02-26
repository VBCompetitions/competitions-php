<?php

namespace VBCompetitions\Competitions;

/**
 * The role of the contact within a team.  There may me multiple contacts with the same role
 */
enum ContactRole: string
{
    /** A team treasurer */
    case TREASURER = 'treasurer';
    /** A team secretary */
    case SECRETARY = 'secretary';
    /** A team manager */
    case MANAGER = 'manager';
    /** A team captain */
    case CAPTAIN = 'captain';
    /** A team coach */
    case COACH = 'coach';
    /** A team assistant coach */
    case ASSISTANT_COACH = 'assistantCoach';
    /** A team medic */
    case MEDIC = 'medic';
}
