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
    $localPaths = [
            realpath('c:/PhpstormProjects/my-repo'),
            realpath('c:/PhpstormProjects/another-repo'),
            realpath('c:/PhpstormProjects/Eyecatchup/Seostats'),
            realpath('c:/PhpstormProjects/Eyecatchup/GetGitStats')
    ];

    $logSince = "1 Jan, 2016";

    $logAuthor = "Stephan Schmitz";

    foreach ($localPaths as $localRepoPath) {
        $gitstats = new GitStats($localRepoPath, $logSince, 0, $logAuthor);

        if (false !== $gitstats) {
            printf("<h5>Commits seit %s in %s</h5>", $logSince, $localRepoPath);

            print $gitstats->renderHtmlCommitsyByUser() . PHP_EOL;
            #print $gitstats->renderMarkdownCommitsByUser() . PHP_EOL;
            #print $gitstats->renderMarkdownCommitsByDate() . PHP_EOL;
            #print $gitstats->renderMarkdownCommitsByWeekday() . PHP_EOL;
        }
    }
}
catch(\Exception $e) {
    print $e->getMessage();
}
