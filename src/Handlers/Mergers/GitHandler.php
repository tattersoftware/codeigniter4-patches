<?php namespace Tatter\Patches\Handlers\Patchers;

use Tatter\Patches\Patches;
use Tatter\Patches\Interfaces\MergerInterface;

class GitHandler implements MergerInterface
{
	/**
	 * Compare each updated file with its prepatch equivalent.
	 * @param Patches $patches  Instance of the library to run against
	 */
	public function run(Patches &$patches)
	{
		
	}
}
