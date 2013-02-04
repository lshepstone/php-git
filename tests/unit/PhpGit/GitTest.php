<?php

namespace PhpGit;

use PhpGit\Git;
use PhpProc\Process;

class GitTest_Repository extends \PhpGit\Repository
{
    public function __construct()
    {
    }
}

class GitTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructSetsPathAndProcessInstanceCorrectly()
    {
        $path = '/usr/bin/git';
        $process = new Process();

        $git = new Git($path, $process);

        $this->assertSame($path, $git->getPath());
        $this->assertSame($process, $git->getProcess());
    }

    public function testGetAndSetRepositoryClass()
    {
        $class = '\PhpGit\GitTest_Repository';

        $git = new Git('/usr/bin/git', new Process());

        $this->assertSame('\PhpGit\Repository', $git->getRepositoryClass());
        $this->assertSame($git, $git->setRepositoryClass($class));
        $this->assertSame($class, $git->getRepositoryClass());
    }

    public function testGetAndSetRepositoryClassWithInvalidClassThrowsException()
    {
        $class = '\PhpGit\Repository\Custom';

        $git = new Git('/usr/bin/git', new Process());

        $this->assertSame('\PhpGit\Repository', $git->getRepositoryClass());

        $this->setExpectedException('\PhpGit\Exception\RuntimeException');

        $this->assertSame($git, $git->setRepositoryClass($class));
    }

    public function testCloneCallsTheGitCommandCorrectlyAndReturnsRepositoryInstance()
    {
        $binary = '/usr/bin/git';
        $url = 'https://github.com/lshepstone/php-git.git';
        $path = '/path/to/repo';
        $class = '\PhpGit\GitTest_Repository';

        $result = $this->getMockBuilder('\PhpProc\Result')
            ->disableOriginalConstructor()
            ->getMock();

        $result->expects($this->once())
            ->method('hasErrors')
            ->will($this->returnValue(false));

        $process = $this->getMockBuilder('\PhpProc\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $process->expects($this->once())
            ->method('setCommand')
            ->with($this->equalTo("{$binary} clone \"{$url}\" \"{$path}\""))
            ->will($this->returnSelf());

        $process->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($result));

        $git = new Git($binary, $process);
        $git->setRepositoryClass($class);

        $repository = $git->clone($url, $path);

        $this->assertInstanceOf($class, $repository);
    }

    public function testCloneWithGitCommandErrorsThrowsException()
    {
        $binary = '/usr/bin/git';
        $url = 'https://github.com/lshepstone/php-git.git';
        $path = '/path/to/repo';
        $message = 'git encountered an error';

        $result = $this->getMockBuilder('\PhpProc\Result')
            ->disableOriginalConstructor()
            ->getMock();

        $result->expects($this->once())
            ->method('hasErrors')
            ->will($this->returnValue(true));

        $result->expects($this->once())
            ->method('getStdErrContents')
            ->will($this->returnValue($message));

        $process = $this->getMockBuilder('\PhpProc\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $process->expects($this->once())
            ->method('setCommand')
            ->with($this->equalTo("{$binary} clone \"{$url}\" \"{$path}\""))
            ->will($this->returnSelf());

        $process->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($result));

        $git = new Git($binary, $process);

        $this->setExpectedException('\PhpGit\Exception\RuntimeException', "Failed to clone {$url}: {$message}");

        $git->clone($url, $path);
    }

    public function testCallUnsupportedCommandThrowsException()
    {
        $command = 'invalidCommand';

        $git = new Git('/usr/bin/git', new Process());

        $this->setExpectedException('\PhpGit\Exception\RuntimeException', "'git {$command}' is not supported");

        $git->$command();
    }

    public function testOpenReturnsRepository()
    {
        $root = 'repos';
        $target = 'php-git';
        $path = "mfs://{$root}/{$target}";

        $mockFs = new \MockFs\MockFs();
        $mockFs->getFileSystem()
            ->addDirectory($root)
            ->addDirectory($target, $root)
            ->addDirectory('.git', "/{$root}/{$target}");

        $git = new Git('/usr/bin/git', new Process());

        $repository = $git->open($path);

        $this->assertSame($path, $repository->getPath());
        $this->assertSame($git, $repository->getGit());
    }
}
