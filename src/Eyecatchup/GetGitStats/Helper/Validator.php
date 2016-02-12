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

class Validator
{
    /**
     * Validate the Dependency-Injection configuration array length.
     *
     * @param array $array Dependency-Injection configuration
     * @param bool $acceptEmpty Allow empty DI configuration? (Used by main constructor.)
     *
     * @void
     * @throws E\NoConfigurationException
     * @throws E\InvalidConfigurationException
     */
    public static function configLength(array $array, $acceptEmpty)
    {
        if (4 < sizeof($array)) {
            throw new E\InvalidConfigurationException(
                'Invalid configuration; too many options. You cannot set more than 4 options.');
        }
        elseif (true !== $acceptEmpty && 0 === sizeof($array)) {
            throw new E\NoConfigurationException(
                    "Missing configuration; the 'local_path' option is required / must be set." .
                    "See the `parse()` documentation for details.");
        }

        return;
    }

    /**
     * Validate the Dependency-Injection configuration array contents.
     *
     * @param array $array Dependency-Injection configuration
     * @param bool $acceptEmpty Allow empty DI configuration? (Used by main constructor.)
     *
     * @return array Validated Dependency-Injection configuration
     */
    public static function config(array $array, $acceptEmpty = false)
    {
        Validator::configLength($array, (bool) $acceptEmpty);

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
    public static function validConfig()
    {
        return [
            'log_since',
            'log_until',
            'log_author',
            'local_path'
        ];
    }
}
