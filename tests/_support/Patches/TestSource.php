<?php namespace Tests\Support\Patches;

use Tatter\Patches\BaseSource;
use Tatter\Patches\Interfaces\SourceInterface;;

class TestSource extends BaseSource implements SourceInterface
{
	/**
	 * Whether files removed upstream should be deleted locally.
	 *
	 * @var bool
	 */
	public $delete = true;

	/**
	 * Array of paths to check during patching.
	 * Required: from, to
	 * Optional: exclude
	 *
	 * @var array
	 */
	public $paths = [
		[
			'from'    => SUPPORTPATH . 'files',
			'to'      => 'tester',
		],
	];

	/**
	 * Run a simulate prepatch event.
	 *
	 * @param array $legacyFiles  Legacy files staged by beforeUpdate
	 *
	 * @return bool  Whether or not the event succeeded
	 */
	public function prepatch(array $legacyFiles): bool
	{
		$GLOBALS['testSourceDidPrepatch'] = true;

		return true;
	}
}
