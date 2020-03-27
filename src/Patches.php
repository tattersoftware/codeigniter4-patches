<?php namespace Tatter\Patches;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\BaseHandler;

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
	protected $path;

	/**
	 * Initialize the configuration and directories.
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(BaseConfig $config = null)
	{
		$this->config = $config ?? config('Patches');

		$this->setPath();
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
	 * Set the path to the working directory and ensure it exists.
	 *
	 * @param string|null $path  Path the directory, defaults to the config value
	 *
	 * @return $this
	 */
	public function setPath(string $path = null): self
	{
		if ($path)
		{
			$this->path = rtrim($path, '/') . '/';
		}
		else
		{
			$this->path = rtrim($this->config->basePath, '/') . '/' . date('Y-m-d-His') . '/';
		}

		// Ensure the directory exists
		if (! is_dir($this->path))
		{
			mkdir($this->path, 0775, true);
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
			$name =    basename($file, '.php');
			$className = $locator->getClassname($file);
			$instance  = new $classname();

			if ($instance instanceof '\Tatter\Patches\Interfaces\PatcherInterface')
			{
				$handlers[$name] = $instance;
			}
		}

		return $handlers;
	}
}
