<?php namespace Tatter\Patches;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseConfig;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Tatter\Patches\BaseHandler;
use Tatter\Patches\Interfaces\PatcherInterface;

/**
 * Class Patches
 *
 * Library to interface with patch handlers for project updates.
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
	 * Array of handler instances
	 *
	 * @var array
	 */
	protected $handlers = [];

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
		$this->gatherHandlers();
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
	 * Display or log a status message.
	 *
	 * @param string $message  The status message
	 * @param bool $error      Whether this is an error message
	 *
	 * @return $this
	 */
	public function status(string $message, bool $error = false): self
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
	 * Locate and load handlers by their name.
	 * Priority: App, other modules, Tatter\Patches
	 *
	 * @return array  $name => $instance
	 */
	protected function gatherHandlers(): self
	{
		$this->handlers = [];

		// Get potential handlers from all namespaces
		$locator = service('locator');
		foreach ($locator->listFiles('Patchers') as $file)
		{
			$classname = $locator->getClassname($file);
			$instance  = new $classname();

			if ($instance instanceof PatcherInterface)
			{
				$this->handlers[basename($file, '.php')] = $instance;
			}
		}

		// Remove any that are ignored
		foreach ($this->config->ignoredHandlers as $name)
		{
			unset($this->handlers[$name]);
		}

		$this->status('Detected handlers: ' . implode(', ', array_keys($this->handlers)));

		return $this;
	}

	/**
	 * Return loaded handlers.
	 *
	 * @return array  $name => $instance
	 */
	public function getHandlers(): array
	{
		return $this->handlers;
	}

	/**
	 * Iterate through handlers and copy files to the workspace.
	 *
	 * @return array  Files copied
	 */
	public function stageFiles(): array
	{
		$paths = [];

		foreach ($this->handlers as $instance)
		{
			foreach ($instance->sources as $source)
			{
				if (! file_exists($source['from']))
				{
					continue;
				}

				$source['to'] = rtrim($source['to'], '/') . '/';

				if (is_dir($source['from']))
				{
					$source['from'] = rtrim(realpath($source['from']), '/') . '/';

					// Get individual file paths
					$files = get_filenames($source['from'], true);
				}
				else
				{
					$files = [$source['from']];
				}

				// Copy each file to its relative destination
				foreach ($files as $file)
				{
					$path = $this->workspace . $source['to'] . str_replace($source['from'], '', $file);

					if (is_file($source['from']))
					{
						$path .= pathinfo($source['from'], PATHINFO_BASENAME);
					}

					// Make sure the destination exists
					$dir = pathinfo($path, PATHINFO_DIRNAME);
					if (! file_exists($dir))
					{
						mkdir($dir, 0775, true);
					}

					// Copy the file, retaining the relative structure
					copy($file, $path);

					$paths[] = $path;
				}
			}
		}

		return $paths;
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
			return true;
		}

		$result = true;

		foreach ($this->handlers as $name => $instance)
		{
			if (method_exists($instance, 'prepatch'))
			{
				$result = $result && $instanace->prepatch();
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
		$input       = new ArrayInput([
			'command'       => 'update',
			'--working-dir' => $this->config->composer,
		]);

		// Prevent $application->run() from exiting the script
		$application->setAutoExit(false);

		// Returns int 0 if everything went fine, or an error code
		$result = $application->run($input);

		if ($result !== 0)
		{
			$this->status('Composer failed with error code ' . $result);
			return false;
		}

		return true;
	}
}
