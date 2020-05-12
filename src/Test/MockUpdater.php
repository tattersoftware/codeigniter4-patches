<?php namespace Tatter\Patches\Test;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Codex;
use Tatter\Patches\Handlers\BaseHandler;
use Tatter\Patches\Exceptions\UpdateException;
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
	 * Array of vendor files to choose from for manipulations.
	 *
	 * @var array
	 */
	protected $vendorFiles;

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
	 * Initialize the handler and helper, set the count, and gather eligible files.
	 *
	 * @param Codex $codex
	 */
	public function __construct(Codex &$codex)
	{
		parent::__construct($codex);

		helper('text');

		$this->gatherFiles();

		// Determine how many changes to make
		if (! isset($this->codex->mockCount))
		{
			$count = count($this->vendorFiles);

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
	 * Manipulate random vendor files.
	 *
	 * @throws UpdateException
	 */
	public function update()
	{
		$this->changedFiles = $this->addedFiles = $this->deletedFiles = [];

		// Always add, change, and delete one file each
		$this->addFile();
		$this->changeFile();
		$this->deleteFile();

		// Do a random number of other operations
		for ($i = 0; $i < $this->codex->mockCount; $i++)
		{
			$file = $this->getFile();
			$rand = rand(1,4);

			// Add if no file was available
			if ($rand === 1 || empty($file))
			{
				$this->addFile();
			}
			// Delete
			elseif ($rand === 2)
			{
				$this->deleteFile($file);
			}
			// Change
			else
			{
				$this->changeFile($file);
			}
		}
	}

	/**
	 * Gather eligible files from the vendor directory
	 *
	 * @return $this
	 */
	protected function gatherFiles(): self
	{
		$this->vendorFiles = [];

		// Get individual filenames
		foreach (get_filenames($this->codex->config->rootPath . 'vendor', true, true) as $filename)
		{
			if (is_file($filename))
			{
				$this->vendorFiles[] = $filename;
			}
		}

		// If no files were gathered then assume something went wrong
		if (empty($this->vendorFiles))
		{
			throw new UpdateException('MockUpdater failed to locate any vendor files');
		}

		// Ignore metadata
		$this->vendorFiles = preg_grep('#vendor/autoload\.php#', $this->vendorFiles, PREG_GREP_INVERT);
		$this->vendorFiles = preg_grep('#vendor/bin/#', $this->vendorFiles, PREG_GREP_INVERT);
		$this->vendorFiles = preg_grep('#vendor/composer/#', $this->vendorFiles, PREG_GREP_INVERT);

		return $this;
	}

	/**
	 * Get a random file to work on.
	 *
	 * @param bool $untouched  Whether to limit to files that have already been touched
	 *
	 * @return string|null  Path to the file, or null if no files are available
	 */
	protected function getFile($untouched = true): ?string
	{
		// Limit to files not changed or deleted
		$files = $untouched ? array_diff($this->vendorFiles, $this->changedFiles, $this->deletedFiles) : $this->vendorFiles;

		if (empty($files))
		{
			return null;
		}

		return $files[array_rand($files)];
	}

	/**
	 * Ensure a file and directory exists then fill it with random contents.
	 *
	 * @param string|null $file      Path to the file
	 * @param string|null $contents  Contents of the new file
	 */
	public function addFile(string $file = null, string $contents = null)
	{
		// If no file was specified then make one in a random directory
		$file     = $file ?? pathinfo($this->getFile(false), PATHINFO_DIRNAME) . '/' . random_string() . '.' . random_string('alpha', 3);
		$contents = $contents ?? bin2hex(random_bytes(128));

		// Make sure the destination directory exists
		ensure_file_dir($file);
		file_put_contents($file, $contents);

		$this->addedFiles[] = $file;
	}

	/**
	 * Change the contents of a random file.
	 *
	 * @param string|null $file      Path to the existing file
	 * @param string|null $contents  Contents of the new file
	 */
	public function changeFile(string $file = null, string $contents = null)
	{
		$file     = $file ?? $this->getFile();
		$contents = $contents ?? bin2hex(random_bytes(128));

		if (! is_file($file))
		{
			throw new UpdateException('Attempt to modify invalid file: ' . $file);
		}

		file_put_contents($file, $contents);

		$this->changedFiles[] = $file;
	}

	/**
	 * Delete a file.
	 *
	 * @param string|null $file  Path to the existing file
	 */
	public function deleteFile(string $file = null)
	{
		$file = $file ?? $this->getFile();

		if (! is_file($file))
		{
			throw new UpdateException('Attempt to delete invalid file: ' . $file);
		}

		unlink($file);
		$this->deletedFiles[] = $file;
	}
}
