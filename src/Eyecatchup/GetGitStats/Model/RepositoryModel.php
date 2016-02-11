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


namespace Eyecatchup\GetGitStats\Model;


class RepositoryModel
{
    /**
     *	Defaults.
     *
     *	@var array
     */
    protected $defaults = [];

    public function __construct(array $properties)
    {
        $this->setDefaults($properties);
    }

    public function commitsByAuthor()
    {
        $html = 'author';
        /*if ($this->defaults['templateRoot']) {
            $file = $this->defaults['templateRoot'] . DIRECTORY_SEPARATOR .
                $this->defaults['template']['baseTemplateFilename'];

            $html = @file_get_contents((string) $file);
        }*/
        return (string) $html;
    }

    public function commitsByDate($str)
    {
        return (string) 'date';
    }

    public function commitsByWeekday($str)
    {
        return (string) 'weekday';
    }

    public function getComposedString($str)
    {
        return (string) '123';
    }

    public function setDefaults(array $properties)
    {
        $this->defaults = $properties;
    }

    public function getDefaults()
    {
        return $this->defaults;
    }
}