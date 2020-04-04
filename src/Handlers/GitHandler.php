<?php namespace Tatter\Patches\Handlers;

use Tatter\Patches\BaseHandler;
use Tatter\Patches\Interfaces\HandlerInterface;

class GitHandler extends BaseHandler implements HandlerInterface
{
	/**
	 * Compare each updated file with its prepatch equivalent.
	 */
	public function patch()
	{
		
	}
}
