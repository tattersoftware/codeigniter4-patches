<?php namespace Tatter\Patches\Test;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Exception\UpdateException;
use Tatter\Patches\Interfaces\UpdaterInterface;

class MockUpdater implements UpdaterInterface
{
	/**
	 * Do nothing.
	 *
	 * @param BaseConfig $config
	 *
	 * @throws UpdateException
	 */
	public function run(BaseConfig $config = null)
	{
	}
}
