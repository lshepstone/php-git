<?php

namespace PhpGit;

use PhpProc\Process;
use PhpGit\Exception\RuntimeException;

/**
 * Git
 *
 * Simple wrapper class for the Git binary.
 */
class Git
{
    /**
     * Default Repository class.
     */
    const REPO_CLASS = '\PhpGit\Repository';

    /**
     * Path to the Git binary.
     *
     * @var string
     */
    protected $path;

    /**
     * Process instance to be used to execute Git commands.
     *
     * @var \PhpProc\Process
     */
    protected $process;

    /**
     * Class to be used for repository instances.
     *
     * @var string
     */
    protected $repositoryClass = self::REPO_CLASS;

    /**
     * Constructs a new instance.
     *
     * @param string $path Path to the Git binary
     * @param \PhpProc\Process $process Process instance to be used to execute Git commands.
     */
    public function __construct($path, Process $process)
    {
        $this->path = (string) $path;
        $this->process = $process;
    }

    /**
     * Gets the path to the Git binary.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the process instance to be used to execute Git commands.
     *
     * @return \PhpProc\Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Gets the class to be used for repository instances.
     *
     * @return string
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * Sets the class to be used for repository instances.
     *
     * @param string $class Class to be used
     * @return Git Fluent interface
     * @throws Exception\RuntimeException
     */
    public function setRepositoryClass($class)
    {
        if (false === is_a($class, self::REPO_CLASS, true)) {
            throw new RuntimeException("{$class} is not a valid repository class");
        }

        $this->repositoryClass = (string) $class;

        return $this;
    }

    /**
     * Method overloading to support calling Git commands directly on an instance.
     *
     * @param string $command Git command to call
     * @param array $arguments Arguments to pass to the method supporting the command
     * @return mixed Returns the result of the method supporting the Git command
     * @throws Exception\RuntimeException
     */
    public function __call($command, $arguments)
    {
        switch($command) {
            case 'clone':
                $method = 'invokeClone';
                break;

            case 'open':
                $method = 'invokeOpen';
                break;

            default:
                throw new RuntimeException("'git {$command}' is not supported");
        }

        return call_user_func_array(array($this, $method), $arguments);
    }

    /**
     * Opens an existing repository at the specified file path.
     *
     * @param $path File path of the repository to open
     * @return Repository
     */
    protected function invokeOpen($path)
    {
        return new $this->repositoryClass($path, $this);
    }

    /**
     * Clones a remote repository to the specified file path.
     *
     * @param string $url URL to remote repository to clone
     * @param string $path File path to clone the repository to
     * @return Repository
     * @throws Exception\RuntimeException
     */
    protected function invokeClone($url, $path)
    {
        $result = $this->process
            ->setCommand("{$this->path} clone \"{$url}\" \"{$path}\"")
            ->execute();

        if ($result->hasErrors()) {
            throw new RuntimeException("Failed to clone {$url}: {$result->getStdErrContents()}");
        }

        return $this->invokeOpen($path);
    }
}
