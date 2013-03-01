php-git
=======

Simple PHP wrapper for the Git command

[![Build Status](https://travis-ci.org/lshepstone/php-git.png?branch=master)](https://travis-ci.org/lshepstone/php-git)

Open
----

To open an existing repository

```php
use \PhpGit\Git;
use \PhpProc\Process;

$git = new Git('/usr/bin/git', new Process());
$repository = $git->open('./php-git');

```

Clone
-----

To clone a remote repository

```php
use \PhpGit\Git;
use \PhpProc\Process;

$git = new Git('/usr/bin/git', new Process());
$repository = $git->clone('https://github.com/lshepstone/php-git.git', './php-git');
```

Pull
----

To pull from the default remote repository

```php
use \PhpGit\Git;
use \PhpProc\Process;

$git = new Git('/usr/bin/git', new Process());
$git->pull('./php-git');

// or, using an already opened Repository instance
$repository = $git->open('./php-git');
$repository->pull();

```