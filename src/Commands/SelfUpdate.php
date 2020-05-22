<?php namespace Tatter\Patches\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
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

		// Run everything up to the merge
		$this->patches->beforeUpdate();
		$this->patches->update();
		$this->patches->afterUpdate();


		// Display the menu, if user selects "quit" then abort
		if (! $this->mergeMenu())
		{
			CLI::write('Patch aborted. Workspace with codex and files:');
			CLI::write($codex->workspace);

			// Write out the Codex
			$this->patches->getCodex()->save();

			return;
		}

		// Call the chosen merging method
		$this->patches->merge();

		// Write out the Codex
		$this->patches->getCodex()->save();

		return $result;
	}

	/**
	 * Display and process the main menu
	 *
	 * @return bool  Whether or not to continue with merge
	 */
    protected function mergeMenu()
    {
		$codex  = $this->patches->getCodex();
		$counts = [
			'changed' => count($codex->changedFiles),
			'added'   => count($codex->addedFiles),
			'deleted' => count($codex->deletedFiles),
		];
		
		if (! array_sum($counts))
		{
			CLI::write('No files to merge!', 'yellow');
			CLI::write('(P)roceed');
			CLI::write('(Q)uit');
			return CLI::prompt('Selection?', ['p', 'q']) === 'p';
		}

		CLI::write('What would you like to do:');
		CLI::write('(P)roceed with the merge');
		CLI::write('(S)how all files');
		CLI::write('Show (C)hanged files (' . $counts['changed'] . ')');
		CLI::write('Show (A)dded files (' . $counts['added'] . ')');
		CLI::write('Show (D)eleted files (' . $counts['deleted'] . ')');
		CLI::write('(Q)uit');

    	switch (CLI::prompt('Selection?', ['p', 's', 'c', 'a', 'd', 'q']))
    	{
    		case 'p':
    			return true;
    		break;

    		case 'q':
    			return false;
    		break;

    		case 's':
    			$this->showFiles($codex->changedFiles, 'Changed');
    			$this->showFiles($codex->addedFiles, 'Added');
    			$this->showFiles($codex->deletedFiles, 'Deleted');
    		break;

    		case 'c':
    			$this->showFiles($codex->changedFiles, 'Changed');
    		break;

    		case 'a':
    			$this->showFiles($codex->addedFiles, 'Added');
    		break;

    		case 'd':
    			$this->showFiles($codex->deletedFiles, 'Deleted');
    		break;
    	}
    	
    	// If a non-returning item was select then run the menu again
    	return $this->mergeMenu();
    }

	/**
	 * Display files in a table list.
	 *
	 * @param array $files    Array of files to list
	 * @param string $status  Changed/Added/Deleted
	 *
	 * @return $this
	 */
    protected function showFiles(array $files, string $status): self
    {
    	if (empty($files))
    	{
			CLI::write('No ' . strtolower($status) . ' files', 'yellow');
			return $this;
    	}

    	$thead = ['File', 'Status', 'Diff'];
    	$tbody = [];

    	foreach ($files as $file)
    	{
    		$tbody[] = [$file, $status, ''];
    	}

		CLI::table($tbody, $thead);

    	return $this;
    }
}
