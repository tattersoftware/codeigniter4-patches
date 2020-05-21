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

	/**
	 * The library instance
	 *
	 * @var Tatter\Patches\Patches
	 */
	public $patches;

	/**
	 * Run through the entire patch process with feedback and interaction.
	 */
    public function run(array $params)
    {
		CLI::write('Beginning patch process');
		$this->patches = new Patches();

		// Display config info
		$codex   = $this->patches->getCodex();
		$sources = $this->patches->getSources();

		CLI::write('Using the following configuration:');
		CLI::table([
			['Updater',   $codex->config->updater],
			['Merger',    $codex->config->merger],
			['Base Path', $codex->config->basePath],
			['Project',   $codex->config->rootPath],
			['Deletes?',  $codex->config->allowDeletes ? 'Allowed' : 'Disabled'],
			['Events?',   $codex->config->allowEvents ? 'Allowed' : 'Disabled'],
			['Sources',   $sources ? implode(', ', array_keys($sources)) : 'None detected'],
			['Ignored',   $codex->config->ignoredSources ? implode(', ', $codex->config->ignoredSources) : 'None'],
		]);

		$this->menu();
return;
		// Run everything up to the merge
		$this->patches->beforeUpdate();
		$this->patches->update();
		$this->patches->afterUpdate();

		$this->menu();

		// Call the chosen merging method
		$this->patches->merge();

		// Write out the Codex
		$this->patches->getCodex->save();

		return $result;
	}

	/**
	 * Display and process the main menu
	 */
    public function menu()
    {
		CLI::write('What would you like to do:');
		CLI::write('(M)erge the files');
		CLI::write('(S)how all files');
		CLI::write('Show (C)hanged files');
		CLI::write('Show (A)dded files');

    	switch (CLI::prompt('Selection?', ['m', 's', 'c', 'a']))
    	{
    		default:
    			CLI::write('Good choice!');
    	}
    }
}
