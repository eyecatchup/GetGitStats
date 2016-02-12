<?php
/**
 * This file is part of Eyecatchup/GetGitStats.
 *
 * @package    GetGitStats
 * @link       https://github.com/eyecatchup/GetGitStats Project website
 * @author     Stephan Schmitz <eyecatchup@gmail.com>
 * @copyright  Copyright (C) 2016 Stephan Schmitz, https://eyecatchup.github.io/
 * @license    http://eyecatchup.mit-license.org/ MIT License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Composer autoloading (run composer update in the package root first!).
require_once realpath(__DIR__ . '/../vendor/autoload.php');

use Eyecatchup\GetGitStats\GetGitStats as GitStats;

try {
    $opts = [
        'local_path' => realpath('C:\xampp\htdocs\cobra_aw_template'),
        'log_since' => '1 Jan, 2016',
    ];

    $gitstats = new GitStats($opts);

    header('Content-Type: text/plain');

    /*$newOpts = [
        'local_path' => realpath('C:\xampp\htdocs'),
        'log_since' => '1 Jan, 2015',
        'log_until' => '15 Jan, 2015'
    ];

    $gitstats->parse($newOpts);*/

    print_r($gitstats->getRepositoryModel()); exit();

    printf("##### Commits since %s in %s" . PHP_EOL . PHP_EOL, $opts['log_since'], $opts['local_path'][0]);

    print $gitstats->getCommitsByWeekday() . PHP_EOL;
}
catch(\Exception $e) {
    print $e->getMessage();
}

