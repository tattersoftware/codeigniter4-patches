<?php namespace Tatter\Patches\Interfaces;

use Tatter\Patches\Codex;

interface MergerInterface
{
	/**
	 * Merge each path to its relative destination.
	 *
	 * @param Codex $codex
	 */
	public function run(Codex &$codex);
}
