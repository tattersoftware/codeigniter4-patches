<?php

use Tatter\Patches\BaseHandler;

class BaseHandlerTest extends \Tests\Support\VirtualTestCase
{
	public function testIsDefinedVirtualPath()
	{
		$test = defined('VIRTUALPATH');

		$this->assertTrue($test);
	}

	public function testConstructCreatesWorkspace()
	{
		$patches = new BaseHandler($this->config);

		$this->assertDirectoryExists($this->config->basePath);
	}

	public function testGetWorkspace()
	{
		$patches = new BaseHandler($this->config);

		$this->assertDirectoryExists($patches->getWorkspace());
	}

	public function testSetWorkspaceCreatesDirectory()
	{
		$patches = new BaseHandler($this->config);

		$patches->setWorkspace(VIRTUALPATH . 'foo');

		$this->assertDirectoryExists(VIRTUALPATH . 'foo');
	}

	public function testGatherSourcesFindsAll()
	{
		$patches = new BaseHandler($this->config);

		$this->assertEquals(['TestSource', 'Framework'], array_keys($patches->getSources()));
	}

	public function testIgnoringSkipsSource()
	{
		$this->config->ignoredSources[] = 'Framework';
		$patches = new BaseHandler($this->config);

		$this->assertEquals(['TestSource'], array_keys($patches->getSources()));	
	}

	public function testGetSourcesReturnsInstances()
	{
		$patches = new BaseHandler($this->config);
		$sources = $patches->getSources();

		$this->assertInstanceOf('Tatter\Patches\Interfaces\SourceInterface', reset($sources));
	}

	public function testGatherPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches = new BaseHandler($this->config);
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

	public function testCopyPathCreatesDirectory()
	{
		$patches = new BaseHandler($this->config);
		$patches->copyPath(SUPPORTPATH . 'Source/Package/lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertDirectoryExists(VIRTUALPATH . 'foobar');
	}

	public function testCopyPathCopiesFile()
	{
		$patches = new BaseHandler($this->config);
		$patches->copyPath(SUPPORTPATH . 'Source/Package/lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertFileExists(VIRTUALPATH . 'foobar/lorem.txt');
	}

	public function testCopyPathsCopiesFilesToDestination()
	{
		$patches = new BaseHandler($this->config);
		$patches->copyPaths(VIRTUALPATH . 'foo');

		$this->assertFileExists(VIRTUALPATH . 'foo/app/ThirdParty/TestSource/lorem.txt');
	}

	public function testCopyPathsReturnsPaths()
	{
		$this->config->ignoredSources[] = 'Framework';

		$patches = new BaseHandler($this->config);

		$paths = $patches->copyPaths(VIRTUALPATH . 'foo');

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
			'app/ThirdParty/TestSource/lorem.txt',
			'app/ThirdParty/TestSource/static.js',
		];

		$this->assertEquals($expected, $paths);
	}

	public function testIsSameFileSucceeds()
	{
		$patches = new BaseHandler($this->config);
		$patches->copyPath(SUPPORTPATH . 'Source/Package/lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertTrue($patches->isSameFile(SUPPORTPATH . 'Source/Package/lorem.txt', VIRTUALPATH . 'foobar/lorem.txt'));
	}

	public function testIsSameFileFailsDifferentFiles()
	{
		$patches = new BaseHandler($this->config);

		$this->assertFalse($patches->isSameFile(SUPPORTPATH . 'Source/Package/lorem.txt', SUPPORTPATH . 'files/images/cat.jpg'));
	}

	public function testIsSameFileFailsFileMissing()
	{
		$patches = new BaseHandler($this->config);

		$this->assertFalse($patches->isSameFile(SUPPORTPATH . 'Source/Package/lorem.txt', SUPPORTPATH . 'notafile.pdf'));
	}
}
