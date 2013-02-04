php-git
=======

Simple PHP wrapper for the Git command

[![Build Status](https://travis-ci.org/lshepstone/php-git.png?branch=master)](https://travis-ci.org/lshepstone/php-git)

```php
use \PhpGit\Git;
use \PhpProc\Process;

$git = new Git('/usr/bin/git', new Process());
$repository = $git->clone('https://github.com/lshepstone/php-git.git', 'php-git');
```
