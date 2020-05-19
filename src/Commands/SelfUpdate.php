<?php namespace Tatter\Patches\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Tatter\Patches\Patches;


class SelfUpdate extends BaseCommand
{
    protected $group       = 'CodeIgniter';
    protected $name        = 'selfupdate';
    protected $description = 'Check for Composer updates then patch this project.';
    protected $usage       = 'selfupdate';

    public function run(array $params)
    {
		$patches = new Patches();

		$patches->run();
	}
}
