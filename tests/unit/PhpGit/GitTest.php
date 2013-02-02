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
}
