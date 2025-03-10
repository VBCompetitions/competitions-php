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
    /** @var string The version of schema that the document conforms to. Defaults to 1.0.0 */
    private string $version = '1.0.0';

    /** @var array A list of key-value pairs representing metadata about the competition, where each key must be unique. This can be used for functionality such as associating a competition with a season, and searching for competitions with matching metadata */
    private array $metadata = [];

    /** @var string A name for the competition */
    private string $name;

    /** @var array The contacts for the Competition */
    private array $contacts = [];


    /** @var ?string Free form string to add notes about the competition.  This can be used for arbitrary content that various implementations can use */
    private ?string $notes = null;

    /** @var array  A list of clubs that the teams are in */
    private array $clubs = [];

    /** @var array The list of all teams in this competition */
    private array $teams = [];

    /** @var array A list of players in the competition */
    private array $players = [];

    /**
     * @var array The stages of the competition. Stages are phases of a competition that happen in order.  There may be only one stage (e.g. for a flat league) or multiple in sequence
     * (e.g. for a tournament with pools, then crossovers, then finals)
     */
    private array $stages = [];

    /** @var object A Lookup table from team IDs (including references) to the team */
    private object $team_lookup;

    /** @var object A Lookup table from player IDs to the player */
    private object $player_lookup;


    /** @var object A Lookup table from stage IDs to the stage */
    private object $stage_lookup;

    /** @var object A Lookup table from club IDs to the club */
    private object $club_lookup;

    /** @var object A Lookup table from contact IDs to the contact */
    private object $contact_lookup;


    /** @var CompetitionTeam The "unknown" team, typically for matching against */
    private CompetitionTeam $unknown_team;

    /**
     * Takes in the Competition name creates an empty Competition object with that name
     *
     * @param string $name The name of the competition
     *
     * @throws Exception thrown when the name is invalid
     */
    function __construct(string $name)
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid competition name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;

        $this->team_lookup = new stdClass();
        $this->player_lookup = new stdClass();
        $this->stage_lookup = new stdClass();
        $this->club_lookup = new stdClass();
        $this->contact_lookup = new stdClass();

        $this->unknown_team = new CompetitionTeam($this, CompetitionTeam::UNKNOWN_TEAM_ID, CompetitionTeam::UNKNOWN_TEAM_NAME);
    }

    /**
     * Loads a competition JSON file and parses its content, then instantiates a Competition object
     *
     * @param string $competition_data_dir The directory to load the competition file from
     * @param string $competition_file The name of the competition file
     *
     * @throws Exception thrown when the competition data is invalid
     *
     * @return Competition
     */
    public static function loadFromFile(string $competition_data_dir, string $competition_file) : Competition
    {
        $competition_json = file_get_contents(realpath($competition_data_dir.DIRECTORY_SEPARATOR.$competition_file));
        if ($competition_json === false) {
            throw new Exception('Failed to load file');
        }
        return Competition::loadFromCompetitionJSON($competition_json);
    }

    /**
     * Loads a Competition object from competition JSON data
     *
     * @param string $competition_json The competition JSON data
     *
     * @throws Exception thrown when the JSON data is invalid
     *
     * @return Competition
     */
    public static function loadFromCompetitionJSON(string $competition_json) : Competition
    {
        $competition_data = json_decode($competition_json);

        if ($competition_data === null) {
            throw new Exception('Document does not contain valid JSON');
        }

        if (property_exists($competition_data, 'version')) {
            // This supports only version 1.0.0 (and all documents without an explicit version are assumed to be at version 1.0.0)
            if (version_compare($competition_data->version, '1.0.0', 'ne')) {
                throw new Exception('Document version '.$competition_data->version.' not supported');
            }
        }

        Competition::validateJSON($competition_data);

        $competition = new Competition($competition_data->name);
        $competition->setVersion($competition_data->version);

        if (property_exists($competition_data, 'metadata')) {
            foreach ($competition_data->metadata as $kv) {
                if ($competition->hasMetadataByKey($kv->key)) {
                    throw new Exception('Metadata with key "'.$kv->key.'" already exists in the competition');
                }
                $competition->setMetadataByKey($kv->key, $kv->value);
            }
        }

        if (property_exists($competition_data, 'contacts')) {
            foreach ($competition_data->contacts as $contact_data) {
                $competition->addContact((new CompetitionContact($competition, $contact_data->id, $contact_data->roles))->loadFromData($contact_data));
            }
        }

        if (property_exists($competition_data, 'notes')) {
            $competition->setNotes($competition_data->notes);
        }

        if (property_exists($competition_data, 'clubs')) {
            foreach ($competition_data->clubs as $club_data) {
                $competition->addClub((new Club($competition, $club_data->id, $club_data->name))->loadFromData($club_data));
            }
        }

        foreach ($competition_data->teams as $team_data) {
            $competition->addTeam((new CompetitionTeam($competition, $team_data->id, $team_data->name))->loadFromData($team_data));
        }

        if (property_exists($competition_data, 'players')) {
            foreach ($competition_data->players as $player_data) {
                $competition->addPlayer((new Player($competition, $player_data->id, $player_data->name))->loadFromData($player_data));
            }
        }


        foreach ($competition_data->stages as $stage_data) {
            $stage = new Stage($competition, $stage_data->id);
            $competition->addStage($stage);
            $stage->loadFromData($stage_data);
        }

        return $competition;
    }

    /**
     * Save the whole Competition as a competition JSON file
     *
     * @param string $competition_data_dir The directory to save the competition file to
     * @param string $competition_file The name of the competition file
     *
     * @throws Exception thrown when the competition file cannot be saved
     *
     * @return Competition the competition
     */
    public function saveToFile(string $competition_data_dir, string $competition_file) : Competition
    {
        // We could set JSON_PRETTY_PRINT but that bloats the file on disk, and it's really a presentation thing,
        // so let the viewer or human prettify the JSON if they way
        $competition_data = json_encode($this);
        Competition::validateJSON(json_decode($competition_data));
        file_put_contents(realpath($competition_data_dir)."/".$competition_file, $competition_data, LOCK_EX);
        return $this;
    }

    /**
     * Serialize the competition data into JSON format
     *
     * @return mixed The JSON representation of the competition data
     */
    public function jsonSerialize() : mixed
    {
        $competition = new stdClass();

        $competition->version = $this->version;

        if (count($this->metadata) > 0) {
            $competition->metadata = $this->metadata;
        }

        $competition->name = $this->name;

        if ($this->notes !== null) {
            $competition->notes = $this->notes;
        }

        if (count($this->clubs) > 0) {
            $competition->clubs = $this->clubs;
        }

        $competition->teams = $this->teams;

        if (count($this->players) > 0) {
            $competition->players = $this->players;
        }

        $competition->stages = $this->stages;

        return $competition;
    }

    /**
     * Process matches for all stages in the competition
     *
     * @return void
     */
    private function processMatches() : void
    {
        foreach ($this->stages as $stage) {
            foreach ($stage->getGroups() as $group) {
                if (!$group->isProcessed()) {
                    $group->processMatches();
                }
            }
        }
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
     * Set the competition version
     *
     * @param string $version the version for the competition data
     *
     * @return Competition the competition
     */
    public function setVersion(string $version) : Competition
    {
        $this->version = $version;
        return $this;
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
     *
     * @return Competition the competition
     */
    public function setName(string $name) : Competition
    {
        if (strlen($name) > 1000 || strlen($name) < 1) {
            throw new Exception('Invalid competition name: must be between 1 and 1000 characters long');
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Add metadata to the competition.
     *
     * This function adds metadata to the competition using the provided key-value pair.
     *
     * @param string $key The key of the metadata
     * @param string $value The value of the metadata
     * @return Competition Returns the current Competition instance for method chaining
     * @throws Exception If the key or value is invalid
     */
    public function setMetadataByKey(string $key, string $value) : Competition
    {
        if (strlen($key) > 100 || strlen($key) < 1) {
            throw new Exception('Invalid metadata key: must be between 1 and 100 characters long');
        }

        if (strlen($value) > 1000 || strlen($value) < 1) {
            throw new Exception('Invalid metadata value: must be between 1 and 1000 characters long');
        }

        foreach ($this->metadata as $kv) {
            if ($kv->key === $key) {
                $kv->value = $value;
                return $this;
            }
        }

        $kv = new stdClass();
        $kv->key = $key;
        $kv->value = $value;
        array_push($this->metadata, $kv);
        return $this;
    }

    /**
     * Check if the competition has metadata with the given key.
     *
     * This function checks if the competition has metadata with the specified key.
     *
     * @param string $key The key to check
     * @return bool Returns true if the metadata value exists, false otherwise
     */
    public function hasMetadataByKey(string $key) : bool
    {
        foreach ($this->metadata as $kv) {
            if ($kv->key === $key) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the competition has any metadata defined.
     *
     * @return bool Returns true if the metadata exists, false otherwise
     */
    public function hasMetadata() : bool
    {
        return count($this->metadata) > 0;
    }

    /**
     * Get the value of metadata with the specified key.
     *
     * This function retrieves the value of the metadata associated with the provided key.
     *
     * @param string $key The key of the metadata
     * @return ?string Returns the value of the metadata if found, otherwise null
     */
    public function getMetadataByKey(string $key) : ?string
    {
        foreach ($this->metadata as $kv) {
            if ($kv->key === $key) {
                return $kv->value;
            }
        }
        return null;
    }

    /**
     * Get the whole metadata array.
     *
     * This function retrieves the value of the metadata associated with the provided key.
     *
     * @return ?array Returns the metadata array or null if no metadata is defined
     */
    public function getMetadata() : ?array
    {
        if ($this->hasMetadata()) {
            return $this->metadata;
        }
        return null;
    }

    /**
     * Delete metadata with the specified key from the competition.
     *
     * This function deletes the metadata with the provided key from the competition.
     *
     * @param string $key The key of the metadata to delete
     * @return Competition Returns the current Competition instance for method chaining
     */
    public function deleteMetadataByKey(string $key) : Competition
    {
        $this->metadata = array_values(array_filter($this->metadata, fn($el): bool => $el->key !== $key));
        return $this;
    }

    /**
     * Add a contact to this competition
     *
     * @param CompetitionContact $contact The contact to add to this competition
     *
     * @return Competition This Competition
     *
     * @throws Exception If a contact with a duplicate ID within the competition is added
     */
    public function addContact(CompetitionContact $contact) : Competition
    {
        if ($this->hasContact($contact->getID())) {
            throw new Exception('competition contacts with duplicate IDs within a competition not allowed');
        }
        array_push($this->contacts, $contact);
        $this->contact_lookup->{$contact->getID()} = $contact;
        return $this;
    }

    /**
     * Returns an array of Contacts for this competition
     *
     * @return array<CompetitionContact>|null The contacts for this competition
     */
    public function getContacts() : ?array
    {
        return $this->contacts;
    }

    /**
     * Returns the Contact with the requested ID, or throws if the ID is not found
     *
     * @param string $id The ID of the contact in this competition to return
     *
     * @throws OutOfBoundsException If a Contact with the requested ID was not found
     *
     * @return CompetitionContact The requested contact for this competition
     */
    public function getContact(string $id) : CompetitionContact
    {
        if (!property_exists($this->contact_lookup, $id)) {
            throw new OutOfBoundsException('Contact with ID "'.$id.'" not found');
        }
        return $this->contact_lookup->$id;
    }

    /**
     * Check if a contact with the given ID exists in this competition
     *
     * @param string $id The ID of the contact to check
     *
     * @return bool True if the contact exists, otherwise false
     */
    public function hasContact(string $id) : bool
    {
        return property_exists($this->contact_lookup, $id);
    }

    /**
     * Check if this competition has any contacts
     *
     * @return bool True if the competition has contacts, otherwise false
     */
    public function hasContacts() : bool
    {
        return count($this->contacts) > 0;
    }

    /**
     * Delete a contact from the competition
     *
     * @param string $id The ID of the contact to delete
     *
     * @return Competition This Competition
     */
    public function deleteContact(string $id) : Competition
    {
        if (!$this->hasContact($id)) {
            return $this;
        }

        unset($this->contact_lookup->$id);
        $this->contacts = array_values(array_filter($this->contacts, fn(CompetitionContact $el): bool => $el->getID() !== $id));
        return $this;
    }

    /**
     * Get the notes for this competition
     *
     * @return ?string the notes for this competition
     */
    public function getNotes() : ?string
    {
        return $this->notes;
    }

    /**
     * Set the notes for this competition
     *
     * @param string|null $notes the notes for this competition
     *
     * @return Competition the competition
     */
    public function setNotes(?string $notes) : Competition
    {
        if (strlen($notes) < 1) {
            throw new Exception('Invalid competition notes: must be at least 1 character long');
        }
        $this->notes = $notes;
        return $this;
    }

    /**
     * Add a new team to the competition
     *
     * @param CompetitionTeam $team The team to add to the competition
     *
     * @throws Exception If the input parameters are invalid or if a team with the requested ID already exists
     *
     * @return Competition This competition
     */
    public function addTeam(CompetitionTeam $team) : Competition
    {
        if ($team->getCompetition() !== $this) {
            throw new Exception('Team was initialised with a different Competition');
        }
        if ($this->hasTeam($team->getID())) {
            return $this;
        }
        array_push($this->teams, $team);
        $this->team_lookup->{$team->getID()} = $team;
        return $this;
    }

    /**
     * Get the teams in this competition
     *
     * @return array The teams in this competition
     */
    public function getTeams() : array
    {
        return $this->teams;
    }

    /**
     * Gets the Team for the given team ID
     *
     * @param string $id The team ID to look up. This may be a pure ID, a reference, or a ternary
     *
     * @return CompetitionTeam The team
     */
    public function getTeam(string $id) : CompetitionTeam
    {
        $this->processMatches();

        if (strncmp($id, '{', 1) !== 0) {
            if (property_exists($this->team_lookup, $id)) {
                return $this->team_lookup->$id;
            }
        }

        /*
         * Check for ternaries like {team_id_1}=={team_id_2}?{team_id_true}:{team_id_false}
         * Note that we only allow one level of ternary, i.e. this does not resolve:
         *  { {ta}=={tb}?{t_true}:{t_false} }=={T2}?{T_True}:{T_False}
         */
        if (preg_match('/^([^=]*)==([^?]*)\?(.*)$/', $id, $lr_matches)) {
            $left_team = $this->getTeam($lr_matches[1]);
            $right_team = $this->getTeam($lr_matches[2]);
            $true_team = null;
            if (preg_match('/^({[^}]*}):(.*)$/', $lr_matches[3], $tf_matches)) {
                $true_team = $this->getTeam($tf_matches[1]);
                $false_team = $this->getTeam($tf_matches[2]);
            } elseif (preg_match('/^([^:]*):(.*)$/', $lr_matches[3], $tf_matches)) {
                $true_team = $this->getTeam($tf_matches[1]);
                $false_team = $this->getTeam($tf_matches[2]);
            }
            if ($true_team !== null) {
                return $left_team == $right_team ? $true_team : $false_team;
            }
        }

        if (preg_match('/^{([^:]*):([^:]*):([^:]*):([^:]*)}$/', $id, $team_ref_parts)) {
            try {
                return $this->getStage($team_ref_parts[1])->getGroup($team_ref_parts[2])->getTeam($team_ref_parts[3], $team_ref_parts[4]);
            } catch (Throwable $th) {
                return $this->unknown_team;
            }
        }

        return $this->unknown_team;
    }

    /**
     * Check if a team with the given ID exists in the competition
     *
     * @param string $id The ID of the team to check
     *
     * @return bool True if the team exists, false otherwise
     */
    public function hasTeam(string $id) : bool
    {
        return property_exists($this->team_lookup, $id);
    }

    /**
     * Delete a team from the competition
     *
     * @param string $id The ID of the team to delete
     *
     * @return Competition This competition
     */
    public function deleteTeam(string $id) : Competition
    {
        if (!$this->hasTeam($id)) {
            return $this;
        }

        $team_matches = [];
        foreach ($this->stages as $stage) {
            $stage_matches = $stage->getMatches($id, VBC_MATCH_PLAYING | VBC_MATCH_OFFICIATING);
            $team_matches = array_merge($team_matches, $stage_matches);
        }

        if (count($team_matches) > 0) {
            $collapse_matches = fn(MatchInterface $m): string => '{'.$m->getGroup()->getStage()->getID().':'.$m->getGroup()->getID().':'.$m->getID().'}';
            throw new Exception('Team still has matches with IDs: '.join(', ', array_map($collapse_matches, $team_matches)));
        }

        // Also remove team from any club's list
        foreach ($this->clubs as $club) {
            $club->deleteTeam($id);
        }

        // Then delete the team
        unset($this->team_lookup->$id);
        $this->teams = array_values(array_filter($this->teams, fn(CompetitionTeam $team): bool => $team->getID() !== $id));

        return $this;
    }

    /**
     * Add a player to this competition
     *
     * @param Player $player The player to add to this competition
     *
     * @return Competition This Competition instance
     *
     * @throws Exception If a player with a duplicate ID within the competition is added
     */
    public function addPlayer(Player $player) : Competition
    {
        if ($player->getID() !== Player::UNREGISTERED_PLAYER_ID && $this->hasPlayer($player->getID())) {
            throw new Exception('players with duplicate IDs within a competition not allowed');
        }

        array_push($this->players, $player);
        $this->player_lookup->{$player->getID()} = $player;
        return $this;
    }

    /**
     * Get the players in this competition
     *
     * @return array<Player> The players in this competition
     */
    public function getPlayers() : array
    {
        return $this->players;
    }

    /**
     * Get the players in the team with the given ID
     *
     * @param string $id the ID of the team to get the players for
     *
     * @return array<Player> The players in the team with the given ID
     */
    public function getPlayersInTeam($id) : array
    {
        return array_values(array_filter($this->players, fn($player): bool => $player->getLatestTeamEntry() !== null && $player->getLatestTeamEntry()->getID() === $id));
    }

    /**
     * Returns the Player with the requested ID, or throws if the ID is not found
     *
     * @param string $id The ID of the player in this team to return
     *
     * @throws OutOfBoundsException If a Player with the requested ID was not found
     *
     * @return Player The requested player for this team
     */
    public function getPlayer(string $id) : Player
    {
        if (!property_exists($this->player_lookup, $id)) {
            throw new OutOfBoundsException('Player with ID "'.$id.'" not found');
        }
        return $this->player_lookup->$id;
    }

    /**
     * Check if this competition has any players
     *
     * @return bool True if the competition has players, otherwise false
     */
    public function hasPlayers() : bool
    {
        return count($this->players) > 0;
    }

    /**
     * Check if a player with the given ID exists in this competition
     *
     * @param string $id The ID of the player to check
     *
     * @return bool True if the player exists, otherwise false
     */
    public function hasPlayer(string $id) : bool
    {
        return property_exists($this->player_lookup, $id);
    }

    /**
     * Check if the team with the given ID has any players defined
     *
     * @param string $id the ID of the team to check
     *
     * @return bool True if the team with the given ID has any players defined
     */
    public function hasPlayersInTeam(string $id) {
        foreach ($this->players as $player) {
            $latest_team = $player->getLatestTeamEntry();
            if ($latest_team !== null && $latest_team->getID() === $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the player with the given ID exists in the team with the given ID
     *
     * @param string $playerID the ID of the player to check
     * @param string $teamID the ID of the team to check
     *
     * @return bool True if the player with the given ID exists in the team with the given ID
     */
    public function hasPlayerInTeam(string $playerID, string $teamID) {
        return $this->hasPlayer($playerID) && $this->getPlayer($playerID)->getLatestTeamEntry() !== null && $this->getPlayer($playerID)->getLatestTeamEntry()->getID() === $teamID;
    }

    /**
     * Delete a player from the team
     *
     * @param string $id The ID of the player to delete
     *
     * @return Competition This Competition instance
     */
    public function deletePlayer(string $id) : Competition
    {
        // TODO check if this player is in any matches
        if (!$this->hasPlayer($id)) {
            return $this;
        }

        unset($this->player_lookup->$id);
        $this->players = array_values(array_filter($this->players, fn(Player $player): bool => $player->getID() !== $id));
        return $this;
    }

    /**
     * Add a new stage to the competition
     *
     * @param Stage $stage The stage to add to the competition
     *
     * @throws Exception If a stage with the requested ID already exists
     *
     * @return Competition This competition
     */
    public function addStage(Stage $stage) : Competition
    {
        if ($stage->getCompetition() !== $this) {
            throw new Exception('Stage was initialised with a different Competition');
        }
        array_push($this->stages, $stage);
        $this->stage_lookup->{$stage->getID()} = $stage;
        return $this;
    }

    /**
     * Get the stages in this competition
     *
     * @return array The stages in this competition
     */
    public function getStages() : array
    {
        return $this->stages;
    }

    /**
     * Returns the Stage with the requested ID, or throws if the ID is not found
     *
     * @param string $id The ID of the stage to return
     *
     * @throws OutOfBoundsException When no stage with the provided ID is found
     *
     * @return Stage The requested stage
     */
    public function getStage(string $id) : Stage
    {
        if (!property_exists($this->stage_lookup, $id)) {
            throw new OutOfBoundsException('Stage with ID '.$id.' not found');
        }
        return $this->stage_lookup->$id;
    }

    /**
     * Check if a stage with the given ID exists in the competition
     *
     * @param string $id The ID of the stage to check
     *
     * @return bool True if the stage exists, false otherwise
     */
    public function hasStage(string $id) : bool
    {
        return property_exists($this->stage_lookup, $id);
    }

    /**
     * Delete a stage from the competition
     *
     * @param string $id The ID of the stage to delete
     *
     * @return Competition This competition
     */
    public function deleteStage($id) : Competition
    {
        $stage_found = false;
        foreach ($this->stages as $stage) {
            if ($stage_found) {
                foreach ($stage->getGroups() as $group) {
                    foreach ($group->getMatches() as $match) {
                        $team_references = [];
                        $team_references = array_merge($team_references, $this->stripTeamReferences($match->getHomeTeam()->getID()));
                        $team_references = array_merge($team_references, $this->stripTeamReferences($match->getAwayTeam()->getID()));

                        $officials = $match->getOfficials();
                        if ($officials !== null && $officials->isTeam()) {
                            $team_references = array_merge($team_references, $this->stripTeamReferences($match->getOfficials()->getTeamID()));
                        }

                        foreach ($team_references as $reference) {
                            if (preg_match('/^{([^:]*):.*}$/', $reference, $parts)) {
                                if ($parts[1] === $id) {
                                    throw new Exception('Cannot delete stage with id "'.$id.'" as it is referenced in match {'.$stage->getID().':'.$group->getID().':'.$match->getID().'}');
                                }
                            }
                        }
                    }
                }
            } else if ($stage->getID() === $id) {
                $stage_found = true;
            }
        }

        if (!$stage_found) {
            return $this;
        }

        unset($this->stage_lookup->$id);
        $this->stages = array_values(array_filter($this->stages, fn(Stage $el): bool => $el->getID() !== $id));

        return $this;
    }

    /**
     * Add a new club to the competition
     *
     * @param Club $club The club to add to the competition
     *
     * @throws Exception If the input parameters are invalid or if a club with the requested ID already exists
     *
     * @return Competition This competition
     */
    public function addClub(Club $club) : Competition
    {
        if ($club->getCompetition() !== $this) {
            throw new Exception('Club was initialised with a different Competition');
        }
        array_push($this->clubs, $club);
        $this->club_lookup->{$club->getID()} = $club;
        return $this;
    }

    /**
     * Get the clubs in this competition
     *
     * @return array The clubs in this competition
     */
    public function getClubs() : array
    {
        return $this->clubs;
    }

    /**
     * Returns the Club with the requested ID, or throws if the ID is not found
     *
     * @param string $id The ID of the club to return
     *
     * @throws OutOfBoundsException When no club with the provided ID is found
     *
     * @return Club The requested club
     */
    public function getClub(string $id) : Club
    {
        if (!property_exists($this->club_lookup, $id)) {
            throw new OutOfBoundsException('Club with ID "'.$id.'" not found');
        }
        return $this->club_lookup->$id;
    }

    /**
     * Check if this competition has any clubs
     *
     * @return bool True if the competition has clubs, otherwise false
     */
    public function hasClubs() : bool
    {
        return count($this->clubs) > 0;
    }

    /**
     * Check if a club with the given ID exists in the competition
     *
     * @param string $id The ID of the club to check
     *
     * @return bool True if the club exists, false otherwise
     */
    public function hasClub(string $id) : bool
    {
        return property_exists($this->club_lookup, $id);
    }

    /**
     * Delete a club from the competition
     *
     * @param string $id The ID of the club to delete
     *
     * @return Competition This competition
     */
    public function deleteClub(string $id) : Competition
    {
        if (!$this->hasClub($id)) {
            return $this;
        }

        $club = $this->getClub($id);
        $teams_in_club = $club->getTeams();
        if (count($teams_in_club) > 0) {
            throw new Exception('Club still contains teams with IDs: '.join(', ', array_map(fn(CompetitionTeam $t): string => '{'.$t->getID().'}', $teams_in_club)));
        }

        unset($this->club_lookup->$id);
        $this->clubs = array_values(array_filter($this->clubs, fn (Club $el): bool => $el->getID() !== $id));

        return $this;
    }

    /**
     * Returns an array of competitions in the form below
     * {
     *   file: (string) name of the competition file,
     *   name: (string) name of the competition,
     *   is_valid: (bool) whether the file passes validation,
     *   is_complete: (bool) whether the competition has completed (all matches are complete)
     *   metadata: (array) an array of objects containing a key field and a value field, representing the competition's metadata
     * }
     *
     * When a metadata matching object is given then each competition file is checked and only those that match all metadata search entries are included
     * in the returned list.  Each metadata search entry can be one of the following:
     *
     * - <code>{ some-key: "some-value" }</code>
     *   - matching requires the key to be defined by the competition and the value must match exactly
     * - <code>{ !some-key: "true" }</code> (note the "!" at the start of the key)
     *   - matching requires that either the key is not present in the competition metadata, or
     *     the key is present but the value does not match.
     *
     * For example, consider 3 requests:
     * - Request A: list with metadata_matches={ "season": "23/24" }
     * - Request B: list with metadata_matches={ "!archived": "true" }
     * - Request C: list with metadata_matches={ "season": "23/24", "!archived": "true" }
     *
     * <pre>
     * Competition1 = {
     *   "name": "This will be included in list A, B and C",
     *   "metadata": [
     *     { "key": "season", "value": "23/24" }
     *   ],
     *   ...
     * }
     *
     * Competition2 = {
     *   "name": "This will be included in lists A, B and C",
     *   "metadata": [
     *     { "key": "season", "value": "23/24" },
     *     { "key": "archived", "value": "false" }
     *   ],
     *   ...
     * }
     *
     * Competition3 = {
     *   "name": "This will be included in list A",
     *   "metadata": [
     *     { "key": "season", "value": "23/24" },
     *     { "key": "archived", "value": "true" }
     *   ],
     *   ...
     * }
     *
     * Competititon4 = {
     *   "name": "This has no metadata, it will be included in list B",
     *   ...
     * }
     * </pre>
     *
     * @param string $competition_data_dir The directory to scan for competition files
     * @param object|null $metadata_matches A set of key-value pairs that must all match in the competition's metadata to be included in the list
     *
     * @return array The list of competitions found in the given directory
     */
    public static function competitionList(string $competition_data_dir, ?object $metadata_matches = null) : array
    {
        if ($metadata_matches !== null) {
            foreach ($metadata_matches as $m_key => $m_value) {
                if (strlen($m_key) > 100 || strlen($m_key) < 1) {
                    throw new Exception('Invalid metadata search key "'.$m_key.'": must be between 1 and 100 characters long');
                }

                if (strlen($m_value) > 1000 || strlen($m_value) < 1) {
                    throw new Exception('Invalid metadata search value "'.$m_value.'": must be between 1 and 1000 characters long');
                }
            }
        }

        $list = [];
        $competition_file_list = scandir($competition_data_dir);
        foreach ($competition_file_list as $competition_file) {
            $real_path = realpath($competition_data_dir.DIRECTORY_SEPARATOR.$competition_file);
            if (!is_file($real_path)) {
                continue;
            }
            $path_parts = pathinfo($real_path);
            if ($path_parts['extension'] != 'json' || strlen($path_parts['filename']) < 1) {
                continue;
            }

            /* check for metadata matches */
            if ($metadata_matches !== null) {
                $competition_json = file_get_contents(realpath($competition_data_dir.DIRECTORY_SEPARATOR.$competition_file));
                $competition_data = json_decode($competition_json);

                if ($competition_data === null) {
                    continue;
                }

                $competition_metadata = new stdClass();
                if (property_exists($competition_data, 'metadata')) {
                    foreach ($competition_data->metadata as $el) {
                        $competition_metadata->{$el->key} = $el->value;
                    }
                }

                $found = true;
                foreach ($metadata_matches as $m_key => $m_value) {
                    if (str_starts_with($m_key, '!')) {
                        $n_key = substr($m_key, 1);
                        if (property_exists($competition_metadata, $n_key) && $competition_metadata->$n_key === $metadata_matches->$m_key) {
                            $found = false;
                            break;
                        }
                    } else {
                        if (!property_exists($competition_metadata, $m_key) || $competition_metadata->$m_key !== $metadata_matches->$m_key) {
                            $found = false;
                            break;
                        }
                    }
                }

                if (!$found) {
                    continue;
                }
            }

            $competition_item = new stdClass();
            try {
                $competition = Competition::loadFromFile($competition_data_dir, $competition_file);
                $competition_item->file = $competition_file;
                $competition_item->is_valid = true;
                $competition_item->is_complete = $competition->isComplete();
                $competition_item->name = $competition->name;
                if ($competition->hasMetadata()) {
                    $competition_item->metadata = $competition->getMetadata();
                }
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
     * @param int[] $home_team_scores The new home team scores
     * @param int[] $away_team_scores The new away team scores
     * @param bool|null $complete Whether the match is complete
     *
     * @throws Exception if the file starts as invalid or the new scores are invalid
     * @throws OutOfBoundsException if the stage, group, or match cannot be found
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

        if ($stage === null) {
            throw new OutOfBoundsException('Stage with ID '.$stage_id.' not found');
        }

        $group = null;
        foreach ($stage->groups as $this_group) {
            if ($this_group->id === $group_id) {
                $group = $this_group;
                break;
            }
        }

        if ($group === null) {
            throw new OutOfBoundsException('Group with ID '.$group_id.' not found');
        }

        $match = null;
        foreach ($group->matches as $this_match) {
            if ($this_match->type === 'match' && $this_match->id === $match_id) {
                $match = $this_match;
                break;
            }
        }

        if ($match === null) {
            throw new OutOfBoundsException('Match with ID '.$match_id.' not found');
        }

        if ($group->matchType === 'continuous') {
            GroupMatch::assertContinuousScoresValid($home_team_scores, $away_team_scores, $group);
            if ($complete === null) {
                throw new Exception('Invalid results: match type is continuous, but the match completeness is not set');
            }
            $match->complete = $complete;
        } else {
            $dummy_competition = new Competition('dummy for score update');
            $dummy_stage = new Stage($dummy_competition, $stage->id);
            $dummy_group = new Crossover($dummy_stage, $group->id, MatchType::SETS);
            GroupMatch::assertSetScoresValid($home_team_scores, $away_team_scores, (new SetConfig($dummy_group))->loadFromData($group->sets));
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
     * Perform schema validation on the JSON data
     *
     * @param mixed $competition_data The object representation of the parsed JSON data
     *
     * @throws Exception An exception containing a list of schema validation errors
     */
    public static function validateJSON(mixed $competition_data) : void
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
     * Validates a team ID, throwing an exception if it isn't and returning if the team ID is valid
     *
     * @param string $team_id The team ID to check. This may be a team ID, a team reference, or a ternary
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
     * @param string $id The team ID to check. This must be a resolved team ID, not a team reference or a ternary
     *
     * @throws Exception An exception if the team does not exist
     */
    private function validateTeamExists(string $id)
    {
        if (!$this->hasTeam($id)) {
            throw new Exception('Team with ID "'.$id.'" does not exist');
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
                $stage = $this->getStage($parts[1]);
            } catch (Throwable $_) {
                throw new Exception('Invalid Stage part: Stage with ID "'.$parts[1].'" does not exist');
            }

            try {
                $group = $stage->getGroup($parts[2]);
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
                    if (count($teams_in_group) < (int)$parts[4]) {
                        throw new Exception('Invalid League position: position is bigger than the number of teams');
                    }
                }
            } else {
                try {
                    $group->getMatch($parts[3]);
                } catch (Throwable $_) {
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
     * This function recursively extracts team references from the team ID, including
     * stripping out team references in a ternary statement
     *
     * @param string $team_reference The string containing team references.
     *
     * @return array<string> An array containing unique team references extracted from the input string.
     */
    private function stripTeamReferences(string $team_reference) : array
    {
        $references = [];
        if (strncmp($team_reference, '{', 1) !== 0) {
            return [];
        } elseif (preg_match('/^([^=]*)==([^?]*)\?(.*)$/', $team_reference, $lr_matches)) {
            $references = array_merge($references, $this->stripTeamReferences($lr_matches[1]));
            $references = array_merge($references, $this->stripTeamReferences($lr_matches[2]));
            if (preg_match('/^({[^}]*}):(.*)$/', $lr_matches[3], $tf_matches)) {
                $references = array_merge($references, $this->stripTeamReferences($tf_matches[1]));
                $references = array_merge($references, $this->stripTeamReferences($tf_matches[2]));
            }
        } else {
            array_push($references, $team_reference);
        }

        return array_unique($references);
    }

    /**
     * Check whether all stages are complete, i.e. all matches in all stages have results
     * and the competition results can be fully calculated
     *
     * @return bool Whether the competition is complete or not
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
