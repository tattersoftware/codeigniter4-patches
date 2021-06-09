<?php namespace Tatter\Patches\Handlers;

use Tatter\Patches\Codex;

/**
 * @deprecated
 */
class BaseHandler
{
	/**
	 * The Codex to run against
	 *
	 * @var Codex
	 */
	public $codex;

	/**
	 * Initialize the handler with a Codex.
	 *
	 * @param Codex $codex
	 */
	public function __construct(Codex &$codex)
	{
		$this->codex = $codex;
	}
}
