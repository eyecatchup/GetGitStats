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


class Validator
{
    /**
     * Validate the Dependency-Injection configuration array.
     *
     * @param array $array Dependency-Injection configuration
     *
     * @return array Validated Dependency-Injection configuration
     */
    public static function config(array $array)
    {
        $out = [];

        if (0 < sizeof($array)) {
            $valid = Validator::validConfig();

            foreach ($array as $key => $val) {
                if (in_array($key, $valid)) {
                    $out[$key] = $val;
                }
            }
        }

        return $out;
    }

    /**
     * Get valid keys for DI-config array.
     *
     * @return array Valid DI-config items
     */
    private static function validConfig()
    {
        return [
            'log_since',
            'log_until',
            'log_author',
            'local_path'
        ];
    }
}