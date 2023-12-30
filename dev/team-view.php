<html>
  <head>
    <title>VBCompetitions-php example team view</title>
  </head>
  <body>
    <h1>See a team view</h1>
    <a href="./index.php">Index</a>
    <h2>Competition List</h2>
    <p>Pick a list</p>
    <ul>
      <?php
require(__DIR__.'/../vendor/autoload.php');
use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\Knockout;
use VBCompetitions\Competitions\KnockoutUtils;
use VBCompetitions\Competitions\League;
use VBCompetitions\Competitions\HTML;
use VBCompetitions\Competitions\MatchUtils;

$dataDir = realpath(__DIR__.'/data');

$competitionList = Competition::competitionList($dataDir);

foreach ($competitionList as $competition) {
    if ($competition->is_valid) {
        echo '<li><a href="./team-view.php?file='.$competition->file.'">'.$competition->name.($competition->is_complete?' [Complete] ':' [In Progress] ').'</a></li>';
    }
}

?>
    </ul>
<?php

if (!array_key_exists('file', $_GET)) {
    echo '  </body>';
    echo '</html>';
    return;
}

$filename = $_GET['file'];
$filename = str_replace('../', '', $filename);
$filename = str_replace('..\\', '', $filename);
$path_info = pathinfo($dataDir.'/'.$filename);

if (realpath($path_info['dirname']) !== $dataDir) {
    # Someone has tried path traversal in the file parameter
    echo '  </body>';
    echo '</html>';
    return;
}

try {
    $competition = new Competition($dataDir, $filename);
} catch (\Throwable $th) {
    echo '    <p>Failed to load data</p>';
    echo '    <p>'.$th->getMessage().'</p>';
    echo '  </body>';
    echo '</html>';
    return;
}

?>
    <h2><?php echo $competition->getName() ?></h2>
    <p>Pick a team</p>
    <ul>
<?php
foreach ($competition->getTeams() as $team) {
    echo '<li><a href="./team-view.php?file='.$competition->getFilename().'&team='.$team->getID().'">'.$team->getName().'</a></li>';
}
?>
    </ul>
<?php

if (!array_key_exists('team', $_GET)) {
    echo '  </body>';
    echo '</html>';
    return;
}

$team_id = $_GET['team'];
if (!$competition->teamIDExists($team_id)) {
    echo '  </body>';
    echo '</html>';
    return;
}

echo '    <h2>'.$competition->getTeamByID($team_id)->getName().'</h2>';

foreach ($competition->getStages() as $stage) {
    if ($stage->teamHasMatches($team_id)) {
        $group = $stage->getGroups()[0];
        foreach ($stage->getGroups() as $this_group) {
            if ($this_group->teamHasMatches($team_id)) {
                $group = $this_group;
                break;
            }
        }
        echo '    <h3>'.$stage->getName().'/'.$group->getName().'</h3>';
        if ($group instanceof League) {
            echo '<h3>Table</h3>'.HTML::getLeagueTableHTML($group, null, $team_id);
        } elseif ($group instanceof Knockout && !is_null($group->getKnockoutConfig())) {
            echo '<h3>Final standing</h3>'.HTML::getFinalStandingHTML($group, $team_id);
        }
        echo '<h3>Matches</h3>'.HTML::getMatchesHTML($group, null, $team_id, VBC_MATCH_ALL_IN_GROUP | VBC_MATCH_OFFICIATING);
    } elseif ($stage->teamHasOfficiating($team_id)) {
        echo '    <h3>'.$stage->getName().'/'.$group->getName().'</h3>';
        foreach ($stage->getGroups() as $group) {
            echo '<h3>Matches</h3>'.HTML::getMatchesHTML($group, null, $team_id, VBC_MATCH_ALL_IN_GROUP | VBC_MATCH_OFFICIATING);
        }
    } elseif (!$stage->isComplete() && $stage->teamMayHaveMatches($team_id) && ! is_null($stage->getIfUnknown())) {
        echo '    <h3>'.$stage->getName().'/'.$group->getName().'</h3>';
        foreach($stage->getIfUnknown()->getDescription() as $description) {
            $body .= '<p>'.$description.'</p>';
        };
        echo '<h3>Matches</h3>'.HTML::getMatchesHTML($stage->getIfUnknown());
    }
}
?>
  </body>
</html>
