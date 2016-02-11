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


use Eyecatchup\GetGitStats\Common\NoGitRepositoryInDirectoryException;

class System
{
    /**
     * Check if the given path points to a valid Git repository.
     *
     * @param string $path The local path to a Git repository
     *
     * @return bool
     * @throws NoGitRepositoryInDirectoryException
     */
    protected static function isGitDir($path)
    {
        if (!file_exists(realpath($path . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'config'))) {
            throw new NoGitRepositoryInDirectoryException('No .git directory in ' . $path);
        }

        return true;
    }

    /**
     * Get the remote URL of a Git repository.
     *
     * @param string $path The local path to a Git repository
     *
     * @return array Repo name, remote name and remote URL
     */
    protected static function getGitRemote($path = '.')
    {
        $tty = shell_exec('cd ' . $path . ' && git remote -v |grep push |awk \'{printf "%s %s", $1, $2}\' -');
        $tmp = explode(" ", $tty);
        $tmp2 = explode("/", $tmp[1]);

        return [
            'repo_name' => end($tmp2),
            'remote_name' => $tmp[0],
            'remote_url' => $tmp[1]
        ];
    }
}