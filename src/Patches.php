<?php namespace Tatter\Patches;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Events\Events;
use Tatter\Patches\Codex;
use Tatter\Patches\Exceptions\ExceptionInterface;
use Tatter\Patches\Interfaces\MergerInterface;
use Tatter\Patches\Interfaces\SourceInterface;
use Tatter\Patches\Interfaces\UpdaterInterface;

/**
 * Class Patches
 *
 * Library to implement patch sources for project updates.
 */
class Patches
{
	/**
	 * Per-run record
	 *
	 * @var Codex
	 */
	protected $codex;

	/**
	 * Array of source instances
	 *
	 * @var array
	 */
	protected $sources = [];

	/**
	 * Instance of the update handler.
	 *
	 * @var Tatter\Patches\Interfaces\UpdaterInterface
	 */
	protected $updater;

	/**
	 * Instance of the merger handler.
	 *
	 * @var Tatter\Patches\Interfaces\MergerInterface
	 */
	protected $merger;

	/**
	 * Initialize the configuration and directories.
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(BaseConfig $config = null)
	{
		helper(['filesystem', 'patches']);

		$this->codex = new Codex($config ?? config('Patches'));

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
			$this->codex->errors[] = $error;
		}

		return $this;
	}

	/**
	 * Return the Codex.
	 *
	 * @return Codex
	 */
	public function getCodex(): Codex
	{
		return $this->codex;
	}

	/**
	 * Get and clear any error messsages
	 *
	 * @return array  Any error messages from the last call
	 */
	public function getErrors(): array
	{
		$errors = $this->codex->errors;

		$this->codex->errors = [];

		return $errors;
	}

	/**
	 * Get the update handler; mostly for testing.
	 *
	 * @return UpdaterInterface
	 */
	public function getUpdater(): UpdaterInterface
	{
		return $this->updater;
	}

	/**
	 * Get the merge handler; mostly for testing.
	 *
	 * @return MergerInterface
	 */
	public function getMerger(): MergerInterface
	{
		return $this->merger;
	}
	
	/**
	 * Return the path to the working directory.
	 *
	 * @return string
	 */
	public function getWorkspace(): string
	{
		return $this->codex->workspace;
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
			$this->codex->workspace = rtrim($path, '/') . '/';
		}
		else
		{
			$this->codex->workspace = rtrim($this->codex->config->basePath, '/') . '/' . date('Y-m-d-His') . '/';
		}

		// Ensure the directory exists
		if (! is_dir($this->codex->workspace))
		{
			mkdir($this->codex->workspace, 0775, true);
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

			if (in_array($name, $this->codex->config->ignoredSources))
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
		$destination = $this->codex->workspace . 'legacy/';

		// Copy the prepatch files and store the list
		$this->codex->legacyFiles = $this->copyPaths($destination);

		$s = $this->codex->legacyFiles == 1 ? '' : 's';
		$this->status(count($this->codex->legacyFiles) . " legacy file{$s} copied to {$destination}");

		// If events are allowed then trigger prepatch
		if ($this->codex->config->allowEvents)
		{
			// Prepatch events receive the array of legacy files
			Events::trigger('prepatch', $destination, $this->codex->legacyFiles);
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
		$this->updater = new $this->config->updater();

		try
		{
			$this->updater->run($this->codex);
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
		$unchangedFiles = [];

		// Copy any files that were changed during the update
		foreach ($this->gatherPaths() as $path)
		{
			$legacy = $this->codex->workspace . 'legacy/'  . $path['to'];

			// If the file is new or changed then copy it
			if (! same_file($legacy, $path['from']))
			{
				if (copy_path($path['from'], $this->codex->workspace . 'current/' . $path['to']))
				{
					// Add it to the appropriate list
					if (is_file($legacy))
					{
						$this->codex->changedFiles[] = $path['to'];
					}
					else
					{
						$this->codex->addedFiles[] = $path['to'];
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
		$this->codex->legacyFiles = array_diff($this->codex->legacyFiles, $unchangedFiles);

		$s = $this->codex->changedFiles == 1 ? '' : 's';
		$this->status(count($this->codex->changedFiles) . " changed file{$s} detected");

		// Check for files that have been deleted
		$this->codex->deletedFiles = array_diff($this->codex->legacyFiles, $this->codex->changedFiles);

		$s = $this->codex->deletedFiles == 1 ? '' : 's';
		$this->status(count($this->codex->deletedFiles) . " deleted file{$s} detected");

		return $this;
	}

	/**
	 * Run the patch handler and call the postpatch event (as needed)
	 *
	 * @return bool  Success or failure
	 */
	public function merge(): bool
	{
		// Ensure a trailing slash on the destination
		$this->codex->config->destination = rtrim($this->codex->config->destination, '/') . '/';

		$this->merger = new $this->codex->config->merger();

		try
		{
			$this->merger->run($this->codex);
		}
		catch (ExceptionInterface $e)
		{
			$this->status($e->getMessage(), true);
			return false;
		}
		
		$s = $this->codex->mergedFiles == 1 ? '' : 's';
		$this->status(count($this->codex->mergedFiles) . "file{$s} merged");

		// If events are allowed then trigger postpatch
		if ($this->codex->config->allowEvents)
		{
			// Postpatch events receive the array of merged files
			Events::trigger('postpatch', $this->codex->mergedFiles);
		}
		else
		{
			$this->status('Skipping postpatch event');
		}

		return true;
	}
}
