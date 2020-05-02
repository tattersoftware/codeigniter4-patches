<?php namespace Tatter\Patches\Interfaces;

use Tatter\Patches\Codex;

interface UpdaterInterface
{
	/**
	 * Update vendor packages.
	 *
	 * @param Codex $codex
	 */
	public function run(Codex &$codex);
}
