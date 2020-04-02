<?php

use Tatter\Patches\Patches;

class LibraryTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testIsDefinedVirtualPath()
	{
		$test = defined('VIRTUALPATH');

		$this->assertTrue($test);
	}

	public function testConstructCreatesWorkspace()
	{
		$patches = new Patches($this->config);

		$this->assertTrue(is_dir($this->config->basePath));
	}

	public function testGetWorkspace()
	{
		$patches = new Patches($this->config);

		$this->assertTrue(is_dir($patches->getWorkspace()));
	}

	public function testSetWorkspaceCreatesDirectory()
	{
		$patches = new Patches($this->config);

		$patches->setWorkspace(VIRTUALPATH . 'foo');

		$this->assertTrue(is_dir(VIRTUALPATH . 'foo'));
	}

	public function testGetHandlersFindsAll()
	{
		$patches = new Patches($this->config);

		$this->assertEquals(['Tester', 'Framework'], array_keys($patches->getHandlers()));
	}

	public function testIgnoringSkipsHandler()
	{
		$this->config->ignoredHandlers[] = 'Framework';
		$patches = new Patches($this->config);

		$this->assertEquals(['Tester'], array_keys($patches->getHandlers()));	
	}

	public function testGetHandlersReturnsInstances()
	{
		$patches  = new Patches($this->config);
		$handlers = $patches->getHandlers();

		$this->assertInstanceOf('Tatter\Patches\Interfaces\PatcherInterface', reset($handlers));
	}
}
