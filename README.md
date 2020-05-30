# Tatter\Patches
Module for updating CodeIgniter 4 projects

[![](https://github.com/tattersoftware/codeigniter4-patches/workflows/PHP%20Unit%20Tests/badge.svg)](https://github.com/tattersoftware/codeigniter4-patches/actions?query=workflow%3A%22PHP+Unit+Tests%22)

## Quick Start

1. Install with Composer: `> composer require --dev tatter/patches`
2. Use the command to update: `> php spark selfupdate`

## Description

**Patches** helps keep your CodeIgniter 4 projects up-to-date when there are framework or
other upstream changes that affect your project source. Use one easy command to patch your
development instance and update dependencies all at once.

### Process

The library will initialize and locate any source files in **{namespace}/Patches**. Legacy
files identified by the source files are copied into the workspace prior to running the update.
After running updates (e.g. the equivalent of `composer update`) any files that changed are
compared with their equivalent version in your project root. Any eligible files are automatically
merged and then you are guided through conflict resolution in cases where a file could not be
merged automatically.

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
* `> composer require --dev tatter/patches`

Or, install manually by downloading the source files and adding the directory to
`app/Config/Autoload.php`.

**Note:** While **Patches** can be run in a production environment it is strongly recommended
that you install it in development (using `--dev`) and then apply changes manually to production.

## Configuration

The library's default behavior can be altered by extending its config file. Copy
**bin/Patches.php** to **app/Config/** and follow the instructions
in the comments. If no config file is found in **app/Config** the library will use its own.

By default **Patches** will use its own built-in handlers for updating (Composer) and merging
(Copy). You can change to another bundled handler (or write your own) using the config values.
You can also disable unwanted aspects of the patching process (like Events). **Patches** will
also auto-detect source files and use any available, but you can specify `ignoredSources` by
their shortname (e.g. "Framework") to prevent using a source.

## Usage

**Patches** comes with a CLI Command to run patches and guide you through the process. After
the module is installed and configured, run "selfupdate" from the command line:

	php spark selfupdate

Follow the prompts to complete the patch process. Copies of the files are left in the workspace
(default: **ROOTPATH/writable/patches/{datetime}**) along with **codex.json**, a copy of the run log.

## Example

Below is a guided example of the patch process. This shows taking [CodeIgniter Playground](https://github.com/codeigniter4projects/playground)
from an earlier release of the framework up to version `4.0.3`.

> Add Patches to the existing project in a development setting

```
> composer require --dev tatter/patches
```
	
> All defaults are fine so kick off the patch process, which will begin with an overview of the configuration

```
> php spark selfupdate

CodeIgniter CLI Tool - Version 4.0.0-rc4 - Server-Time: 2020-05-27 19:23:15pm

Beginning patch process
Detected sources: Framework
Using the following configuration:
+-----------+--------------------------------------------------+
| Updater   | Tatter\Patches\Handlers\Updaters\ComposerHandler |
| Merger    | Tatter\Patches\Handlers\Mergers\CopyHandler      |
| Base Path | /var/www/igniter.be/patches/writable/patches     |
| Project   | /var/www/igniter.be/patches/                     |
| Deletes?  | Allowed                                          |
| Events?   | Allowed                                          |
| Sources   | Framework                                        |
| Ignored   | None                                             |
+-----------+--------------------------------------------------+
```

> The prepatch process will copy existing source files and run the update

```
66 legacy files copied to /var/www/igniter.be/patches/writable/patches/2020-05-29-192315/legacy/

Loading composer repositories with package information
Updating dependencies (including require-dev)         
Package operations: 0 installs, 1 update, 0 removals
  - Updating codeigniter4/framework (v4.0.0-rc4 => v4.0.3):  Checking out 6d019e5354
Writing lock file
Generating autoload files
```

> After the files are updated Patches analyzes changes and presents a menu based on your project's differences

```
7 changed files detected
0 added files detected
0 deleted files detected

What would you like to do:
(P)roceed with the merge
(L)ist all files
Show (C)hanged files (7)
Show (A)dded files (0)
Show (D)eleted files (0)
(Q)uit
Selection? [p, l, c, a, d, q]:
```

> You can view which files were affected by typing "l" (L)

```
+---------------------------------+---------+------+
| File                            | Status  | Diff |
+---------------------------------+---------+------+
| app/Config/Autoload.php         | Changed | 5    |
| app/Config/Boot/development.php | Changed | 3    |
| app/Config/Boot/production.php  | Changed | 3    |
| app/Config/Boot/testing.php     | Changed | 3    |
| app/Config/Events.php           | Changed | 13   |
| app/Config/Exceptions.php       | Changed | 6    |
| app/Config/Services.php         | Changed | 3    |
+---------------------------------+---------+------+

No added files
No deleted files
```

> If everything looks correct, typing "p" will proceed with merge

```
Selection? [p, l, c, a, d, q]: p
5 files merged
2 conflicts detected
```

> If there were conflicts then an additional menu will prompt for conflict resolution

```
2 conflicts detected
What would you like to do:
(L)ist conflict files
(G)uided resolution
(O)verwrite all files
(S)kip all files
(Q)uit
```

> In this case Playground has a few config files with trivial modifications

```
Selection? [l, g, o, s, q]: l
+-------------------------+---------+------+
| File                    | Status  | Diff |
+-------------------------+---------+------+
| app/Config/Autoload.php | Changed | 5    |
| app/Config/Services.php | Changed | 3    |
+-------------------------+---------+------+
No added files
No deleted files
```

> We will use the guided resolution to view each diff then overwrite the files

```
Selection? [l, g, o, s, q]: g

This file was changed but your copy does not match the original.
app/Config/Services.php
(D)isplay diff
(O)verwrite
(S)kip
(Q)uit
Selection? [d, o, s, q]: d
-require_once SYSTEMPATH . 'Config/Services.php';
-

Selection? [d, o, s, q]: o
```

> Upon completion the path to the workspace will be displayed so you can review what was accomplished

```
Workspace with codex and files:
/var/www/igniter.be/patches/writable/patches/2020-05-29-192315/
```

> If your code is tracked you can easily see what changes were made during the patch

```
> git status
On branch patches
Changes not staged for commit:
  (use "git add <file>..." to update what will be committed)
  (use "git checkout -- <file>..." to discard changes in working directory)

	modified:   app/Config/Boot/development.php
	modified:   app/Config/Boot/production.php
	modified:   app/Config/Boot/testing.php
	modified:   app/Config/Events.php
	modified:   app/Config/Exceptions.php
	modified:   app/Config/Services.php

no changes added to commit (use "git add" and/or "git commit -a")

> git diff HEAD
...
```
