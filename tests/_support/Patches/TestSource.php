<?php namespace Tests\Support\Patches;

use Tatter\Patches\Interfaces\SourceInterface;;

class TestSource implements SourceInterface
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
	public $paths = [
		[
			'from'    => SUPPORTPATH . 'files',
			'to'      => 'tester',
		],
	];
}
