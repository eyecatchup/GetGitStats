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


class Arrays
{
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
}