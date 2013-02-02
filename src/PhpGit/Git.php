<?php

namespace PhpGit;

use PhpProc\Process;
use PhpGit\Exception\RuntimeException;

class Git
{
    protected $path;

    protected $process;

    protected $repositoryClass = '\PhpGit\Repository';

    public function __construct($path, Process $process)
    {
        $this->path = (string) $path;
        $this->process = $process;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    public function setRepositoryClass($class)
    {
        $this->repositoryClass = (string) $class;

        return $this;
    }

    public function __call($method, $arguments)
    {
        switch($method) {
            case 'clone':
                $method = 'invokeClone';
                break;

            case 'open':
                $method = 'invokeOpen';
                break;

            case 'init':
                $method = 'invokeInit';
                break;

            default:
                throw new RuntimeException("'git {$method}' is not supported");
        }

        return call_user_func_array(array($this, $method), $arguments);
    }

    protected function invokeOpen($path)
    {
        if (false === is_a($this->repositoryClass, '\PhpGit\Repository', true)) {
            throw new RuntimeException("{$this->repositoryClass} is not a valid repository class");
        }

        return new $this->repositoryClass($path, $this);
    }

    protected function invokeClone($url, $path)
    {
        $result = $this->process
            ->setCommand("{$this->path} clone \"{$url}\" \"{$path}\"")
            ->execute();

        if ($result->hasErrors()) {
            throw new RuntimeException("Failed to clone {$url}; git says: "
                . PHP_EOL . '  ' . $result->getStdErrContents());
        }

        return $this->invokeOpen($path);
    }
}
