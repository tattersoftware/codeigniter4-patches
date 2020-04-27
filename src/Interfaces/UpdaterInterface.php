<?php namespace Tatter\Patches\Interfaces;

use CodeIgniter\Config\BaseConfig;

interface UpdaterInterface
{
	/**
	 * Update vendor packages.
	 *
	 * @param BaseConfig $config
	 */
	public function run(BaseConfig $config = null);
}
