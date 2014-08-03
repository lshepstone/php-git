<?php

namespace PhpGit;

use PhpGit\Exception\RepoNotFoundException;

/**
 * Repository
 *
 * Represents a Git repository.
 */
class Repository
{
    /**
     * File path to the repository.
     *
     * @var string
     */
    protected $path;

    /**
     * Git instance used to execute commands in context of this repository.
     *
     * @var Git
     */
    protected $git;

    /**
     * Constructs a new instance.
     *
     * @param $path File path to this repository
     * @param  Git                        $git Git instance used to execute commands in context of the repository.
     * @throws Exception\RuntimeException
     */
    public function __construct($path, Git $git)
    {
        if (false === $git->repoExists($path)) {
            throw new RepoNotFoundException("No Git repository found at {$path}");
        }

        $this->path = (string) $path;
        $this->git = $git;
    }

    /**
     * Gets the file path to this repository.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the Git instance used to execute commands in context of the repository.
     *
     * @return Git
     */
    public function getGit()
    {
        return $this->git;
    }

    /**
     * Pulls from the default remote repository.
     */
    public function pull()
    {
        $this->git->pull($this->path);
    }
}
