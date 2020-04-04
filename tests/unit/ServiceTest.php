<?php

use Config\Services;

class ServiceTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function testUsesDefaultHandler()
	{
		$config  = config('Patches');
		$patches = Services::patches(null, null, false);

		$this->assertInstanceOf($config->handler, $patches);
	}

	public function testUsesDefinedHandler()
	{
		$classname = 'Tatter\Patches\Handlers\MockPatcher';

		$patches = Services::patches($classname, null, false);

		$this->assertInstanceOf($classname, $patches);
	}

	public function testUsesConfigHandler()
	{
		$config = config('Patches');

		$config->handler = 'Tatter\Patches\Handlers\MockPatcher';

		$patches = Services::patches(null, $config, false);

		$this->assertInstanceOf($config->handler, $patches);
	}
}
