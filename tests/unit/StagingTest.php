<?php

use Tatter\Patches\Patches;

class StagingTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->config           = new \Tatter\Patches\Config\Patches();
		$this->config->basePath = VIRTUALPATH . 'workspace';
	}

	public function testStageFilesCopies()
	{
		$patches  = new Patches($this->config);

		$patches->stageFiles();

		$this->assertTrue(file_exists($patches->getWorkspace() . 'tester/lorem.txt'));
	}

	public function testStageReturnsPaths()
	{

		$this->config->ignoredHandlers[] = 'Framework';

		$patches   = new Patches($this->config);
		$workspace = $patches->getWorkspace();

		$paths = $patches->stageFiles();

		$expected = [
			$workspace . 'tester/nested/cat.jpg',
			$workspace . 'tester/lorem.txt',
		];

		$this->assertEquals($expected, $paths);
	}
}
