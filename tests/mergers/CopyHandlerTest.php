<?php

use Tatter\Patches\Handlers\Mergers\CopyHandler;
use Tatter\Patches\Patches;

class CopyHandlerTest extends \Tests\Support\VirtualTestCase
{
	/**
	 * @var Tatter\Patches\Test\MockUpdater
	 */
	protected $updater;

	/**
	 * @var Tatter\Patches\Handlers\Mergers\CopyHandler
	 */
	protected $handler;

	public function setUp(): void
	{
		parent::setUp();

		// Framework has way too many files so we will ignore it for now
		$this->config->ignoredSources[] = 'Framework';

		// Prepare the library
		$this->patches = new Patches($this->config);
		$this->patches->beforeUpdate();
		$this->patches->update();
		$this->patches->afterUpdate();

		// Get the MockUpdater instance to compare files
		$this->updater = $this->patches->getUpdater();

		$this->codex   = $this->patches->getCodex();
		$this->handler = new CopyHandler($this->codex);
	}

	public function testReturnsMergedFiles()
	{
		$this->handler->merge();

		$expected = [
			'app/ThirdParty/TestSource/lorem.txt',
			'app/ThirdParty/TestSource/src/definition.json',
		];

		$this->assertEquals($expected, $this->codex->mergedFiles);
	}

	public function testReturnsConflictFiles()
	{
		// Create some content where a file will be added
		mkdir($this->project . 'app/ThirdParty/TestSource/src', 0700, true);
		file_put_contents($this->project . 'app/ThirdParty/TestSource/src/definition.json', 'Seat taken');

		$this->handler->merge();

		$expected = [
			'changed' => [],
			'added'   => ['app/ThirdParty/TestSource/src/definition.json'],
			'deleted' => [],
		];

		$this->assertEquals($expected, $this->codex->conflicts);
	}

	public function testChangesFile()
	{
		$this->handler->merge();

		$expected = 'All your base are belong to us.';
		$contents = file_get_contents($this->project . 'app/ThirdParty/TestSource/lorem.txt');

		$this->assertEquals($expected, $contents);
	}

	public function testAddsFile()
	{
		$this->handler->merge();

		$this->assertFileExists($this->project . 'app/ThirdParty/TestSource/src/definition.json');
	}
}
