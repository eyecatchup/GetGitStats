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


use Eyecatchup\GetGitStats\Common\Exception as E;
use Eyecatchup\GetGitStats\DI\Container;
use Eyecatchup\GetGitStats\DI\Container\Standard as StandardContainer;
use Eyecatchup\GetGitStats\Interfaces\OutputInterface;

class GetGitStats
{
    /**
     *  Dependency-Injection container.
     *
     *  @var DI\Container
     */
    protected $container = null;

    /**
     *  Any valid output interface implementation.
     *
     *  @var Clients\StringOutput | Clients\JsonOutput
     */
    private $output;

    /**
     *  Git repository domain model representation.
     *
     *  @var Model\RepositoryModel
     */
    private $repo;
    #protected $repo;

    /**
     * Eyecatchup\GetGitStats Constructor.
     *
     * @param array $config Dependency-Injection configuration
     *
     * @void
     */
    public function __construct(array $config = [])
    {
        $this->container = new StandardContainer(
            Helper\Validator::config($config, true)
        );

        $this->setRepositoryModel(
            $this->container->get('repo')
        );

        $this->setOutput(
            new Clients\StringOutput
        );
    }

    /**
     * Update the DI container config and the domain model instance.
     *
     * @todo Add check for, and exception if, new local path === old local path
     * @param array $config Dependency-Injection configuration
     *
     * @void
     */
    public function parse(array $config)
    {
        $this->container->configure([
            'repo_init_props' => Helper\Validator::config($config)
        ]);

        $this->setRepositoryModel(Factory\RepositoryFactory::create(
            $this->container->get('repo_init_props')
        ));
    }

    /**
     * Get a reconstituted string representation of the commit data in buffer.
     *
     * @return string String-representation of the reconstituted commit data
     * @throws E\GetGitStatsException
     */
    public function getCommitsByAuthor()
    {
        if ($this->getRepositoryModel() === null) {
            throw new E\GetGitStatsException('Nothing done yet! Use createDocument first.');
        }

        return $this->output->load(
            $this->repo->commitsByAuthor([])
        );
    }

    /**
     * Get a reconstituted string representation of the commit data in buffer.
     *
     * @return string String-representation of the reconstituted commit data
     * @throws E\GetGitStatsException
     */
    public function getCommitsByDate()
    {
        if ($this->getRepositoryModel() === null) {
            throw new E\GetGitStatsException('Nothing done yet! Use createDocument first.');
        }

        return $this->output->load(
            $this->repo->commitsByDate([])
        );
    }

    /**
     * Get a reconstituted string representation of the commit data in buffer.
     *
     * @return string String-representation of the reconstituted commit data
     * @throws E\GetGitStatsException
     */
    public function getCommitsByWeekday()
    {
        if ($this->getRepositoryModel() === null) {
            throw new E\GetGitStatsException('Nothing done yet! Use createDocument first.');
        }

        return $this->output->load(
            $this->repo->commitsByWeekday([])
        );
    }

    /**
     * Get the internal Dependency-Injection container.
     *
     * @return DI\Container DI-container
     */
    private function container()
    {
        return $this->container;
    }

    /**
     * Get the domain model representation of the Git log-data in buffer.
     *
     * @return Model\RepositoryModel The Git repository domain model
     */
    public function getRepositoryModel()
    {
        #return $this->container->get('repo');
        return $this->repo;
    }

    /**
     * (Re-)Set the domain model instance.
     *
     * @param Model\RepositoryModel $model The domain model
     * @void
     */
    private function setRepositoryModel(Model\RepositoryModel $model)
    {
        $this->repo = $model;
        $this->container->set('repo', $this->repo);
    }

    /**
     * Set the output format for the data in buffer.
     *
     * @param OutputInterface $outputType Output interface
     * @void
     */
    public function setOutput(OutputInterface $outputType)
    {
        $this->output = $outputType;
    }




}
