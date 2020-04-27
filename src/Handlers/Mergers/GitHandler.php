<?php namespace Tatter\Patches\Handlers\Patchers;

use Tatter\Patches\BaseHandler;
use Tatter\Patches\Interfaces\PatcherInterface;

class GitHandler extends BaseHandler implements PatcherInterface
{
	/**
	 * Compare each updated file with its prepatch equivalent.
	 */
	public function run(string $target = null): array
	{
		
	}
}
