<?php namespace Tatter\Patches\Config;

use CodeIgniter\Config\BaseConfig;

class Patches extends BaseConfig
{
	/**
	 * Path to the base directory for workspaces.
	 *
	 * @var string
	 */
	public $basePath = WRITEPATH . 'patches';

	/**
	 * Path to the directory containing composer.json.
	 *
	 * @var string
	 */
	public $composer = ROOTPATH;

	/**
	 * Whether files removed upstream may be deleted locally.
	 * Overrides individual handler settings.
	 *
	 * @var bool
	 */
	public $allowDeletes = true;

	/**
	 * Whether handlers may run their prepatch() and postpatch() methods.
	 *
	 * @var bool
	 */
	public $allowEvents = true;

	/**
	 * Array of patch handler names to ignore. E.g. ['Framework']
	 *
	 * @var array
	 */
	public $ignoredHandlers = [];
}
