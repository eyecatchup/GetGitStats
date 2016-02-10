<?php
/**
 * Created by PhpStorm.
 * User: stephan.schmitz
 * Date: 10.02.2016
 * Time: 12:15
 */

// Composer autoloading (run composer install in the package root first!).
require_once realpath(__DIR__ . '/../vendor/autoload.php');

use Eyecatchup\GetGitStats as GitStats;

try {
    $localRepoPath = realpath('c:/PhpstormProjects/cobra-aw/cobra-aw');

    $logSince = "1 Jan, 2016";

    $gitstats = new GitStats($localRepoPath, $logSince);

    if (false !== $gitstats) {
        printf("##### Commits seit %s in %s" . PHP_EOL . PHP_EOL, $logSince, $localRepoPath);

        #print $gitstats->renderHtmlCommitsyByUser() . PHP_EOL;
        print $gitstats->renderMarkdownCommitsByUser() . PHP_EOL;
        #print $gitstats->renderMarkdownCommitsByDate() . PHP_EOL;
        #print $gitstats->renderMarkdownCommitsByWeekday() . PHP_EOL;
    }
}
catch(\Exception $e) {
    print $e->getMessage();
}
