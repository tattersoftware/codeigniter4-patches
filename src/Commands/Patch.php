<?php namespace Tatter\Patches\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;


class Patch extends BaseCommand
{
    protected $group       = 'Patches';
    protected $name        = 'patch';
    protected $description = 'Check for Composer updates then patch this project.';
    protected $usage       = 'patch';

    public function run(array $params)
    {
		$config = config('Patches');
		foreach ($config->paths as $path)
		{
			CLI::write($path['from'] . ' => ' . $path['to']);
		}

		// Call `composer update` command programmatically
		// https://stackoverflow.com/questions/17219436/run-composer-with-a-php-script-in-browser#25208897
		$application = new Application();
		$application->setAutoExit(false); // prevent `$application->run` method from exiting the script

		$input = new ArrayInput([
			'command' => 'update',
			'--working-dir' => ROOTPATH,
		]);
		$application->run($input);

		echo 'Done.';
	}
}
