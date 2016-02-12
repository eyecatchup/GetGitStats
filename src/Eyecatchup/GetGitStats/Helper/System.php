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

class System
{
    /**
     * Check if the given path points to a valid directory.
     *
     * @param string $path The local path to a Git repository
     *
     * @return bool
     * @throws E\InvalidPathException
     */
    public static function isDir($path)
    {
        if (!is_dir(realpath($path))) {
            $msg = sprintf("Invalid path; directory '%s' does not exist.", $path);
            throw new E\InvalidPathException($msg);
        }

        return true;
    }
}
