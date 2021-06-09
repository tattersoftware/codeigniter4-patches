<?php namespace Tatter\Patches\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * @deprecated
 */
class Patches extends BaseConfig
{
	/**
	 * Full class name of the update handler to use
	 *
	 * @var string
	 */
	public $updater = 'Tatter\Patches\Handlers\Updaters\ComposerHandler';

	/**
	 * Full class name of the merge handler to use
	 *
	 * @var string
	 */
	public $merger = 'Tatter\Patches\Handlers\Mergers\CopyHandler';

	/**
	 * Path to the base directory for workspaces.
	 *
	 * @var string
	 */
	public $basePath = WRITEPATH . 'patches';

	/**
	 * Path to the project to patch.
	 *
	 * @var string
	 */
	public $rootPath = ROOTPATH;

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
	 * Array of patch source names to ignore. E.g. ['Framework']
	 *
	 * @var array
	 */
	public $ignoredSources = [];
}
