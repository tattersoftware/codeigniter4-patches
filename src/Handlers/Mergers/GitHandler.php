<?php namespace Tatter\Patches\Handlers\Patchers;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Codex;
use Tatter\Patches\Handlers\BaseHandler;
use Tatter\Patches\Patches;
use Tatter\Patches\Interfaces\MergerInterface;

class GitHandler extends BaseHandler implements MergerInterface
{
	/**
	 * Compare each updated file with its prepatch equivalent.
	 *
	 * @param Codex $codex
	 */
	public function merge()
	{
		
	}
}
