# Tatter\Patches
Automated project updates for CodeIgniter 4

[![](https://github.com/tattersoftware/codeigniter4-patches/workflows/Tests/badge.svg)](https://github.com/tattersoftware/codeigniter4-patches/actions/workflows/test.yml)

## Quick Start

1. Install with Composer: `> composer require --dev tatter/patches`
2. Use the command to update: `> vendor/bin/patch`

## Description

**Patches** helps keep your CodeIgniter 4 projects up-to-date when there are framework changes
that affect your project source. Use one easy command to patch your development instance and
stage any conflicts for easy resolution.

## Requirements

**Patches** is built on top of [Git](https://git-scm.com) and [Composer](https://getcomposer.org),
so requires both of those be installed and accessible in your `$PATH`. Additionally, the
`patch` command makes the following assumptions (and will fail if they are not met):

* You project is in an existing Git repository (with configured Git user)
* The CodeIgniter 4 framework is installed via Composer in your **vendor/** directory
* The current branch is "clean" (no uncommitted changes or unstaged files)
* Your project files are in their standard locations (**app/**, **public/**, **env**, **spark**)
* You have no ignored files in **app/** or **public/** that the patch process would disrupt
* The **vendor/** folder must be ignored for tracking on your Git repository

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
```console
> composer require --dev tatter/patches
```

> Note: While **Patches** can be run in a production environment it is strongly recommended
  that you install it in development (using `--dev`) and then deploy the repo changes to production.

You may also download the script and add it to your favorite projects.

## Usage

**Patches** comes with a single script, `patch`, which Composer will treat as a binary and
deploy to your **vendor/bin/** folder. Simply run the command to kick off the patch process:

    ./vendor/bin/patch

### Arguments

Most of the time the simple script is what you will want, but `patch` takes a few arguments
to alter the behavior of the patch process. Arguments that take a "commit-ish" can use anything
Git recognizes (branch, hash, tag, reference, revision, etc).

#### Help (-h)

Displays the latest command help:

```console
Usage: ./patch [-c <current version>] [-v <target version>]

Patches an existing CodeIgniter 4 project repo to a different version of the framework.

Options:
  -h             Help. Show this help message and exit
  -c commit-ish  Alternate version to consider "current" (rarely needed).
  -v commit-ish  Version to use for patching. Defaults to the latest.
```

#### Version (-v <commit-ish>)

Manually sets the version to patch to. This is useful if you need to stop at a specific
release, or if your project is pointed at the `develop` branch and you do not want certain
commits. Examples:

* Patch the current installed repo to a specific version.
	./vendor/bin/patch -v 4.1.2

* Patch up to a specific commit.
	./vendor/bin/patch -v dev-develop#0cff5488676f36f9e08874fdeea301222b6971a2

#### Current (-c <commit-ish>)

Ignores the current installed version of the framework in favor of the specified one. This
is unlikely to be needed in most cases, but can be helpful for example with new installations
or if you updated with Composer but forgot to run patches first. Example:

* Assume the repo is in an older state and patch.

## How it Works

**Patches** is a shell script that calls `git` and `composer`. When called it will simulate
an upgrade from your current version of the framework to the latest or specified version.
The simulation assumes no files were modified in your project, which is very likely not the
case, so the staged simulation is then compared as a three-way merge against your current
project root. **Patches** works in a dedicated branch (`tatter/scratch`) so it will never
modify your project directly. Patched files are all staged on `tatter/patches` so you can
review them before merging or pushing to remote. Consider the following examples.

### Added Files

**CodeIgniter** decides it is time for a `Widget` component, which includes **app/Config/Widget.php**
for the configuration. Your project is running version `4.1.2` but wants to update to `4.2.0` to
use this new component. When **Patches** simulates the update between these versions `git` will
notice the new file:

```console
A	app/Config/Widget.php
```

When the final stage of the patch is run this new file will be merged into your project.

### Changed Files

In addition to the config file above, `Widget` also comes with a great new `WidgetFilter`. As with
all `Filters` it must be aliased in your **app/Config/Filters.php** file before it can be used.
The framework already took care of this for new projects:

```php
class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'csrf'     => CSRF::class,
        'toolbar'  => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'widget'   => WidgetFilter::class,
    ];
```

When the final stage of the patch is run `git` will examine your existing file at **app/Config/Filters.php**
and perform its signature [three-way merge](https://en.wikipedia.org/wiki/Merge_(version_control)#Recursive_three-way_merge)
(technically, this is done with a `cherry-pick`). This means if your version is unchanged or if it has
been modified in a compatible way then **Patches** it will apply the edits for you without intervention.

### Conflicts

Compatible changes are great, but say you have a weird layout fetish (no judgment) and the
**app/Config/Filters.php** file in your project now looks like this:

```php
class Filters extends BaseConfig
{
    public $aliases = ['csrf' => CSRF::class,'toolbar' => DebugToolbar::class,'honeypot' => Honeypot::class];
```

You likely moral corruption aside, `git` will not know how to handle merging the new `WidgetFilter`
and you now have a conflict. **Patches** will clean up and leave your repo in the conflict state
so you can proceed with your favorite conflict resolution. Open **app/Config/Filters.php** in
your favorite text editor to find the conflict:

```php
class Filters extends BaseConfig
{
<<<<<<< HEAD
    public $aliases = ['csrf' => CSRF::class,'toolbar' => DebugToolbar::class,'honeypot' => Honeypot::class];
=======
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'csrf'     => CSRF::class,
        'toolbar'  => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'widget'   => WidgetFilter::class,
    ];
>>>>>>> tatter/scratch
```

Once you have resolved all the conflicts you can finish the cherry-pick. For example in this case
you would update the file and run the following commands:

```shell
git add app/Config/Filters.php
git cherry-pick --continue
```

## Troubleshooting

### Compatibility

If you are unsure whether **Patches** is compatible with your environment, it is recommended that
you run the test cases first. Clone or download the repo and launch the tests with their `run` command:

	./tests/run

### Clean Up

* It is **always** safe to delete `tatter/scratch` - this branch has nothing relevant to your project.
* It is **always** safe to delete `tatter/patches`, but if you have not committed and merged the changes then you will need to start the patch process over.
* Should you decide not to use **Patches** anymore just remove the Composer package or delete the script - that's all!

### Recovery

**Patches** is very conservative and takes many precautions not to touch any of your project files.
If you are relatively new to Git and you get into a merge conflict that becomes a mess, the first
thing to do: *don't panic*! Your files are safe and your repo is intact and the only thing that can
compromise that is typing in a bunch of commands you do not understand from the internet.

The first thing to know is that **Patches** works with two dedicated branches: `tatter/scratch` is
where it stages all the files, and `tatter/patches` is where it attempts the merge. If you are stuck
make sure you know which branch you are on using `git branch` - likely your project uses one of the
typical "main" branches: `develop`, `main`, or `master`.

Next thing to be aware of, the final merge stage that could induce conflict is actually handled by
a `cherry-pick`. This is a technical Git process for isolating a single commit and applying it to
another branch. if you are mid-cherry-pick then `git status` should display the current state, as well
as any conflicting files and some hints for how to proceed:

```console
git status
On branch tatter/patches
You are currently cherry-picking commit a8b4361.
  (all conflicts fixed: run "git cherry-pick --continue")
  (use "git cherry-pick --skip" to skip this patch)
  (use "git cherry-pick --abort" to cancel the cherry-pick operation)
```

As hinted above, you should be able to abort the entire process and get back to your unaltered
project state any time you like with the following commands (swap `develop` for your main branch name):
* `git cherry-pick --abort`
* `git switch develop`

### Support

Still need help?

* Visit the [CodeIgniter Forums](https://forum.codeigniter.com/) to ask for help.
* Click the "Sponsor" button on [the Patches repo](https://github.com/tattersoftware/codeigniter4-patches) for premium support options

**GitHub Issues are for Bug Reports and Feature Requests only. Issues opened for support will be
closed and their authors browbeaten.**
