<?php namespace Tatter\Patches\Patches;

use Tatter\Patches\BaseSource;
use Tatter\Patches\Interfaces\SourceInterface;

class Framework extends BaseSource implements SourceInterface
{
	/**
	 * Whether files removed upstream should be deleted locally.
	 *
	 * @var bool
	 */
	public $delete = true;

	/**
	 * Array of paths to check during patching.
	 * Required: from (absolute), to (relative)
	 * Optional: exclude
	 *
	 * @var array
	 */
	public $paths = [
		[
			'from'    => SYSTEMPATH . '../app',
			'to'      => 'app',
		],
		[
			'from'    => SYSTEMPATH . '../writable',
			'to'      => 'writeable',
		],
		[
			'from'    => SYSTEMPATH . '../public',
			'to'      => 'public',
		],
		[
			'from'    => SYSTEMPATH . '../spark',
			'to'      => '.',
		],
		[
			'from'    => SYSTEMPATH . '../env',
			'to'      => '.',
		],
		[
			'from'    => SYSTEMPATH . '../builds',
			'to'      => '.',
		],
	];
}
