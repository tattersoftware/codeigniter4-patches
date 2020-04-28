<?php namespace Tatter\Patches\Test;

use Tatter\Patches\Patches;
use Tatter\Patches\Interfaces\MergerInterface;

class MockMerger implements MergerInterface
{
	/**
	 * Do nothing.
	 *
	 * @param Patches $patches  Instance of the library to run against
	 */
	public function run(Patches &$patches)
	{
	}
}
