<?php namespace Tatter\Patches;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Tatter\Patches\Interfaces\SourceInterface;

/**
 * Class BaseHandler
 *
 * Common functions to interface with patch definitions for project updates.
 */
class BaseHandler
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
	 * @var Tatter\Patches\Config\Patches
	 */
	protected $workspace;

	/**
	 * Initialize the configuration and directories.
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(BaseConfig $config = null)
	{
		helper('filesystem');

		$this->config = $config ?? config('Patches');

		$this->setWorkspace();
		$this->gatherSources();
	}

	/**
	 * Execute a patch request.
	 *
	 * @return bool  Whether or not the patch succeeded
	 */
	protected function run(): bool
	{
		$result = true;

		$this->status('Patch initiated, handlers: ' . implode(', ', array_keys($this->handlers)));

		// Stage the files
		$paths = $this->stageFiles();
		$s     = $paths == 1 ? '' : 's';
		$this->status(count($paths) . "file{$s} staged for patching");

		// Run the prepatch events
		if (! $this->config->allowEvents)
		{
			$this->status('Skipping prepatch event');
		}
		elseif ($this->prepatch())
		{
			$this->status('Handler prepatch event successful');
		}
		else
		{
			$this->status('Handler prepatch event failed', true);
		}
		
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
			$classname = $locator->getClassname($file);
			$instance  = new $classname();

			if ($instance instanceof SourceInterface)
			{
				$this->sources[basename($file, '.php')] = $instance;
			}
		}

		// Remove any that are ignored
		foreach ($this->config->ignoredSources as $name)
		{
			unset($this->sources[$name]);
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
					$path['from'] = rtrim(realpath($path['from']), '/') . '/';

					// Get individual filenames
					foreach (get_filenames($path['from'], true) as $filename)
					{
						$paths[] = [
							'from' => $filename,
							'to'   => $path['to'] . str_replace($path['from'], '', $filename),
						];
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
	 * Iterate through each source and copy files to the workspace.
	 *
	 * @return array  Array of the new files
	 */
	public function stageFiles(): array
	{
		$filenames = [];

		// Copy each file to its relative destination
		foreach ($this->gatherPaths() as $path)
		{
			$filename = $this->workspace . $path['to'];

			// Make sure the destination directory exists
			$dir = pathinfo($filename, PATHINFO_DIRNAME);
			if (! file_exists($dir))
			{
				mkdir($dir, 0775, true);
			}

			// Copy the file, retaining the relative structure
			copy($path['from'], $filename);

			$filenames[] = $filename;
		}

		return $filenames;
	}

	/**
	 * Run handler prepatch methods (if enabled)
	 *
	 * @return bool  True if all events succeed
	 */
	public function prepatch(): bool
	{
		if (! $this->config->allowEvents)
		{
			return false;
		}

		$result = true;

		foreach ($this->sources as $name => $instance)
		{
			if (method_exists($instance, 'prepatch'))
			{
				if (! $instanace->prepatch())
				{
					$this->status('Failed to run prepatch event for ' . $name, true);
					$result = false;
				}
			}
		}
		
		return $result;
	}

	/**
	 * Call Composer programmatically to update all vendor files
	 * https://stackoverflow.com/questions/17219436/run-composer-with-a-php-script-in-browser#25208897
	 *
	 * @return bool  True if the update succeeds
	 */
	public function composer(): bool
	{
		$application = new Application();
		$params      = [
			'command'       => 'update',
			'--working-dir' => $this->config->composer,
		];
		
		// Suppress Composer output during testing
		if (ENVIRONMENT === 'testing')
		{
			$params['--quiet'] = true;
		}
		else
		{
			$params['--verbose'] = true;
		}

		$input = new ArrayInput($params);

		// Prevent $application->run() from exiting the script
		$application->setAutoExit(false);

		// Returns int 0 if everything went fine, or an error code
		$result = $application->run($input);

		if ($result !== 0)
		{
			$this->status('Composer failed with error code ' . $result, true);
			return false;
		}

		return true;
	}
}
