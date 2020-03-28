<?php namespace Tatter\Patches;

use CodeIgniter\Config\BaseConfig;
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
		
		return $this;
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
	public function getHandlers(): array
	{
		$handlers = [];

		// Get potential handlers from all namespaces
		$locator = service('locator');
		foreach ($locator->listFiles('Patchers') as $file)
		{
			$classname = $locator->getClassname($file);
			$instance  = new $classname();

			if ($instance instanceof PatcherInterface)
			{
				$handlers[basename($file, '.php')] = $instance;
			}
		}

		// Remove any that are ignored
		foreach ($this->config->ignoredHandlers as $name)
		{
			unset($handlers[$name]);
		}

		$this->status('Detected handlers: ' . implode(', ', array_keys($handlers)));

		return $handlers;
	}

	/**
	 * Iterate through handlers and copy files to the workspace.
	 *
	 * @param $handlers  Array of handler instances
	 *
	 * @return array  File copied
	 */
	public function stageFiles(array $handlers): array
	{
		$paths = [];

		foreach ($handlers as $instance)
		{
			foreach ($instance->sources as $source)
			{
				// Get individual file paths
				$files = get_filenames($source['from']);

				// Copy each file to its relative destination
				foreach ($files as $file)
				{
					$path = $this->workspace . $source['to'] . $file;

					// Make sure the destination exists
					$dir = pathinfo($path, PATHINFO_DIRNAME);
					if (! file_exists($dir))
					{
						mkdir($dir, 0775, true);
					}

					// Copy the file, retaining the relative structure
					copy($source['from'] . $file, $path);
				}
			}
		}
	}
}
