<?php namespace Tatter\Patches\Config;

use CodeIgniter\Config\BaseService;
use Tatter\Patches\Interfaces\HandlerInterface;

class Services extends BaseService
{
	/**
	 * Returns a configured patch handler
	 *
	 * @param string   $handler  The patch handler to use
	 * @param Config   $config
	 * @param boolean  $getShared
	 *
	 * @return Tatter\Patches\Interfaces\HandlerInterface
	 */
	public static function patches(string $handler = null, $config = null, bool $getShared = true): HandlerInterface
	{
		if ($getShared)
		{
			return static::getSharedInstance('patches', $handler, $config);
		}

		if (is_null($config))
		{
			$config = config('Patches');
		}

		if (is_null($handler))
		{
			$handler = $config->handler;
		}

		return new $handler($config);
	}
}
