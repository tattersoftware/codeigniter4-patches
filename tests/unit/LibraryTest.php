<?php

use Tatter\Patches\Patches;

class LibraryTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->config           = new \Tatter\Patches\Config\Patches();
		$this->config->basePath = VIRTUALPATH . 'workspace';
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

	public function testSetWorkspaceCreatesDirectory()
	{
		$patches = new Patches($this->config);

		$patches->setWorkspace(VIRTUALPATH . 'foo');

		$this->assertTrue(is_dir(VIRTUALPATH . 'foo'));
	}

	public function testGetHandlersFindsAll()
	{
		$patches = new Patches($this->config);

		$handlers = $patches->getHandlers();
		
		$this->assertEquals(['Tester', 'Framework'], array_keys($handlers));
	}

	public function testIgnoringSkipsHandler()
	{
		$this->config->ignoredHandlers[] = 'Framework';
		$patches = new Patches($this->config);

		$handlers = $patches->getHandlers();
		
		$this->assertEquals(['Tester'], array_keys($handlers));	
	}

	public function testGetHandlersReturnsInstances()
	{
		$patches = new Patches($this->config);

		$handlers = $patches->getHandlers();
		
		$this->assertInstanceOf('Tatter\Patches\Interfaces\PatcherInterface', reset($handlers));
	}

	public function testStageFilesCopies()
	{
		$patches  = new Patches($this->config);
		$handlers = $patches->getHandlers();

		$patches->stageFiles($handlers);

		$this->assertTrue(file_exists($this->config->basePath . 'lorem.txt'));
	}
}
