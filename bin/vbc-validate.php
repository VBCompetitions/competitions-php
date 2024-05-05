#!/usr/bin/env php
<?php

namespace VBCompetitions\Competitions;

include $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

use Throwable;

if ($argc != 2) {
    echo 'Usage: vbc-validate [competition JSON file]'.PHP_EOL;
    exit(1);
}

try {
    $path_info = pathinfo($argv[1]);
    $competition = Competition::loadFromFile($path_info['dirname'], $path_info['basename']);
    echo 'File is valid'.PHP_EOL;
} catch (Throwable $th) {
    echo PHP_EOL.'Errors found in file:'.PHP_EOL.PHP_EOL;
    echo $th->getMessage().PHP_EOL;
    if (!is_null($th->getPrevious())) {
        echo $th->getPrevious()->getMessage().PHP_EOL;
    }
}
