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
    $gitstats = new GitStats;

    $opts = [
        'path' => [
             realpath('C:\xampp\htdocs\repo_a')
        ],
        'log_author' => 'Stephan Schmitz'
    ];

    $gitstats->parse($opts);

    #$gitstats->setOutput(new CSVOutput);
    #$gitstats->setOutput(new HTMLOutput);
    #$gitstats->setOutput(new JsonOutput);
    #$gitstats->setOutput(new MarkdownOutput);

    printf("##### Commits since %s in %s" . PHP_EOL . PHP_EOL, $opts['since'], $opts['path'][0]);

    print $gitstats->getCommitsByAuthor() . PHP_EOL;
    #print $gitstats->getCommitsByDate() . PHP_EOL;
    #print $gitstats->getCommitsByWeekday() . PHP_EOL;

    $newOpts = [
        'path' => [
             realpath('C:\xampp\htdocs\repo_b')
        ],
        'log_since' => '1 Jan, 2016',
        'log_until' => '31 Jan, 2016'
    ];

    $gitstats->parse($newOpts);

    #$gitstats->setOutput(new CSVOutput);
    #$gitstats->setOutput(new HTMLOutput);
    #$gitstats->setOutput(new JsonOutput);
    #$gitstats->setOutput(new MarkdownOutput);

    printf("##### Commits since %s in %s" . PHP_EOL . PHP_EOL, $newOpts['since'], $newOpts['path'][0]);

    print $gitstats->getCommitsByAuthor() . PHP_EOL;
    #print $gitstats->getCommitsByDate() . PHP_EOL;
    #print $gitstats->getCommitsByWeekday() . PHP_EOL;
}
catch(\Exception $e) {
    print $e->getMessage();
}
