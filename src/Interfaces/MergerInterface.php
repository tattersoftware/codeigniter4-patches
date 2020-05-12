<?php namespace Tatter\Patches\Interfaces;

use Tatter\Patches\Codex;

interface MergerInterface
{
	/**
	 * Initialize the handler with a Codex.
	 *
	 * @param Codex $codex
	 */
	public function __construct(Codex &$codex);

	/**
	 * Merge each path to its relative destination.
	 */
	public function merge();
}
