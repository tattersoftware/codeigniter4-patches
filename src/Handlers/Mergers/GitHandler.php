<?php namespace Tatter\Patches\Handlers\Patchers;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Codex;
use Tatter\Patches\Patches;
use Tatter\Patches\Interfaces\MergerInterface;

class GitHandler implements MergerInterface
{
	/**
	 * Compare each updated file with its prepatch equivalent.
	 *
	 * @param Codex $codex
	 */
	public function run(Codex &$codex)
	{
		
	}
}
