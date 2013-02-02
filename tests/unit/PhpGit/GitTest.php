<?php

namespace PhpGit;

use PhpGit\Git;
use PhpProc\Process;

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
        $class = '\PhpGit\Repository\Custom';

        $git = new Git('/usr/bin/git', new Process());

        $this->assertSame('\PhpGit\Repository', $git->getRepositoryClass());
        $this->assertSame($git, $git->setRepositoryClass($class));
        $this->assertSame($class, $git->getRepositoryClass());
    }

    public function testCloneCallsTheGitCommandCorrectly()
    {
        $binary = '/usr/bin/git';
        $url = 'https://github.com/lshepstone/php-git.git';
        $path = '/path/to/repo';

        $process = $this->getMockBuilder('\PhpProc\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $process->expects($this->once())
            ->method('setCommand')
            ->with($this->equalTo("{$binary} clone \"{$url}\" \"{$path}\""))
            ->will($this->returnSelf());

        $process->expects($this->once())
            ->method('setCommand')
            ->with($this->equalTo("{$binary} clone \"{$url}\" \"{$path}\""))
            ->will($this->returnSelf());

        $git = new Git($binary, $process);

        $git->clone($url, $path);
    }
}
