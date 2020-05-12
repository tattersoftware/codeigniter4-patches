<?php namespace Tatter\Patches\Test;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Codex;
use Tatter\Patches\Handlers\BaseHandler;
use Tatter\Patches\Exception\UpdateException;
use Tatter\Patches\Interfaces\UpdaterInterface;

/**
 * Class MockUpdater
 *
 * Manipulates random files from vendor to simulate running
 * an update. Tracks changes so they are available for comparison.
 */
class MockUpdater extends BaseHandler implements UpdaterInterface
{
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
	 * Initialize the handler with a Codex.
	 *
	 * @param Codex $codex
	 */
	public function __construct(Codex &$codex)
	{
		parent::__construct($codex);

		// Determine how many changes to make
		if (! isset($this->codex->mockCount))
		{
			$count = count($this->codex->legacyFiles);

			if ($count < 10)
			{
				$this->codex->mockCount = ceil($count / 2) + 1;
			}
			elseif ($count < 25)
			{
				$this->codex->mockCount = ceil($count / 3);
			}
			elseif ($count < 50)
			{
				$this->codex->mockCount = ceil($count / 4);
			}
			else
			{
				$this->codex->mockCount = 20;
			}
		}
	}

	/**
	 * Manipulate random files in vendor.
	 *
	 * @throws UpdateException
	 */
	public function update()
	{
		$this->changedFiles = $this->addedFiles = $this->deletedFiles = [];

		for ($i = 0; $i < $this->codex->mockCount; $i++)
		{
			switch (rand(1,3))
			{
				// Change
				case 1:
					$file = $this->getFile();

					$this->fillFile($this->codex->config->rootPath . 'vendor/' . $file);

					$this->changedFiles[] = $file;
				break;

				// Add
				case 2:
					$this->fillFile($this->codex->config->rootPath . 'vendor/' . $file);

					$this->addedFiles[] = $file;
				break;

				// Delete
				case 3:
					unlink($this->codex->config->rootPath . 'vendor/' . $file);

					$this->deletedFiles[] = $file;
				break;
			}
		}
	}

	/**
	 * Get a random file to work on.
	 *
	 * @return string|null  Path to the file, or null if no files are available
	 */
	protected function getFile(): ?string
	{
		// Ignore deleted files
		$files = array_diff($this->codex->legacyFiles, $this->deletedFiles);

		if (empty($files))
		{
			return null;
		}

		return $files[array_rand($files)];
	}

	/**
	 * Ensure a file and directory exists then fill it with random contents.
	 *
	 * @param string $file  Path to the file
	 */
	protected function fillFile(string $file)
	{
		// Make sure the destination directory exists
		ensure_file_dir($file);
		file_put_contents($file, bin2hex(random_bytes(128)));
	}
}
