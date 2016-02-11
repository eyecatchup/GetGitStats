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


namespace Eyecatchup\GetGitStats\DI\Container;


use Eyecatchup\GetGitStats\DI\Container;
use Eyecatchup\GetGitStats\Model\RepositoryModel;

class Standard extends Container
{
    /**
     *  Sets the default properties.
     *
     *  @param array $properties Dependency injection settings.
     */
    public function __construct(array $properties = [])
    {
        $this->configure([
            'log_since'  => 0,
            'log_until'  => 0,
            'log_author' => 0,
            'local_path' => 0
        ]);

        $this->configure($properties);
        $this->setupRepo();
    }

    /**
     *  Set defaults for configurable properties.
     */
    protected function setupRepo()
    {
        $this->configure([
            'repoDefaults' => [
                'since'  => $this->get('log_since'),
                'until'  => $this->get('log_until'),
                'author' => $this->get('log_author'),
                'path'   => $this->get('local_path')
            ],
            'repo' => Container::unique(function($C) {
                return new RepositoryModel($C->get('repoDefaults'));
            })
        ]);
    }
}