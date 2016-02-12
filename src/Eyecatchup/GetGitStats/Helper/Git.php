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


namespace Eyecatchup\GetGitStats\Helper;


use Eyecatchup\GetGitStats\Common\Exception as E;

class Git
{
    public static function parseGitlog($localPath = '.', $logSince = 0, $logUntil = 0, $author = 0)
    {
        $cmd = 'cd ' . $localPath . ' && git log --shortstat --no-merges';

        if (0 !== $author) {
            $cmd .= sprintf(' --author="%s"', $author);
        }

        if (0 !== $logSince) {
            $cmd .= sprintf(' --since="%s"', $logSince);
        }

        if (0 !== $logUntil) {
            $cmd .= sprintf(' --until="%s"', $logUntil);
        }

        $out = shell_exec($cmd);

        $splitAll = preg_split('/commit (\w+)\n/mu', $out, null, PREG_SPLIT_DELIM_CAPTURE);
        unset($splitAll[0]);

        $commits = [];

        for ($i = 1; $i <= sizeof($splitAll); $i++) {
            $commit = (object) [];
            $commit->author = (object) [];
            $commit->commit_date = (object) [];
            $commit->changes = (object) [];

            $commit->id = $splitAll[$i];

            $data = $splitAll[$i+1];
            $matches = [];

            preg_match('/Author: (.+?) \<(.+?)\>\n/', $data, $matches);
            $commit->author->name = !isset($matches[1]) ? 0 : $matches[1];
            $commit->author->email = !isset($matches[2]) ? 0 : $matches[2];

            preg_match('/Date:   (.+?)\n/', $data, $matches);
            $commit->commit_date->str = !isset($matches[1]) ? 0 : $matches[1];
            $commit->commit_date->day = !isset($matches[1]) ? 0 : Git::getDay($matches[1]);
            $commit->commit_date->weekday = !isset($matches[1]) ? 0 : Git::getWeekday($matches[1]);
            $commit->commit_date->timestamp = !isset($matches[1]) ? 0 : strtotime($matches[1]);

            preg_match('/(?P<files>\d+) file/', $data, $matches);
            $commit->changes->files = $matches['files'];

            preg_match('/, (?P<ins>\d+) ins/', $data, $matches);
            $commit->changes->insertions = !isset($matches['ins']) ? 0 : $matches['ins'];

            preg_match('/, (?P<del>\d+) del/', $data, $matches);
            $commit->changes->deletions = !isset($matches['del']) ? 0 : $matches['del'];

            array_push($commits, $commit);
            $i++;
        }

        return Git::reconstitute($commits);
    }

    /**
     * @param $commits
     *
     * @return object
     */
    private static function reconstitute($commits)
    {
        $out = (object) [];

        $counts = (object) [];
        $counts->commits = sizeof($commits);
        $counts->commit_authors = 0;
        $counts->commit_days = 0;
        $counts->files_changed = 0;
        $counts->insertions = 0;
        $counts->deletions = 0;

        $commitsByUser = [];
        $commitsByDate = [];
        $commitsByWeekday = [];

        foreach ($commits as $commit) {
            if (!isset($commitsByUser[$commit->author->email])) {
                $commitsByUser[$commit->author->email] = [
                    'name' => $commit->author->name,
                    'email' => $commit->author->email,
                    'commits_total' => (int) 1,
                    'files_changed_total' => (int) $commit->changes->files,
                    'insertions_total' => (int) $commit->changes->insertions,
                    'deletions_total' => (int) $commit->changes->deletions
                ];
            }
            else {
                $commitsByUser[$commit->author->email]['commits_total'] += (int) 1;
                $commitsByUser[$commit->author->email]['files_changed_total'] += (int) $commit->changes->files;
                $commitsByUser[$commit->author->email]['insertions_total'] += (int) $commit->changes->insertions;
                $commitsByUser[$commit->author->email]['deletions_total'] += (int) $commit->changes->deletions;
            }

            if (!isset($commitsByDate[$commit->commit_date->day])) {
                $commitsByDate[$commit->commit_date->day] = [
                    'commits_total' => (int) 1,
                    'files_changed_total' => (int) $commit->changes->files,
                    'insertions_total' => (int) $commit->changes->insertions,
                    'deletions_total' => (int) $commit->changes->deletions
                ];
            }
            else {
                $commitsByDate[$commit->commit_date->day]['commits_total'] += (int) 1;
                $commitsByDate[$commit->commit_date->day]['files_changed_total'] += (int) $commit->changes->files;
                $commitsByDate[$commit->commit_date->day]['insertions_total'] += (int) $commit->changes->insertions;
                $commitsByDate[$commit->commit_date->day]['deletions_total'] += (int) $commit->changes->deletions;
            }

            if (!isset($commitsByWeekday[$commit->commit_date->weekday])) {
                $commitsByWeekday[$commit->commit_date->weekday] = [
                    'commits_total' => (int) 1,
                    'files_changed_total' => (int) $commit->changes->files,
                    'insertions_total' => (int) $commit->changes->insertions,
                    'deletions_total' => (int) $commit->changes->deletions
                ];
            }
            else {
                $commitsByWeekday[$commit->commit_date->weekday]['commits_total'] += (int) 1;
                $commitsByWeekday[$commit->commit_date->weekday]['files_changed_total'] += (int) $commit->changes->files;
                $commitsByWeekday[$commit->commit_date->weekday]['insertions_total'] += (int) $commit->changes->insertions;
                $commitsByWeekday[$commit->commit_date->weekday]['deletions_total'] += (int) $commit->changes->deletions;
            }

            $counts->files_changed += (int) $commit->changes->files;
            $counts->insertions += (int) $commit->changes->insertions;
            $counts->deletions += (int) $commit->changes->deletions;
        }

        $counts->commit_authors = sizeof($commitsByUser);
        $counts->commit_days = sizeof($commitsByDate);

        $authors = [];

        foreach ($commitsByUser as $author => $data) {
            $commitsByUser[$author]['commits_percent'] = Git::getPercent($counts->commits, $commitsByUser[$author]['commits_total']);
            $commitsByUser[$author]['files_changed_percent'] = Git::getPercent($counts->files_changed, $commitsByUser[$author]['files_changed_total']);
            $commitsByUser[$author]['insertions_percent'] = Git::getPercent($counts->insertions, $commitsByUser[$author]['insertions_total']);
            $commitsByUser[$author]['deletions_percent'] = Git::getPercent($counts->deletions, $commitsByUser[$author]['deletions_total']);

            array_push($authors, $commitsByUser[$author]);
            unset($commitsByUser[$author]);
        }

        usort($authors, ['Eyecatchup\GetGitStats\Helper\Arrays', 'desc_by_commits_total']);

        $commitsByUser = [];

        foreach ($authors as $user) {
            $tmp = (object) [];
            $tmp->author = (object) [];
            $tmp->commits = (object) [];
            $tmp->changes = (object) [];
            $tmp->changes->files = (object) [];
            $tmp->changes->insertions = (object) [];
            $tmp->changes->deletions = (object) [];

            $tmp->author->name = $user['name'];
            $tmp->author->email = $user['email'];
            $tmp->commits->total = $user['commits_total'];
            $tmp->commits->percent = $user['commits_percent'];
            $tmp->changes->files->total = $user['files_changed_total'];
            $tmp->changes->files->percent = $user['files_changed_percent'];
            $tmp->changes->insertions->total = $user['insertions_total'];
            $tmp->changes->insertions->percent = $user['insertions_percent'];
            $tmp->changes->deletions->total = $user['deletions_total'];
            $tmp->changes->deletions->percent = $user['deletions_percent'];
            $tmp->changes->net = $user['insertions_total'] - $user['deletions_total'];
            $tmp->changes->ratio = Git::getInsertionsDeletionsRatio($user['insertions_total'], $user['deletions_total']);

            $commitsByUser []= $tmp;
        }

        $counts->net = $counts->insertions - $counts->deletions;
        $counts->ratio = Git::getInsertionsDeletionsRatio($counts->insertions, $counts->deletions);

        $remote = Git::getGitRemote();

        $out->meta = (object) [];
        $out->meta->repo_name = $remote->repo_name;
        $out->meta->branch_name = '';
        $out->meta->remote_name = $remote->remote_name;
        $out->meta->remote_url = $remote->remote_url;
        $out->meta->log_since = Git::getLogSince();
        $out->meta->log_until = Git::getLogUntil();
        $out->meta->log_created = Git::getLogCreated();

        $out->totals = $counts;

        $out->commits = (object) [];
        $out->commits->by_user = $commitsByUser;
        $out->commits->by_date = $commitsByDate;
        $out->commits->by_weekday = $commitsByWeekday;
        $out->commits->all = $commits;

        return $out;
    }

    /**
     * Check if the given path points to a valid Git repository.
     *
     * @param string $path The local path to a Git repository
     *
     * @return bool
     * @throws E\NoGitRepositoryInPathException
     */
    public static function isGitDir($path)
    {
        if (true === System::isDir((string) $path)) {
            if (!file_exists(realpath($path . '/.git/config'))) {
                $msg = sprintf("No '.git' directory in '%s'", $path);
                throw new E\NoGitRepositoryInPathException($msg);
            }
        }

        return true;
    }

    /**
     * Get the remote URL of a Git repository.
     *
     * @param string $path The local path to a Git repository
     *
     * @return object Repo name, remote name and remote URL
     */
    public static function getGitRemote($path = '.')
    {
        $tty = shell_exec('cd ' . $path . ' && git remote -v |grep push |awk \'{printf "%s %s", $1, $2}\' -');
        $tmp = explode(" ", $tty);
        $tmp2 = explode("/", $tmp[1]);

        return (object) [
            'repo_name' => end($tmp2),
            'remote_name' => $tmp[0],
            'remote_url' => $tmp[1]
        ];
    }

    public static function getInsertionsDeletionsRatio($insertions, $deletions)
    {
        return round(100 / ((int) $deletions / ((int) $insertions  / 100)), 2, PHP_ROUND_HALF_DOWN);
    }

    public static function getPercent($sum, $int)
    {
        return round((int) $int / ((int) $sum / 100), 2, PHP_ROUND_HALF_DOWN);
    }

    public static function getDay($datestr)
    {
        return (string) date_format(date_create($datestr), "Y-m-d");
    }

    public static function getWeekday($datestr)
    {
        return (string) date_format(date_create($datestr), "D");
    }

    /**
     * @param int $logSince
     *
     * @return int|string
     */
    public static function getLogSince($logSince = 0)
    {
        return (0 !== $logSince) ? Git::getDay($logSince) : 0;
    }

    /**
     * @param mixed $logUntil
     *
     * @return string
     */
    function getLogUntil($logUntil = 0)
    {
        $until = (0 !== $logUntil) ? $logUntil : date("d-m-Y H:I:s");

        return Git::getDay($until);
    }

    function getLogCreated()
    {
        return date("Y-m-d H:i:s");
    }
}