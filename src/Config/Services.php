<?php namespace Tatter\Patches\Config;

use CodeIgniter\Config\BaseService;
use Tatter\Patches\BaseHandler;

class Services extends BaseService
{
	/**
	 * Returns a configured patch handler
	 *
	 * @param BaseHandler $handler  The patch handler to use
	 * @param Config      $config
	 * @param boolean     $getShared
	 *
	 * @return \Tatter\Patches\BaseHandler
	 */
	public static function patches(BaseHandler $handler = null, $config = null, bool $getShared = true): Firebase
	{
		if ($getShared)
		{
			return static::getSharedInstance('patches', $method, $config);
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
