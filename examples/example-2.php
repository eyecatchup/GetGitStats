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
    $localPaths = [
            realpath('c:/PhpstormProjects/cobra-aw/cobra-aw'),
            realpath('c:/PhpstormProjects/cobra-aw/cobra-aw/htdocs/typo3conf/ext/eft'),
            realpath('c:/PhpstormProjects/cobra-aw/cobra-aw/htdocs/typo3conf/ext/shop'),
            realpath('c:/PhpstormProjects/cobra-aw/cobra-aw/htdocs/typo3conf/ext/cobra_aw_template'),
            realpath('c:/PhpstormProjects/cobra-aw/cobra-aw/htdocs/typo3conf/ext/congstar_base'),
            realpath('c:/PhpstormProjects/cobra-aw/cobra-aw/htdocs/typo3conf/ext/congstar_configuration')
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
