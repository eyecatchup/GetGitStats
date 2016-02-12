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


use Eyecatchup\GetGitStats\Helper\Git;

class RepositoryModel
{
    /**
     *	Defaults.
     *
     *	@var array
     */
    protected $properties = null;

    /**
     * RepositoryModel constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->setProps($properties);
    }

    /**
     * @return mixed
     */
    public function getLocalPath()
    {
        return $this->properties['local_path'];
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function commitsByAuthor($data)
    {
        $html = 'author';
        /*if ($this->defaults['templateRoot']) {
            $file = $this->defaults['templateRoot'] . DIRECTORY_SEPARATOR .
                $this->defaults['template']['baseTemplateFilename'];

            $html = @file_get_contents((string) $file);
        }*/
        return (string) $html;
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function commitsByDate($data)
    {
        return (string) 'date';
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function commitsByWeekday($data)
    {
        return (string) 'weekday';
    }

    public function getComposedString()
    {
        return (string) '123';
    }

    /**
     * @param array $properties
     */
    private function setProps(array $properties)
    {
        $this->properties = $properties;

        if (false !== $this->getLocalPath() &&
            is_string($this->getLocalPath()))
        {
            if (Git::isGitDir($this->getLocalPath())) {
                $this->properties['commits'] = Git::parseGitlog(
                    $this->getLocalPath(),
                    $this->properties['log_since'],
                    $this->properties['log_until'],
                    $this->properties['author']
                );
            }
        }
    }

    /**
     * @return array
     */
    public function getProps()
    {
        return $this->properties;
    }

    private function reconstitute()
    {

    }
}
