<?php

namespace VBCompetitions\Competitions;

use Opis\JsonSchema\{
    Validator,
    Errors\ErrorFormatter,
};
use Exception;
use JsonSerializable;
use OutOfBoundsException;
use stdClass;
use Throwable;

/**
 * The teams, the competition structure, the matches and the results of a volleyball competition
 */
final class Competition implements JsonSerializable
{
    /** The version of schema that the document conforms to. Defaults to 1.0.0 */
    private string $version = '1.0.0';

    /** A name for the competition */
    private string $name;

    /** Free form string to add notes about the competition.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** The list of all teams in this competition */
    private array $teams = [];

    /**
     * The stages of the competition. Stages are phases of a competition that happen in order.  There may be only one stage (e.g. for a flat league) or multiple in sequence
     * (e.g. for a tournament with pools, then crossovers, then finals)
     */
    private array $stages = [];

    /** A Lookup table from team IDs (including references) to the team */
    private object $team_lookup;

    /** A Lookup table from stage IDs to the stage */
    private object $stage_lookup;

    /** The filename that was loaded into this Competition */
    private string $filename;

    /** The "unknown" team, typically for matching against */
    private CompetitionTeam $unknown_team;

    /**
     * Loads a competition file and parses its content, creating any metadata needed
     *
     * @param string $competition_data_dir The directory to load the competition file from
     * @param string $competition_file The name of the competition file
     *
     * @throws Exception thrown when the competition data is invalid
     */
    function __construct(string $competition_data_dir, string $competition_file)
    {
        $this->filename = $competition_file;

        $competition_data = json_decode(file_get_contents(realpath($competition_data_dir.DIRECTORY_SEPARATOR.$competition_file)));

        if ($competition_data === null) {
            throw new Exception('Document does not contain valid JSON');
        }

        if (property_exists($competition_data, 'version')) {
            $this->version = $competition_data->version;
        }

        // This supports only version 1.0.0 (and all documents without an explicit version are assumed to be at version 1.0.0)
        if (version_compare($this->version, '1.0.0', 'ne')) {
            throw new Exception('Document version '.$this->version.' not supported');
        }

        $this->validateJSON($competition_data);

        $unknown_team_data = new stdClass();
        $unknown_team_data->id = CompetitionTeam::UNKNOWN_TEAM_ID;
        $unknown_team_data->name = CompetitionTeam::UNKNOWN_TEAM_NAME;
        $this->unknown_team = new CompetitionTeam($unknown_team_data);

        $this->team_lookup = new stdClass();
        $this->stage_lookup = new stdClass();

        foreach ($competition_data->teams as $team_data) {
            $new_team = new CompetitionTeam($team_data);
            array_push($this->teams, $new_team);
            if (property_exists($this->team_lookup, $new_team->getID())) {
                throw new Exception('Competition data failed validation. Teams with duplicate IDs not allowed: "'.$new_team->getID().'"');
            }
            $this->team_lookup->{$new_team->getID()} = $new_team;
        }

        $this->name = $competition_data->name;

        if (property_exists($competition_data, 'notes')) {
            $this->notes = $competition_data->notes;
        }

        foreach ($competition_data->stages as $stage_data) {
            new Stage($this, $stage_data);
        }
    }

    /**
     * Save the whole Competition as a competition JSON file
     *
     * @param string $competition_data_dir The directory to save the competition file to
     * @param string $competition_file The name of the competition file
     *
     * @throws Exception thrown when the competition file cannot be saved

     */
    public function save(string $competition_data_dir, string $competition_file)
    {
        $competition_data = json_encode($this, JSON_PRETTY_PRINT);
        Competition::validateJSON(json_decode($competition_data));
        file_put_contents(realpath($competition_data_dir)."/".$competition_file, $competition_data, LOCK_EX);
    }

    public function appendStage(Stage $new_stage) : void
    {
        array_push($this->stages, $new_stage);
        if (property_exists($this->stage_lookup, $new_stage->getID())) {
            throw new Exception('Competition data failed validation. Stages with duplicate IDs not allowed: {'.$new_stage->getID().'}');
        }
        $this->stage_lookup->{$new_stage->getID()} = $new_stage;
    }

    /**
     * Get the schema version for this competition, as a semver string
     *
     * @return string the schema version
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * Get the name for this competition
     *
     * @return string the competition name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the competition Name
     *
     * @param string $name the new name for the competition
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * Get the notes for this competition
     *
     * @return string|null the notes for this competition
     */
    public function getNotes() : string|null
    {
        return $this->notes;
    }

    /**
     * Get the teams in this competition
     *
     * @return array the teams in this competition
     */
    public function getTeams() : array
    {
        return $this->teams;
    }

    /**
     * Gets the Team for the given team ID
     *
     * @param string $team_id The team ID to look up. This may be a pure ID, a reference or a ternary
     *
     * @return CompetitionTeam The team
     */
    public function getTeamByID(string $team_id) : CompetitionTeam
    {
        if (strncmp($team_id, '{', 1) !== 0) {
            if (property_exists($this->team_lookup, $team_id)) {
                return $this->team_lookup->$team_id;
            }
        }

        /*
         * Check for ternaries like {team_id_1}=={team_id_2}?{team_id_true}:{team_id_false}
         * Note that we only allow one level of ternary, i.e. this does not resolve:
         *  {{ta}=={tb}?{t_true}:{t_false}}=={T2}?{T_True}:{T_False}
         */
        if (preg_match('/^([^=]*)==([^?]*)\?(.*)$/', $team_id, $lr_matches)) {
            $left_team = $this->getTeamByID($lr_matches[1]);
            $right_team = $this->getTeamByID($lr_matches[2]);
            $true_team = null;
            if (preg_match('/^({[^}]*}):(.*)$/', $lr_matches[3], $tf_matches)) {
                $true_team = $this->getTeamByID($tf_matches[1]);
                $false_team = $this->getTeamByID($tf_matches[2]);
            } elseif (preg_match('/^([^:]*):(.*)$/', $lr_matches[3], $tf_matches)) {
                $true_team = $this->getTeamByID($tf_matches[1]);
                $false_team = $this->getTeamByID($tf_matches[2]);
            }
            if (!is_null($true_team)) {
                return $left_team == $right_team ? $true_team : $false_team;
            }
        }

        if (preg_match('/^{.*}$/', $team_id)) {
            // As we go through the results, we populate the lookup table
            // e.g. when a league group is complete, we can populate {Stage1:Group1:league:1} with the team in position 1 in Group 1 in Stage 1
            // If we look up a key and get a hit then the team is known, if not then we don't know so return "unknown"
            if (property_exists($this->team_lookup, $team_id)) {
                return $this->team_lookup->{$team_id};
            }
        }

        return $this->unknown_team;
    }

    /**
     * Get the stages in this competition
     *
     * @return array the stages in this competition
     */
    public function getStages() : array
    {
        return $this->stages;
    }

    /**
     * Returns the Stage with the requested ID, or throws if the ID is not found
     *
     * @param string $stage_id The ID of the stage to return
     *
     * @throws OutOfBoundsException No Stage with the requested ID was not found
     *
     * @return Stage the requested stage
     */
    public function getStageById(string $stage_id) : Stage
    {
        if (!property_exists($this->stage_lookup, $stage_id)) {
            throw new OutOfBoundsException('Stage with ID '.$stage_id.' not found');
        }
        return $this->stage_lookup->$stage_id;
    }

    /**
     * Gets the name of the file loaded for this Competition
     *
     * @return string The filename for this Competition
     */
    public function getFilename() : string
    {
        return $this->filename;
    }

    /**
     * Serialize the data in JSON format
     */
    public function jsonSerialize() : mixed
    {
        $competition = new stdClass();

        $competition->version = $this->version;

        $competition->name = $this->name;

        if ($this->notes !== null) {
            $competition->notes = $this->notes;
        }

        $competition->teams = $this->teams;

        $competition->stages = $this->stages;

        return $competition;
    }

    /**
     * Returns an array of competitions in the form below
     * {
     *   file: (string) name of the competition file,
     *   name: (string) name of the competition,
     *   is_valid: (bool) whether the file passes validation,
     *   is_complete: (bool) whether the competition has completed (all matches are complete)
     * }
     *
     * @param string $competition_data_dir The directory to scan for competition files
     *
     * @return array The list of competitions found in the given directory
     */
    public static function competitionList(string $competition_data_dir) : array
    {
        $list = [];
        $competition_file_list = scandir($competition_data_dir);
        foreach($competition_file_list as $competition_file) {
            $real_path = realpath($competition_data_dir.DIRECTORY_SEPARATOR.$competition_file);
            if (!is_file($real_path)) {
                continue;
            }
            $path_parts = pathinfo($real_path);
            if ($path_parts['extension'] != 'json' || strlen($path_parts['filename']) < 1) {
                continue;
            }

            $competition_item = new stdClass();
            try {
                $competition = new Competition($competition_data_dir, $competition_file);
                $competition_item->file = $competition_file;
                $competition_item->is_valid = true;
                $competition_item->is_complete = $competition->isComplete();
                $competition_item->name = $competition->name;
            } catch (Throwable $th) {
                $competition_item->is_valid = false;
                $competition_item->file = $competition_file;
                $competition_item->error_message = $th->getMessage();
            }
            array_push($list, $competition_item);
        }

        return $list;
    }

    /**
     * Loads a competition file without initialising the objects, searches for the given stage ID, group ID and match ID,
     * updates the score and then saves the file back to the same location
     *
     * @param string $competition_data_dir The directory to load the competition file from
     * @param string $competition_file The name of the competition file
     * @param string $stage_id The ID of the stage containing the match to update
     * @param string $group_id The ID of the group containing the match to update
     * @param string $match_id The ID of the match to update
     * @param int[] $home_team_scores The new home team scores, with a continuous-score match having an array of length 1
     * @param int[] $away_team_scores The new home team scores, with a continuous-score match having an array of length 1
     *
     * @throws Exception if the file starts as invalid or the new scores are invalid, e.g. more than one value for a continuous match
     * @throws OutOfBoundsException if the stage, group or match cannot be found
     *
     * @return bool whether the results for the match were updated in the competition file
     */
    public static function updateMatchResults(string $competition_data_dir, string $competition_file, string $stage_id, string $group_id, string $match_id, array $home_team_scores, array $away_team_scores, ?bool $complete) : bool
    {
        $score_length = count($home_team_scores);
        if ($score_length !== count($away_team_scores)) {
            throw new Exception('Invalid results: score lengths are different');
        }

        for ($i = 0; $i < $score_length; $i++) {
            if (! is_int($home_team_scores[$i])) {
                throw new Exception('Invalid results: found a non-integer home team score value');
            }
            if (! is_int($away_team_scores[$i])) {
                throw new Exception('Invalid results: found a non-integer away team score value');
            }
        }

        $competition_data = json_decode(file_get_contents(realpath($competition_data_dir."/".$competition_file)));

        if ($competition_data === null) {
            throw new Exception('Document does not contain valid JSON');
        }

        Competition::validateJSON($competition_data);

        $stage = null;
        foreach ($competition_data->stages as $this_stage) {
            if ($this_stage->id === $stage_id) {
                $stage = $this_stage;
                break;
            }
        }

        if (is_null($stage)) {
            throw new OutOfBoundsException('Stage with ID '.$stage_id.' not found');
        }

        $group = null;
        foreach ($stage->groups as $this_group) {
            if ($this_group->id === $group_id) {
                $group = $this_group;
                break;
            }
        }

        if (is_null($group)) {
            throw new OutOfBoundsException('Group with ID '.$group_id.' not found');
        }

        $match = null;
        foreach ($group->matches as $this_match) {
            if ($this_match->type === 'match' && $this_match->id === $match_id) {
                $match = $this_match;
                break;
            }
        }

        if (is_null($match)) {
            throw new OutOfBoundsException('Match with ID '.$match_id.' not found');
        }

        if ($group->matchType === 'continuous') {
            GroupMatch::assertContinuousScoresValid($home_team_scores, $away_team_scores, $group);
            if ($complete === null) {
                throw new Exception('Invalid results: match type is continuous, but the match completeness is not set');
            }
            $match->complete = $complete;
        } else {
            GroupMatch::assertSetScoresValid($home_team_scores, $away_team_scores, new SetConfig($group->sets));
            if (property_exists($match, 'duration') && $complete === null) {
                throw new Exception('Invalid results: match type is sets and match has a duration, but the match completeness is not set');
            }
            if ($complete !== null) {
                $match->complete = $complete;
            }
        }

        $match->homeTeam->scores = $home_team_scores;
        $match->awayTeam->scores = $away_team_scores;

        Competition::validateJSON($competition_data);

        return file_put_contents(realpath($competition_data_dir)."/".$competition_file, json_encode($competition_data, JSON_PRETTY_PRINT), LOCK_EX);
    }

    /**
     * Performs schema validation on the JSON data
     *
     * @param mixed $competition_data The object representation of the parsed JSON data
     *
     * @throws Exception An exception containing a list of schema validation errors
     */
    private static function validateJSON(mixed $competition_data) : void
    {
        $validator = new Validator();

        $validator->setMaxErrors(5);
        $validator->resolver()->registerFile(
            'https://github.com/monkeysppp/VBCompetitions-schema/tree/1.0.0',
            realpath(__DIR__.'/../schema/competition.json')
        );

        $result = $validator->validate($competition_data, 'https://github.com/monkeysppp/VBCompetitions-schema/tree/1.0.0');

        if ($result->isValid()) {
            return;
        } else {
            $errors = '';
            foreach ((new ErrorFormatter())->formatOutput($result->error(), "basic")['errors'] as $error) {
                $errors .= sprintf("[%s] [%s] %s".PHP_EOL, $error['keywordLocation'], $error['instanceLocation'], $error['error']);
            }
            throw new Exception('Competition data failed schema validation:\n'.$errors);
        }
    }

    /**
     * Validates a team ID, throwing an exception if it isn't and returning if the team Id is valid
     *
     * @param string $team_id The team ID to check.  This may be a team ID, a team reference or a ternary
     * @param string $match_id The match that the team is a part of (for the exception message)
     * @param string $field The field the team is active in, e.g. "homeTeam", "officials > team" (for the exception message)
     *
     * @throws Exception An exception stating that the team ID is invalid
     */
    public function validateTeamID(string $team_id, string $match_id, string $field)
    {
        // If it looks like a team ID
        if (strncmp($team_id, '{', 1) !== 0) {
            try {
                $this->validateTeamExists($team_id);
            } catch (Throwable $th) {
                throw new Exception('Invalid team ID for '.$field.' in match with ID "'.$match_id.'"', 0, $th);
            }
        // If it looks like a ternary
        } elseif (preg_match('/^([^=]*)==([^?]*)\?(.*)$/', $team_id, $lr_matches)) {
            // Check the "left" part is a team reference
            try {
                $this->validateTeamReference($lr_matches[1]);
            } catch (Throwable $th) {
                throw new Exception('Invalid ternary left part reference for '.$field.' in match with ID "'.$match_id.'": "'.$lr_matches[1].'"', 0, $th);
            }

            // Check the "right" part is a team reference
            try {
                $this->validateTeamReference($lr_matches[2]);
            } catch (Throwable $th) {
                throw new Exception('Invalid ternary right part reference for '.$field.' in match with ID "'.$match_id.'": "'.$lr_matches[2].'"', 0, $th);
            }

            if (preg_match('/^({[^}]*}):(.*)$/', $lr_matches[3], $tf_matches)) {
                // If "true" team is a reference...
                try {
                    $this->validateTeamReference($tf_matches[1]);
                } catch (Throwable $th) {
                    throw new Exception('Invalid ternary true team reference for '.$field.' in match with ID "'.$match_id.'": "'.$tf_matches[1].'"', 0, $th);
                }

                try {
                    if (strncmp($tf_matches[2], '{', 1) !== 0) {
                        $this->validateTeamExists($tf_matches[2]);
                    } else {
                        $this->validateTeamReference($tf_matches[2]);
                    }
                } catch (Throwable $th) {
                    throw new Exception('Invalid ternary false team reference for '.$field.' in match with ID "'.$match_id.'": "'.$tf_matches[2].'"', 0, $th);
                }
            } elseif (preg_match('/^([^:]*):(.*)$/', $lr_matches[3], $tf_matches)) {
                try {
                    $this->validateTeamExists($tf_matches[1]);
                } catch (Throwable $th) {
                    throw new Exception('Invalid ternary true team reference for '.$field.' in match with ID "'.$match_id.'": "'.$tf_matches[1].'"', 0, $th);
                }

                try {
                    if (strncmp($tf_matches[2], '{', 1) !== 0) {
                        $this->validateTeamExists($tf_matches[2]);
                    } else {
                        $this->validateTeamReference($tf_matches[2]);
                    }
                } catch (Throwable $th) {
                    throw new Exception('Invalid ternary false team reference for '.$field.' in match with ID "'.$match_id.'": "'.$tf_matches[2].'"', 0, $th);
                }
            }
        // If it looks like an invalid team ref
        } elseif (preg_match('/^{[^}]*$/', $team_id)) {
            throw new Exception('Invalid team reference for '.$field.' in match with ID "'.$match_id.'": "'.$team_id.'"');
        // It must be a team reference
        } else {
            $this->validateTeamReference($team_id);
        }
    }

    /**
     * Takes in an exact, resolved team ID and checks that the team exists
     *
     * @param string $team_id The team ID to check.  This must be a resolved team ID, not a team reference or a ternary
     *
     * @throws Exception An exception if the team does not exist
     */
    private function validateTeamExists(string $team_id)
    {
        if (!$this->teamIdExists($team_id)) {
            throw new Exception('Team with ID "'.$team_id.'" does not exist');
        }
    }

    /**
     * Takes in a team reference and validates that it is valid.  This performs the following checks
     * <ul>
     * <li>Is the reference syntax valid</li>
     * <li>Does the referenced Stage exist</li>
     * <li>Does the referenced Group exist</li>
     * <li>For a League position reference, is the position a valid integer</li>
     * <li>For a League position reference in a <em>completed</em> League, is the position in the bounds of the number of teams</li>
     * <li>Does the referenced Match exist</li>
     * <li>For a Match winner/loser, is the result reference valid</li>
     * </ul>
     *
     * @param string $team_ref The team reference to check
     *
     * @throws Exception An exception if the team reference is invalid
     */
    private function validateTeamReference(string $team_ref)
    {
        if (preg_match('/^{([^:]*):([^:]*):([^:]*):(.*)}$/', $team_ref, $parts)) {
            $group = null;
            try {
                $stage = $this->getStageById($parts[1]);
            } catch (Throwable $_) {
                throw new Exception('Invalid Stage part: Stage with ID "'.$parts[1].'" does not exist');
            }

            try {
                $group = $stage->getGroupById($parts[2]);
            } catch (Throwable $_) {
                throw new Exception('Invalid Group part: Group with ID "'.$parts[2].'" does not exist in stage with ID "'.$parts[1].'"');
            }

            if ($parts[3] === 'league') {
                if ($parts[4] !== (string)(int)$parts[4]) {
                    throw new Exception('Invalid League position: reference must be an integer');
                }
                if ((int)$parts[4] < 1) {
                    throw new Exception('Invalid League position: reference must be a positive integer');
                }
                if ($group->isComplete()) {
                    $teams_in_group = $group->getTeamIDs(VBC_TEAMS_KNOWN);
                    if (count($teams_in_group) < $parts[4]) {
                        throw new Exception('Invalid League position: position is bigger than the number of teams');
                    }
                }
            } else {
                try {
                    $group->getMatchById($parts[3]);
                } catch (Throwable $th) {
                    throw new Exception('Invalid Match part in reference '.$team_ref.' : Match with ID "'.$parts[3].'" does not exist in stage:group with IDs "'.$parts[1].':'.$parts[2].'"');
                }
                if ($parts[4] !== 'winner' && $parts[4] !== 'loser') {
                    throw new Exception('Invalid Match result in reference '.$team_ref.': reference must be one of "winner"|"loser" in stage:group:match with IDs "'.$parts[1].':'.$parts[2].':'.$parts[3].'"');
                }
            }
        } else {
            throw new Exception('Invalid team reference format "'.$team_ref.'", must be "{STAGE-ID:GROUP-ID:TYPE-INDICATOR:ENTITY-INDICATOR}"');
        }
    }

    /**
     * Checks whether the given team ID is in the list of teams for this competition
     *
     * @param string $team_id The team ID (or a team reference) to look up
     *
     * @return bool Whether a team with the given ID exists
     */
    public function teamIdExists(string $team_id) : bool
    {
        return property_exists($this->team_lookup, $team_id);
    }

    /**
     * Add a team reference to the team lookup table, for later lookup in resolving team ids
     *
     * @param string $key the team reference key (without braces)
     * @param CompetitionTeam $team the resolved team for the reference
     *
     * @throws Exception thrown when the key already exists and the team is different from the existing entry
     */
    public function addTeamReference(string $key, CompetitionTeam $team) : void
    {
        if (isset($this->team_lookup->{'{'.$key.'}'}) && $this->team_lookup->{'{'.$key.'}'}->getID() !== $team->getID()) {
            throw new Exception('Key mismatch in team lookup table.  Key '.$key.' currently set to team with ID '.$this->team_lookup->{'{'.$key.'}'}->getID().', call tried to set to team with ID '.$team->getID());
        }
        $this->team_lookup->{'{'.$key.'}'} = $team;
    }

    /**
     * Check whether all stages are complete, i.e. all matches in all stages have results
     * and the competition results can be fully calculated
     *
     * @return bool whether the competition is complete or not
     */
    public function isComplete() : bool
    {
        foreach ($this->stages as $stage) {
            if (!$stage->isComplete()) {
                return false;
            }
        }

        return true;
    }
}
