<?php

namespace PhpGit;

use PhpGit\Git;
use PhpGit\Exception\RuntimeException;

class Repository
{
    protected $path;

    protected $git;

    public function __construct($path, Git $git)
    {
        if (false === is_dir("{$path}/.git")) {
            throw new RuntimeException("No git repository found at {$path}");
        }

        $this->path = (string) $path;
        $this->git = $git;
    }

    public function getPath()
    {
        return $this->path;
    }
}
