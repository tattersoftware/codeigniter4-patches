<?php namespace Tatter\Patches;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Events\Events;
use Tatter\Patches\Exceptions\ExceptionInterface;
use Tatter\Patches\Interfaces\SourceInterface;

/**
 * Class Patches
 *
 * Library to implement patch sources for project updates.
 */
class Patches
{
	/**
	 * Config file to use.
	 *
	 * @var Tatter\Patches\Config\Patches
	 */
	protected $config;

	/**
	 * Error messages from the last call
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Array of source instances
	 *
	 * @var array
	 */
	protected $sources = [];

	/**
	 * Path to the working directory.
	 *
	 * @var string
	 */
	protected $workspace;

	/**
	 * Array of relative paths from sources before updating
	 *
	 * @var array|null
	 */
	public $legacyFiles;

	/**
	 * Array of relative paths to files changed by updating
	 *
	 * @var array|null
	 */
	public $changedFiles;

	/**
	 * Array of relative paths to files added by updating
	 *
	 * @var array|null
	 */
	public $addedFiles;

	/**
	 * Array of relative paths to files deleted by updating
	 *
	 * @var array|null
	 */
	public $deletedFiles;

	/**
	 * Array of relative paths that caused a conflict during patching
	 *
	 * @var array|null
	 */
	public $conflictFiles;

	/**
	 * Initialize the configuration and directories.
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(BaseConfig $config = null)
	{
		helper(['filesystem', 'patches']);

		$this->config = $config ?? config('Patches');

		$this->setWorkspace();
		$this->gatherSources();
	}

	/**
	 * Run through the entire patch process.
	 *
	 * @return bool  Whether or not the patch succeeded
	 */
	public function run(): bool
	{
		$result = true;

		// Copy legacy files and trigger the prepatch event
		$this->beforeUpdate();

		// Update the vendor packages
		$this->update();

		// Check for and copy updated files
		$this->afterUpdate();

		// Call the chosen merging method
		$this->merge();

		return $result;
	}

	/**
	 * Display or log a status message.
	 *
	 * @param string $message  The status message
	 * @param bool $error      Whether this is an error message
	 *
	 * @return $this
	 */
	protected function status(string $message, bool $error = false): self
	{
		// Always log it
		log_message($error ? 'error' : 'debug', $message);

		// For CLI calls write the output
		if (is_cli() && ENVIRONMENT !== 'testing')
		{
			CLI::write($message, $error ? 'red' : 'white');
		}
		
		// If it was an error then store it
		if ($error)
		{
			$this->errors[] = $error;
		}

		return $this;
	}

	/**
	 * Get and clear any error messsages
	 *
	 * @return array  Any error messages from the last call
	 */
	public function getErrors(): array
	{
		$errors       = $this->errors;
		$this->errors = [];

		return $errors;
	}
	
	/**
	 * Return the path to the working directory.
	 *
	 * @return string
	 */
	public function getWorkspace(): string
	{
		return $this->workspace;
	}
	
	/**
	 * Set the path to the working directory and ensure it exists.
	 *
	 * @param string|null $path  Path the directory, defaults to the config value
	 *
	 * @return $this
	 */
	public function setWorkspace(string $path = null): self
	{
		if ($path)
		{
			$this->workspace = rtrim($path, '/') . '/';
		}
		else
		{
			$this->workspace = rtrim($this->config->basePath, '/') . '/' . date('Y-m-d-His') . '/';
		}

		// Ensure the directory exists
		if (! is_dir($this->workspace))
		{
			mkdir($this->workspace, 0775, true);
		}

		return $this;
	}

	/**
	 * Locate and load sources by their name.
	 * Priority: App, other modules, Tatter\Patches
	 *
	 * @return array  $name => $instance
	 */
	protected function gatherSources(): self
	{
		$this->sources = [];

		// Get potential sources from all namespaces
		$locator = service('locator');
		foreach ($locator->listFiles('Patches') as $file)
		{
			$name = basename($file, '.php');

			if (in_array($name, $this->config->ignoredSources))
			{
				continue;
			}

			$classname = $locator->getClassname($file);
			$instance  = new $classname();

			if ($instance instanceof SourceInterface)
			{
				$this->sources[$name] = $instance;
			}
		}

		$this->status('Detected sources: ' . implode(', ', array_keys($this->sources)));

		return $this;
	}

	/**
	 * Return loaded sources.
	 *
	 * @return array  $name => $instance
	 */
	public function getSources(): array
	{
		return $this->sources;
	}

	/**
	 * Iterate through sources and gather file paths.
	 *
	 * @return array  Actual file paths as [from => $fullPath, to => $relativePath]
	 */
	public function gatherPaths(): array
	{
		$paths = [];

		foreach ($this->sources as $instance)
		{
			foreach ($instance->paths as $path)
			{
				$path['to'] = rtrim($path['to'], '/') . '/';

				if (is_dir($path['from']))
				{
					$path['from'] = realpath($path['from']) ?: $path['from'];
					$path['from'] = rtrim($path['from'], '/') . '/';

					// Get individual filenames
					foreach (get_filenames($path['from'], null, true) as $filename)
					{
						if (is_file($path['from'] . $filename))
						{
							$paths[] = [
								'from' => $path['from'] . $filename,
								'to'   => $path['to'] . $filename,
							];
						}
					}
				}
				elseif (is_file($path['from']))
				{
					$paths[] = [
						'from' => $path['from'],
						'to'   => $path['to'] . str_replace($path['from'], '', $filename) . pathinfo($path['from'], PATHINFO_BASENAME),
					];
				}
			}
		}

		return $paths;
	}

	/**
	 * Copy each path to its relative destination.
	 *
	 * @param string $destination  Directory to copy files into
	 *
	 * @return array  Array of the actual files copied, relative paths
	 */
	public function copyPaths(string $destination): array
	{
		$files = [];
		$destination = rtrim($destination, '/') . '/';

		// Copy each file to its relative destination
		foreach ($this->gatherPaths() as $path)
		{
			$filename = $destination . $path['to'];

			if (copy_path($path['from'], $filename))
			{
				$files[] = $path['to'];
			}
		}

		return $files;
	}

	/**
	 * Copy legacy files and trigger the prepatch event.
	 *
	 * @return $this
	 */
	public function beforeUpdate(): self
	{
		$destination = $this->workspace . 'legacy/';

		// Copy the prepatch files and store the list
		$this->legacyFiles = $this->copyPaths($destination);

		$s = $this->legacyFiles == 1 ? '' : 's';
		$this->status(count($this->legacyFiles) . " legacy file{$s} copied to {$destination}");

		// If events are allowed then trigger prepatch
		if ($this->config->allowEvents)
		{
			// Prepatch events receive the array of legacy files
			Events::trigger('prepatch', $destination, $this->legacyFiles);
		}
		else
		{
			$this->status('Skipping prepatch event');
		}

		return $this;
	}

	/**
	 * Call the chosen handler to update vendor sources.
	 *
	 * @return bool  True if the update succeeds
	 */
	public function update(): bool
	{
		$updater = new $this->config->updater();

		try
		{
			$updater->run();
		}
		catch (ExceptionInterface $e)
		{
			$this->status($e->getMessage(), true);
			return false;
		}

		return true;
	}

	/**
	 * Copy updated files and filter unneeded legacy files
	 *
	 * @return $this
	 */
	public function afterUpdate(): self
	{
		$this->changedFiles = [];
		$this->addedFiles   = [];
		$this->deletedFiles = [];
		$unchangedFiles     = [];

		// Copy any files that were changed during the update
		foreach ($this->gatherPaths() as $path)
		{
			$legacy = $this->workspace . 'legacy/'  . $path['to'];

			// If the file is new or changed then copy it
			if (! same_file($legacy, $path['from']))
			{
				if (copy_path($path['from'], $this->workspace . 'current/' . $path['to']))
				{
					// Add it to the appropriate list
					if (is_file($legacy))
					{
						$this->changedFiles[] = $path['to'];
					}
					else
					{
						$this->addedFiles[] = $path['to'];
					}
				}
			}
			// If the file remained the same then remove the legacy copy
			elseif (is_file($legacy))
			{
				$unchangedFiles[] = $path['to'];
				unlink($legacy);
			}
		}

		// Update the array of legacy files to match the new filtered list
		$this->legacyFiles = array_diff($this->legacyFiles, $unchangedFiles);

		$s = $this->changedFiles == 1 ? '' : 's';
		$this->status(count($this->changedFiles) . " changed file{$s} detected");

		// Check for files that have been deleted
		$this->deletedFiles = array_diff($this->legacyFiles, $this->changedFiles);

		$s = $this->deletedFiles == 1 ? '' : 's';
		$this->status(count($this->deletedFiles) . " deleted file{$s} detected");

		return $this;
	}

	/**
	 * Run the patch handler and call the postpatch event (as needed)
	 *
	 * @return $this
	 */
	public function merge(): array
	{
		// Ensure a trailing slash on the destination
		$this->config->destination = rtrim($this->config->destination, '/') . '/';

		$merger = new $this->config->merger();

		try
		{
			$merger->run($this);
		}
		catch (ExceptionInterface $e)
		{
			$this->status($e->getMessage(), true);
			return false;
		}
		
		$s = $this->patchedFiles == 1 ? '' : 's';
		$this->status(count($this->patchedFiles) . "file{$s} patched");

		// If events are allowed then trigger postpatch
		if ($this->config->allowEvents)
		{
			// Postpatch events receive the array of patched files
			Events::trigger('postpatch', $this->patchedFiles);
		}
		else
		{
			$this->status('Skipping postpatch event');
		}
	}
}
