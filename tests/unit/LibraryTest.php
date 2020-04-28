<?php

use Tatter\Patches\Patches;

class LibraryTest extends \Tests\Support\VirtualTestCase
{
	public function testIsDefinedVirtualPath()
	{
		$test = defined('VIRTUALPATH');

		$this->assertTrue($test);
	}

	public function testConstructCreatesWorkspace()
	{
		$patches = new Patches($this->config);

		$this->assertDirectoryExists($this->config->basePath);
	}

	public function testGetWorkspace()
	{
		$patches = new Patches($this->config);

		$this->assertDirectoryExists($patches->getWorkspace());
	}

	public function testSetWorkspaceCreatesDirectory()
	{
		$patches = new Patches($this->config);

		$patches->setWorkspace(VIRTUALPATH . 'foo');

		$this->assertDirectoryExists(VIRTUALPATH . 'foo');
	}

	public function testGatherSourcesFindsAll()
	{
		$patches = new Patches($this->config);

		$this->assertEquals(['TestSource', 'Framework'], array_keys($patches->getSources()));
	}

	public function testIgnoringSkipsSource()
	{
		$this->config->ignoredSources[] = 'Framework';
		$patches = new Patches($this->config);

		$this->assertEquals(['TestSource'], array_keys($patches->getSources()));	
	}

	public function testGetSourcesReturnsInstances()
	{
		$patches = new Patches($this->config);
		$sources = $patches->getSources();

		$this->assertInstanceOf('Tatter\Patches\Interfaces\SourceInterface', reset($sources));
	}

	public function testGatherPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches = new Patches($this->config);
		$paths   = $patches->gatherPaths();

		$expected = [
			[
				'from' => VIRTUALPATH . 'Package/images/cat.jpg',
				'to'   => 'app/ThirdParty/TestSource/images/cat.jpg',
			],
			[
				'from' => VIRTUALPATH . 'Package/lorem.txt',
				'to'   => 'app/ThirdParty/TestSource/lorem.txt',
			],
			[
				'from' => VIRTUALPATH . 'Package/static.js',
				'to'   => 'app/ThirdParty/TestSource/static.js',
			],
		];

		$this->assertCount(3, $paths);
		$this->assertEquals($expected, $paths);
	}

	public function testCopyPathsCopiesFilesToDestination()
	{
		$patches = new Patches($this->config);
		$patches->copyPaths(VIRTUALPATH . 'foo');

		$this->assertFileExists(VIRTUALPATH . 'foo/app/ThirdParty/TestSource/lorem.txt');
	}

	public function testCopyPathsReturnsPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches = new Patches($this->config);

		$paths = $patches->copyPaths(VIRTUALPATH . 'foo');

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
			'app/ThirdParty/TestSource/lorem.txt',
			'app/ThirdParty/TestSource/static.js',
		];

		$this->assertEquals($expected, $paths);
	}
}
