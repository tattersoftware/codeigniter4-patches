<?php namespace Tatter\Patches;

use CodeIgniter\Config\BaseConfig;
use JsonSerializable;

/**
 * Class Codex
 *
 * Portable record of per-patch settings and outcomes.
 */
class Codex implements JsonSerializable
{
	/**
	 * Config file to use.
	 *
	 * @var Tatter\Patches\Config\Patches
	 */
	public $config;

	/**
	 * Error messages
	 *
	 * @var array
	 */
	public $errors = [];

	/**
	 * Path to the working directory.
	 *
	 * @var string
	 */
	public $workspace = '';

	/**
	 * Array of relative paths from sources before updating
	 *
	 * @var array
	 */
	public $legacyFiles = [];

	/**
	 * Array of relative paths to files changed by updating
	 *
	 * @var array
	 */
	public $changedFiles = [];

	/**
	 * Array of relative paths to files added by updating
	 *
	 * @var array
	 */
	public $addedFiles = [];

	/**
	 * Array of relative paths to files deleted by updating
	 *
	 * @var array
	 */
	public $deletedFiles = [];

	/**
	 * Array of relative paths that were successfully merged
	 *
	 * @var array
	 */
	public $mergedFiles = [];

	/**
	 * Arrays of relative paths that caused conflict during merging
	 *
	 * @var array
	 */
	public $conflicts = [
		'changed' => [],
		'added'   => [],
		'deleted' => [],
	];

	/**
	 * Initialize the configuration.
	 *
	 * @param BaseConfig $config
	 */
	public function __construct(BaseConfig $config)
	{
		$this->config = $config;
	}

	/**
	 * Write out the codex to its workspace
	 *
	 * @return self
	 */
	public function save()
	{
		file_put_contents($this->workspace . 'codex.json', json_encode($this, JSON_PRETTY_PRINT));

		return $this;
	}

	/**
	 * Support for json_encode()
	 *
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
}
