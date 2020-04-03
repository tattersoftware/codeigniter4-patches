<?php namespace Tatter\Patches\Patches;

use Tatter\Patches\Interfaces\SourceInterface;

class Framework implements SourceInterface
{
	/**
	 * Whether files removed upstream should be deleted locally.
	 *
	 * @var bool
	 */
	public $delete = true;

	/**
	 * Method to use when patching files into the project:
	 * 'copy' : Copies files based on their hash, ignores conflicts
	 * 'git'  : Uses a temporary repository to merge changes
	 *
	 * @var string
	 */
	public $method = 'copy';

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
