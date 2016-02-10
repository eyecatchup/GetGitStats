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


namespace Eyecatchup\GetGitStats;


class GetGitStats
{
    protected $repo;
    protected $commits;
    protected $commitsReconstituted;
    protected $logSince = 0;
    protected $logUntil = 0;
    protected $author = 0;
    protected $localPath;

    /**
     * @param string $repo_path The local path to a Git repository.
     * @param mixed $since An optional start date to limit the logs.
     * @param mixed $until An optional end date to limit the logs.
     * @param mixed $author An optional author to limit the logs.
     */
    public function __construct($repo_path = '.', $since = 0, $until = 0, $author = 0)
    {
        $this->localPath = realpath($repo_path);
        $this->logSince = $since;
        $this->logUntil = $until;
        $this->author = $author;

        $this->getCommitsFromLocalRepo();
    }

    /**
     * Helper function for PHP's usort to compare two extension keys by their alphabetical order.
     *
     * @param array $arr1 An array item.
     * @param array $arr2 Another array item.
     * @return integer Returns an integer representing the sorting weight.
     */
    private static function desc_by_commits_total($arr1, $arr2)
    {
        if ($arr1['commits_total'] == $arr2['commits_total']) {
            return 0;
        }

        return ($arr1['commits_total'] > $arr2['commits_total']) ? -1 : +1;
    }

    protected function getCommitsFromLocalRepo()
    {
        if (!file_exists(realpath($this->localPath . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'config'))) {
            die("No git repo!");
            return false;
        }

        $cmd = 'cd ' . $this->localPath . ' && git log --shortstat --no-merges';

        if (0 !== $this->author) {
            $cmd .= sprintf(' --author="%s"', $this->author);
        }

        if (0 !== $this->logSince) {
            $cmd .= sprintf(' --since="%s"', $this->logSince);
        }

        if (0 !== $this->logUntil) {
            $cmd .= sprintf(' --until="%s"', $this->logUntil);
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
            $commit->commit_date->day = !isset($matches[1]) ? 0 : $this->getDay($matches[1]);
            $commit->commit_date->weekday = !isset($matches[1]) ? 0 : $this->getWeekday($matches[1]);
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

        $this->commits = $commits;

        return $this->reconstitute();
    }

    protected function reconstitute()
    {
        $commits = $this->commits;

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

        $commiters = [];

        foreach ($commitsByUser as $author => $data) {
            $commitsByUser[$author]['commits_percent'] = $this->getPercent($counts->commits, $commitsByUser[$author]['commits_total']);
            $commitsByUser[$author]['files_changed_percent'] = $this->getPercent($counts->files_changed, $commitsByUser[$author]['files_changed_total']);
            $commitsByUser[$author]['insertions_percent'] = $this->getPercent($counts->insertions, $commitsByUser[$author]['insertions_total']);
            $commitsByUser[$author]['deletions_percent'] = $this->getPercent($counts->deletions, $commitsByUser[$author]['deletions_total']);

            array_push($commiters, $commitsByUser[$author]);
            unset($commitsByUser[$author]);
        }

        usort($commiters, ['Eyecatchup\GetGitStats\GetGitStats', 'desc_by_commits_total']);

        $commitsByUser = [];

        foreach ($commiters as $user) {
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
            $tmp->changes->ratio = $this->getInsertionsDeletionsRatio($user['insertions_total'], $user['deletions_total']);

            $commitsByUser []= $tmp;
        }

        $counts->net = $counts->insertions - $counts->deletions;
        $counts->ratio = $this->getInsertionsDeletionsRatio($counts->insertions, $counts->deletions);

        $remote = $this->getGitRemote();

        $out->meta = (object) [];
        $out->meta->repo_name = $remote->repo_name;
        $out->meta->branch_name = '';
        $out->meta->remote_name = $remote->remote_name;
        $out->meta->remote_url = $remote->remote_url;
        $out->meta->log_since = $this->getLogSince();
        $out->meta->log_until = $this->getLogUntil();
        $out->meta->log_created = $this->getLogCreated();

        $out->totals = $counts;

        $out->commits = (object) [];
        $out->commits->by_user = $commitsByUser;
        $out->commits->by_date = $commitsByDate;
        $out->commits->by_weekday = $commitsByWeekday;
        $out->commits->all = $commits;

        $this->commits = $out;

        return true;
    }

    function renderMarkdownCommitsByDate()
    {
        $data = $this->commits;

        $out  = "| Date | Commits (total) | Files changed (total) | Insertions (total) | Deletions (total) | Lines net (Ins. - Del.) |" . PHP_EOL;
        $out .= "|------|----------------:|----------------------:|-------------------:|------------------:|------------------------:|" . PHP_EOL;

        foreach ($data->commits->by_date as $date => $data) {
            $out .= sprintf(
                "| %s | %s | %s | %s | %s | %s |" . PHP_EOL,
                $date,
                $data['commits_total'],
                $data['files_changed_total'],
                $data['insertions_total'],
                $data['deletions_total'],
                ($data['insertions_total'] - $data['deletions_total'])
            );
        }

        return $out;
    }

    function renderMarkdownCommitsByWeekday()
    {
        $data = $this->commits;

        $out  = "| Day | Commits (total) | Files changed (total) | Insertions (total) | Deletions (total) | Lines net (Ins. - Del.) |" . PHP_EOL;
        $out .= "|-----|----------------:|----------------------:|-------------------:|------------------:|------------------------:|" . PHP_EOL;

        foreach ($data->commits->by_weekday as $weekday => $data) {
            $out .= sprintf(
                "| %s | %s | %s | %s | %s | %s |" . PHP_EOL,
                $weekday,
                $data['commits_total'],
                $data['files_changed_total'],
                $data['insertions_total'],
                $data['deletions_total'],
                ($data['insertions_total'] - $data['deletions_total'])
            );
        }

        return $out;
    }

    function renderMarkdownCommitsByUser()
    {
        $data = $this->commits;

        $out  = "| Author | Commits (total) | Commits (%) | Files changed (total) | Files changed (%) | Insertions (total) | Insertions (%) | Deletions (total) | Deletions (%) | Lines net (Ins. - Del.) | Ins./Del. Ratio (1:n) |" . PHP_EOL;
        $out .= "|--------|----------------:|------------:|----------------------:|------------------:|-------------------:|---------------:|------------------:|--------------:|------------------------:|----------------------:|" . PHP_EOL;
        $out .= sprintf(
            "| **TOTAL** | **%s** | **%s** | **%s** | **%s** | **%s** | **%s** | **%s** | **%s** | **%s** | **%s** |" . PHP_EOL,
            $data->totals->commits, "100 %",
            $data->totals->files_changed, "100 %",
            $data->totals->insertions, "100 %",
            $data->totals->deletions, "100 %",
            $data->totals->net,
            '1 : ' . $data->totals->ratio
        );

        foreach ($data->commits->by_user as $commiter) {
            $out .= sprintf(
                "| %s | %s | %s | %s  | %s | %s | %s | %s | %s | %s | %s |" . PHP_EOL,
                $commiter->author->name,
                $commiter->commits->total,
                $commiter->commits->percent . " %",
                $commiter->changes->files->total,
                $commiter->changes->files->percent . " %",
                $commiter->changes->insertions->total,
                $commiter->changes->insertions->percent . " %",
                $commiter->changes->deletions->total,
                $commiter->changes->deletions->percent . " %",
                $commiter->changes->net,
                "1 : " . $commiter->changes->ratio
            );
        }

        return $out;
    }

    function renderHtmlCommitsyByUser()
    {
        $data = $this->commits;

        $out  = "<table><thead><tr><th>Author</th><th>Commits (total)</th><th>Commits (%)</th><th>Files changed (total)</th><th>Files changed (%)</th><th>Insertions (total)</th><th>Insertions (%)</th><th>Deletions (total)</th><th>Deletions (%)</th><th>Lines net (Ins. - Del.)</th><th>Ins./Del. Ratio (1:n)</th></tr></thead><tbody>" . PHP_EOL;
        $out .= sprintf(
            "<tr><td><strong>TOTAL</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td><td><strong>%s</strong></td></tr>" . PHP_EOL,
            $data->totals->commits, "100 %",
            $data->totals->files_changed, "100 %",
            $data->totals->insertions, "100 %",
            $data->totals->deletions, "100 %",
            $data->totals->net,
            '1 : ' . $data->totals->ratio
        );

        foreach ($data->commits->by_user as $commiter) {
            $out .= sprintf(
                "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>" . PHP_EOL,
                $commiter->author->name,
                $commiter->commits->total,
                $commiter->commits->percent . " %",
                $commiter->changes->files->total,
                $commiter->changes->files->percent . " %",
                $commiter->changes->insertions->total,
                $commiter->changes->insertions->percent . " %",
                $commiter->changes->deletions->total,
                $commiter->changes->deletions->percent . " %",
                $commiter->changes->net,
                "1 : " . $commiter->changes->ratio
            );
        }

        return $out . '</tbody></table>';
    }

    function getGitRemote()
    {
        $tty = shell_exec('cd ' . $this->localPath . ' && git remote -v |grep push |awk \'{printf "%s %s", $1, $2}\' -');
        $tmp = explode(" ", $tty);
        $tmp2 = explode("/", $tmp[1]);

        return (object) [
            'repo_name' => end($tmp2),
            'remote_name' => $tmp[0],
            'remote_url' => $tmp[1]
        ];
    }

    function getInsertionsDeletionsRatio($insertions, $deletions)
    {
        return round(100 / ((int) $deletions / ((int) $insertions  / 100)), 2, PHP_ROUND_HALF_DOWN);
    }

    function getPercent($sum, $int)
    {
        return round((int) $int / ((int) $sum / 100), 2, PHP_ROUND_HALF_DOWN);
    }

    function getDay($datestr)
    {
        return (string) date_format(date_create($datestr), "Y-m-d");
    }

    function getWeekday($datestr)
    {
        return (string) date_format(date_create($datestr), "D");
    }

    function getLogSince()
    {
        return (0 !== $this->logSince) ? $this->getDay($this->logSince) : 0;
    }

    function getLogUntil()
    {
        $until = (0 !== $this->logUntil) ? $this->logUntil : date("d-m-Y H:I:s");

        return $this->getDay($until);
    }

    function getLogCreated()
    {
        return date("Y-m-d H:i:s");
    }

}