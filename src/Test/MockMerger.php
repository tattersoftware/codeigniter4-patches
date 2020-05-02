<?php namespace Tatter\Patches\Test;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Codex;
use Tatter\Patches\Patches;
use Tatter\Patches\Interfaces\MergerInterface;

class MockMerger implements MergerInterface
{
	/**
	 * Do nothing.
	 *
	 * @param Codex $codex
	 */
	public function run(Codex &$codex)
	{
	}
}
