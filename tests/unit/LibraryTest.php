<?php

use Tatter\Patches\Patches;

class LibraryTest extends \Tests\Support\MockProjectTestCase
{
	use \Tests\Support\VirtualTestTrait;

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

		$patches->setWorkspace(self::$project . 'foo');

		$this->assertDirectoryExists(self::$project . 'foo');
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
				'from' => self::$project . 'vendor/testsource/images/cat.jpg',
				'to'   => 'app/ThirdParty/TestSource/images/cat.jpg',
			],
			[
				'from' => self::$project . 'vendor/testsource/lorem.txt',
				'to'   => 'app/ThirdParty/TestSource/lorem.txt',
			],
			[
				'from' => self::$project . 'vendor/testsource/static.js',
				'to'   => 'app/ThirdParty/TestSource/static.js',
			],
		];

		$this->assertCount(3, $paths);
		$this->assertEquals($expected, $paths);
	}

	public function testCopyPathsCopiesFilesToDestination()
	{
		$patches = new Patches($this->config);
		$patches->copyPaths(self::$project . 'foo');

		$this->assertFileExists(self::$project . 'foo/app/ThirdParty/TestSource/lorem.txt');
	}

	public function testCopyPathsReturnsPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches = new Patches($this->config);

		$paths = $patches->copyPaths(self::$project . 'foo');

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
			'app/ThirdParty/TestSource/lorem.txt',
			'app/ThirdParty/TestSource/static.js',
		];

		$this->assertEquals($expected, $paths);
	}

	public function testDiffFileReturnsExpectedValue()
	{
		$patches = new Patches($this->config);
		$patches->run();
		$codex = $patches->getCodex();

		$file = $codex->addedFiles[0];
		file_put_contents($codex->workspace . 'legacy/' . $file, "0123456789\nabcdefghij");
		file_put_contents($codex->workspace . 'current/' . $file, "0123456789aba\nabcdefghij\nxyz");

		$expected = "-0123456789\n-abcdefghij\n+0123456789aba\n+abcdefghij\n+xyz\n";

		$this->assertEquals($expected, $patches->diffFile($file));
	}
}
