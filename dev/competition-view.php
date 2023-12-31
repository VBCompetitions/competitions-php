<html>
  <head>
    <title>VBCompetitions-php example competition view</title>
    <style>
        th {
            border: 1px solid #444444;
            border-radius: 3px;
            background-color: #aaaaaa;
            padding: 1px 3px 1px 3px;
        }

        td {
            border-radius: 3px;
            /* background-color: #dddddd; */
            padding: 1px 3px 1px 3px;
        }

        td[class*="vbc-"] {
            background-color: #dddddd;
        }

        td.vbc-league-table-num {
            text-align: center;
        }

        td.vbc-this-team {
            border: 2px solid #7777dd;
            background-color: #ccccdd;
        }

        td.vbc-match-score {
            background-color: #bbbbbb;
            text-align: center;
        }

        td.vbc-match-winner {
            background-color: #99dd99;
            text-align: center;
        }

        td.vbc-match-loser {
            background-color: #cccccc;
            text-align: center;
        }
    </style>
  </head>
  <body>
    <h1>View a league</h1>
    <a href="./index.php">Index</a>
    <h2>Competition List</h2>
    <p>Pick a competition</p>
    <ul>
<?php

require_once(__DIR__.'/../vendor/autoload.php');

use VBCompetitions\Competitions\Competition;
use VBCompetitions\Competitions\League;
use VBCompetitions\Competitions\GroupType;
use VBCompetitions\Competitions\HTML;

// use VBCompetitions\Competitions\MatchType;

$dataDir = realpath(__DIR__.'/data');

$competitionList = Competition::competitionList($dataDir);

foreach ($competitionList as $competition) {
    if ($competition->is_valid) {
        echo '<li><a href="./competition-view.php?file='.$competition->file.'">'.$competition->name.($competition->is_complete?' [Complete] ':' [In Progress] ').'</a></li>';
    }
}

?>
    </ul>
<?php

if (!array_key_exists('file', $_GET)) {
    echo "  </body>\n";
    echo "</html>";
    return;
}

$filename = $_GET['file'];
$filename = str_replace('../', '', $filename);
$filename = str_replace('..\\', '', $filename);
$path_info = pathinfo($dataDir.'/'.$filename);

if (realpath($path_info['dirname']) !== $dataDir) {
    # Someone has tried path traversal in the file parameter
    echo "  </body>\n";
    echo "</html>";
    return;
}

try {
    $competition = new Competition($dataDir, $filename);
} catch (\Throwable $th) {
    echo "    <p>Failed to load data</p>".PHP_EOL;
    echo "    <p>".str_replace('\n', '<br>', $th->getMessage())."</p>\n";
    echo "  </body>\n";
    echo "</html>";
    return;
}

$competition_name = $competition->getName();
if ($competition_name == null) {
    $competition_name = substr($filename, 0, -5);
}
?>
    <h2><?php echo $competition->getName() ?></h2>
    <p>Pick a stage</p>
    <ul>
<?php
foreach ($competition->getStages() as $stage) {
    echo '      <li><a href="./competition-view.php?file='.$competition->getFilename().'&stage='.$stage->getID().'">'.$stage->getName().'</a></li>';
}
?>
    </ul>
<?php

if (!array_key_exists('stage', $_GET)) {
    echo "  </body>";
    echo "</html>";
    return;
}

$stage_id = $_GET['stage'];
$stage = $competition->getStageByID($stage_id);
$stage_name = $stage->getName();
if ($stage_name == null) {
    $stage_name = $stage_i;
}
?>
    <h2><?php echo $stage->getName() ?></h2>
    <p>Pick a group</p>
    <ul>
<?php
foreach ($stage->getGroups() as $group) {
    echo '      <li><a href="./competition-view.php?file='.$competition->getFilename().'&stage='.$stage_id.'&group='.$group->getID().'">'.$group->getName().'</a></li>';
}
?>
    </ul>
<?php

if (!array_key_exists('group', $_GET)) {
    echo "  </body>";
    echo "</html>";
    return;
}

$group_id = $_GET['group'];
$group = $stage->getGroupByID($group_id);

$groupType = 'unknown';
switch ($group->getType()) {
    case GroupType::LEAGUE:
        $groupType = 'league';
        break;
    case GroupType::CROSSOVER:
        $groupType = 'crossover';
        break;
    case GroupType::KNOCKOUT:
        $groupType = 'knockout';
        break;

    default:
        # code...
        break;
}
?>
    <p>Group type:<?php echo $groupType ?></p>
<?php

if ($group instanceof League) {
    echo '<h3>Table</h3>'.HTML::getLeagueTableHTML($group);
}

echo '<h3>Matches</h3>'.HTML::getMatchesHTML($group);

?>
  </body>
</html>
