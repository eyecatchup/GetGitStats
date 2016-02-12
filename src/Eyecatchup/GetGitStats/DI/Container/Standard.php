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


use Eyecatchup\GetGitStats\DI;
use Eyecatchup\GetGitStats\Model;

class Standard extends DI\Container
{
    /**
     *  Sets the default properties.
     *
     *  @param array $properties Dependency injection settings.
     */
    public function __construct(array $properties = [])
    {
        $this->configure([
            'log_since'  => false,
            'log_until'  => false,
            'log_author' => false,
            'local_path' => false
        ]);

        $this->configure($properties);
        $this->setupRepositoryModel();
    }

    /**
     *  Set defaults for configurable properties.
     */
    protected function setupRepositoryModel()
    {
        $this->configure([
            'repo_init_props' => [
                'repo_name' => '',
                'local_path' => $this->get('local_path'),
                'remote_url' => '',
                'remote_name' => '',
                'log_since' => $this->get('log_since'),
                'log_until' => $this->get('log_until'),
                'log_created' => date('Y-m-d'),
                'total' => [
                    'commits' => 0,
                    'commit_authors' => 0,
                    'commit_days' => 0,
                    'files_changed' => 0,
                    'line_insertions' => 0,
                    'line_deletions' => 0,
                    'lines_net' => 0,
                ],
                'author' => $this->get('log_author'),
                'authors' => [],
                'commits' => []
            ],
            'repo' => DI\Container::unique(function($C) {
                return new Model\RepositoryModel($C->get('repo_init_props'));
            })
        ]);
    }
}