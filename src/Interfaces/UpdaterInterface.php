<?php namespace Tatter\Patches\Interfaces;

use Tatter\Patches\Codex;

interface UpdaterInterface
{
	/**
	 * Initialize the handler with a Codex.
	 *
	 * @param Codex $codex
	 */
	public function __construct(Codex &$codex);

	/**
	 * Update vendor packages.
	 */
	public function run();
}
