<?php

use Tatter\Patches\Handlers\Mergers\CopyHandler;
use Tatter\Patches\Patches;

class CopyHandlerTest extends \Tests\Support\MockProjectTestCase
{
	use \Tests\Support\VirtualTestTrait;

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

		$expected = str_replace($this->source, 'app/ThirdParty/TestSource/',
			array_merge($this->updater->addedFiles, $this->updater->changedFiles));

		$this->assertEqualsCanonicalizing($expected, $this->codex->mergedFiles);
	}

	public function testReturnsConflictFiles()
	{
		// Create some content where a file will be added
		$file = str_replace($this->source, 'app/ThirdParty/TestSource/', $this->updater->addedFiles[0]);
		ensure_file_dir($this->project . $file);
		file_put_contents($this->project . $file, 'Seat taken');

		$this->handler->merge();

		$expected = [
			'changed' => [],
			'added'   => [$file],
			'deleted' => [],
		];

		$this->assertEquals($expected, $this->codex->conflicts);
	}

	public function testChangesFile()
	{
		$this->handler->merge();

		$file = str_replace($this->source, $this->project . 'app/ThirdParty/TestSource/', $this->updater->changedFiles[0]);
		$contents = file_get_contents($file);
		$this->assertTrue(ctype_xdigit($contents));
	}

	public function testAddsFile()
	{
		$this->handler->merge();

		$file = str_replace($this->source, $this->project . 'app/ThirdParty/TestSource/', $this->updater->addedFiles[0]);

		$this->assertFileExists($file);
	}
}
