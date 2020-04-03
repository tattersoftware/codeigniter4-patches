<?php

use Tatter\Patches\BaseHandler;

class StagingTest extends \Tests\Support\VirtualTestCase
{
	public function testGatherPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches = new BaseHandler($this->config);
		$paths   = $patches->gatherPaths();

		$expected = [
			[
				'from' => SUPPORTPATH . 'files/nested/cat.jpg',
				'to'   => 'tester/nested/cat.jpg',
			],
			[
				'from' => SUPPORTPATH . 'files/lorem.txt',
				'to'   => 'tester/lorem.txt',
			],
		];

		$this->assertCount(2, $paths);
		$this->assertEquals($expected, $paths);
	}

	public function testStagingCopiesFiles()
	{
		$patches = new BaseHandler($this->config);
		$patches->stageFiles();

		$this->assertTrue(file_exists($patches->getWorkspace() . 'tester/lorem.txt'));
	}

	public function testStagingReturnsPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches   = new BaseHandler($this->config);
		$workspace = $patches->getWorkspace();

		$paths = $patches->stageFiles();

		$expected = [
			$workspace . 'tester/nested/cat.jpg',
			$workspace . 'tester/lorem.txt',
		];

		$this->assertEquals($expected, $paths);
	}
}
