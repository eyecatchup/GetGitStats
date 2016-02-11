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
        'path' => [
            realpath('C:\xampp\htdocs\cobra_aw_template')
        ],
        'log_since' => '1 Jan, 2016',
        #'log_until' => '2 Jan, 2016',
        #'log_author' => 'Stephan Schmitz'
    ];

    #$gitstats = new GitStats($opts);
    $gitstats = new GitStats;

    $gitstats->configure($opts);

    #$gitstats->setOutput(new CSVOutput);
    #$gitstats->setOutput(new HTMLOutput);
    #$gitstats->setOutput(new JsonOutput);
    #$gitstats->setOutput(new MarkdownOutput);

    printf("##### Commits seit %s in %s" . PHP_EOL . PHP_EOL, $opts['since'], $opts['path'][0]);

    print $gitstats->getCommitsByAuthor() . PHP_EOL;
    #print $gitstats->getCommitsByDate() . PHP_EOL;
    #print $gitstats->getCommitsByWeekday() . PHP_EOL;
}
catch(\Exception $e) {
    print $e->getMessage();
}
