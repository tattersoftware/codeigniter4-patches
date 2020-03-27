<?php namespace Tatter\Patches\Patchers;

use Tatter\Patches\Interfaces\PatcherInterface;

class Framework implements PatcherInterface
{
	/**
	 * Array of paths to check during patching.
	 * Requires: from, to
	 * Optional: exclude
	 *
	 * @var array
	 */
	public $paths = [
		[
			'from'    => SYSTEMPATH . '../app',
			'to'      => APPPATH,
		],
		[
			'from'    => SYSTEMPATH . '../writable',
			'to'      => WRITEPATH,
		],
		[
			'from'    => SYSTEMPATH . '../public',
			'to'      => FCPATH,
		],
		[
			'from'    => SYSTEMPATH . '../spark',
			'to'      => ROOTPATH,
		],
		[
			'from'    => SYSTEMPATH . '../env',
			'to'      => ROOTPATH,
		],
	];
}