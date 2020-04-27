<?php namespace Tatter\Patches\Interfaces;

use Tatter\Patches\Patches;

interface MergerInterface
{
	/**
	 * Merge each path to its relative destination.
	 *
	 * @param Patches $patches  Instance of the library to run against
	 */
	public function run(&Patches $patches);
}
