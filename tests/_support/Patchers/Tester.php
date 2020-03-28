<?php namespace Tests\Support\Patchers;

use Tatter\Patches\Interfaces\PatcherInterface;

class Tester implements PatcherInterface
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
	 * Required: from, to
	 * Optional: exclude
	 *
	 * @var array
	 */
	public $sources = [
		[
			'from'    => SUPPORTPATH . 'files',
			'to'      => VIRTUALPATH,
		],
	];
}
