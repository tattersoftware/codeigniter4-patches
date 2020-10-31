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
	 * @var Patches
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

		CLI::write('Using the following configuration:', 'light_cyan');
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


		// Display the merge menu, if user selects "quit" then abort
		if (! $this->mergeMenu())
		{
			CLI::write('Process aborted.', 'red');
			return $this->complete();
		}

		// Call the chosen merging method
		$this->patches->merge();

		// Display the conflicts menu, if user selects "quit" then abort
		if (! $this->conflictsMenu())
		{
			CLI::write('Process aborted.', 'red');
			return $this->complete();
		}

		return $this->complete();
	}

	/**
	 * Display and process the main menu
	 *
	 * @return bool  Whether or not to continue with merge
	 */
    protected function mergeMenu(): bool
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

		CLI::write('What would you like to do:', 'light_cyan');
		CLI::write('(P)roceed with the merge');
		CLI::write('(L)ist all files');
		CLI::write('Show (C)hanged files (' . $counts['changed'] . ')');
		CLI::write('Show (A)dded files (' . $counts['added'] . ')');
		CLI::write('Show (D)eleted files (' . $counts['deleted'] . ')');
		CLI::write('(Q)uit');

    	switch (CLI::prompt('Selection?', ['p', 'l', 'c', 'a', 'd', 'q']))
    	{
    		case 'p':
    			return true;

    		case 'q':
    			return false;

    		case 'l':
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
	 * Display and process the conflicts menu
	 *
	 * @return bool
	 */
    protected function conflictsMenu(): bool
    {
		$codex  = $this->patches->getCodex();
		$counts = [
			'changed' => count($codex->conflicts['changed']),
			'added' => count($codex->conflicts['added']),
			'deleted' => count($codex->conflicts['deleted']),
		];
		
		if (! array_sum($counts))
		{
			CLI::write('Skipping conflict resolution.', 'green');
			return true;
		}

		CLI::write('What would you like to do:', 'light_cyan');
		CLI::write('(L)ist conflict files');
		CLI::write('(G)uided resolution');
		CLI::write('(O)verwrite all files');
		CLI::write('(S)kip all files');
		CLI::write('(Q)uit');

    	switch (CLI::prompt('Selection?', ['l', 'g', 'o', 's', 'q']))
    	{
    		case 'q':
    		case 's':
    			return false;

    		case 'l':
    			$this->showFiles($codex->conflicts['changed'], 'Changed');
    			$this->showFiles($codex->conflicts['added'], 'Added');
    			$this->showFiles($codex->conflicts['deleted'], 'Deleted');
    		break;

    		case 'g':
    			foreach ($codex->conflicts as $status => $files)
    			{
	    			foreach ($files as $file)
    				{
    					if (! $this->resolveMenu($file, $status))
    					{
    						return false;
    					}
    				}
				}

    			return true;
    	}

    	// If a non-returning item was select then run the menu again
    	return $this->conflictsMenu();
    }

	/**
	 * Display and process the resolve menu
	 *
	 * @param string $file    Path to the file
	 * @param string $status  Changed/Added/Deleted
	 *
	 * @return bool  False to quit out of the whole process
	 */
    protected function resolveMenu(string $file, string $status): bool
    {
    	$codex = $this->patches->getCodex();

		CLI::write(lang('Patches.conflict' . ucfirst($status)));
		CLI::write($file);

		CLI::write('(D)isplay diff');
		CLI::write('(O)verwrite');
		CLI::write('(S)kip');
		CLI::write('(Q)uit');

    	switch (CLI::prompt('Selection?', ['d', 'o', 's', 'q']))
    	{
    		case 's':
    			return true;

    		case 'q':
    			return false;

    		case 'd':
    			CLI::write($this->patches->diffFile($file));
    			return $this->resolveMenu($file, $status);

    		case 'o':
				$current = $codex->workspace . 'current/' . $file;
				$project = $codex->config->rootPath . $file;

				if ($status == 'Deleted')
				{
					unlink($project);
				}
				// Copy over the existing file
				else
				{
					copy_path($current, $project);
				}
			break;
    	}

		return true;
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
    		// Get the number of changes
			$diff = $this->patches->diffFile($file);

    		$tbody[] = [$file, $status, $diff ? count(explode("\n", $diff)) : 0];
    	}

		CLI::table($tbody, $thead);

    	return $this;
    }

	/**
	 * Write out the Codex and quit.
	 *
	 * @return $this
	 */
    protected function complete(): self
    {
		// Write out the Codex
		$codex = $this->patches->getCodex();
		$codex->save();

		CLI::write('Workspace with codex and files:', 'light_cyan');
		CLI::write($codex->workspace);

		return $this;
	}
}
