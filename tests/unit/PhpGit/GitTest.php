<?php

namespace PhpGit;

use PhpProc\Process;
use MockFs\MockFs;

class GitTest_Repository extends \PhpGit\Repository
{
    public function __construct()
    {
    }
}

class GitTest extends \PHPUnit_Framework_TestCase
{
    const REPO_PATH_ROOT = 'repos';
    const REPO_PATH_REPO = 'php-git';

    protected $repoPath = null;

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
        $path = $this->getMockRepoPath();
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
        $path = $this->getMockRepoPath();

        $result = $this->getMockBuilder('\PhpProc\Result')
            ->disableOriginalConstructor()
            ->getMock();

        $result->expects($this->once())
            ->method('hasErrors')
            ->will($this->returnValue(true));

        $result->expects($this->once())
            ->method('getStdErrContents')
            ->will($this->returnValue(null));

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

        $this->setExpectedException('\PhpGit\Exception\RuntimeException');

        $git->clone($url, $path);
    }

    public function testPullCallsTheGitCommandCorrectly()
    {
        $binary = '/usr/bin/git';
        $path = $this->getMockRepoPath();
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
            ->method('setWorkingDirectory')
            ->with($this->equalTo($path))
            ->will($this->returnSelf());

        $process->expects($this->once())
            ->method('setCommand')
            ->with($this->equalTo("{$binary} pull"))
            ->will($this->returnSelf());

        $process->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($result));

        $git = new Git($binary, $process);
        $git->pull($path);
    }

    public function testPullWithGitCommandErrorsThrowsException()
    {
        $binary = '/usr/bin/git';
        $path = $this->getMockRepoPath();
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
            ->method('setWorkingDirectory')
            ->with($this->equalTo($path))
            ->will($this->returnSelf());

        $process->expects($this->once())
            ->method('setCommand')
            ->with($this->equalTo("{$binary} pull"))
            ->will($this->returnSelf());

        $process->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($result));

        $git = new Git($binary, $process);

        $this->setExpectedException('\PhpGit\Exception\RuntimeException');

        $git->pull($path);
    }

    public function testPullWithInvalidRepoDirectoryPathThrowsException()
    {
        $binary = '/usr/bin/git';

        $git = new Git($binary, new Process());

        $this->setExpectedException('\PhpGit\Exception\RepoNotFoundException');

        $git->pull('mfs://repos/invalid-repo');
    }

    public function testOpenReturnsRepository()
    {
        $path = $this->getMockRepoPath();

        $git = new Git('/usr/bin/git', new Process());

        $repository = $git->open($path);

        $this->assertSame($path, $repository->getPath());
        $this->assertSame($git, $repository->getGit());
    }

    public function testCallUnsupportedCommandThrowsException()
    {
        $command = 'invalidCommand';

        $git = new Git('/usr/bin/git', new Process());

        $this->setExpectedException('\PhpGit\Exception\RuntimeException', "'git {$command}' is not supported");

        $git->$command();
    }

    protected function getMockRepoPath()
    {
        $mockFs = new MockFs();
        $mockFs->getFileSystem()
            ->addDirectory('repos')
            ->addDirectory('php-git', '/repos')
            ->addDirectory('.git', "/repos/php-git");

        return 'mfs://repos/php-git';
    }
}
