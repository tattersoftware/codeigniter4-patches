<?php namespace Tatter\Patches;

use CodeIgniter\Events\Events;

/**
 * Class BaseSource
 *
 * Common functions for patch sources.
 *
 * @deprecated
 */
class BaseSource
{
	/**
	 * Whether files removed upstream should be deleted locally.
	 *
	 * @var bool
	 */
	public $delete = true;

	/**
	 * Array of paths to check during patching.
	 * Required: from (absolute), to (relative)
	 * Optional: exclude
	 *
	 * @var array
	 */
	public $paths = [];

	/**
	 * Register patch events.
	 */
	public function __construct()
	{
		foreach (['prepatch', 'postpatch'] as $event)
		{
			// Check for a corresponding method for this event
			if (method_exists($this, $event))
			{
				// Register the function for this event
				Events::on('prepatch', [$this, $event]);
			}
		}
	}
}
