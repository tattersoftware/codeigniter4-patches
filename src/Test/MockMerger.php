<?php namespace Tatter\Patches\Test;

use Tatter\Patches\Codex;
use Tatter\Patches\Handlers\BaseHandler;
use Tatter\Patches\Interfaces\MergerInterface;

class MockMerger extends BaseHandler implements MergerInterface
{
	/**
	 * Do nothing.
	 */
	public function merge()
	{
	}
}
