<?php namespace Tatter\Patches\Config;

use CodeIgniter\Config\BaseConfig;

class Patches extends BaseConfig
{
	/**
	 * Path to the working directory for patch files.
	 *
	 * @var string
	 */
	public $basePath = WRITEPATH . 'patches';

	/**
	 * Whether handlers may run their post-patch commands.
	 *
	 * @var bool
	 */
	public $allowCommands = true;

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
}
