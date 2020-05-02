<?php namespace Tatter\Patches\Interfaces;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Codex;
use Tatter\Patches\Patches;

interface MergerInterface
{
	/**
	 * Merge each path to its relative destination.
	 *
	 * @param Codex $codex
	 */
	public function run(Codex &$codex);
}
