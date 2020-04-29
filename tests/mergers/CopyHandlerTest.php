<?php

use Tatter\Patches\Handlers\Mergers\CopyHandler;
use Tatter\Patches\Patches;

class CopyHandlerTest extends \Tests\Support\VirtualTestCase
{
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
		$this->mockUpdate();
		$this->patches->afterUpdate();

		$this->handler = new CopyHandler();
	}

	public function testReturnsMergedFiles()
	{
		list($mergedFiles, $conflictFiles) = $this->handler->run($this->config, $this->patches->getWorkspace(), $this->patches->changedFiles, $this->patches->addedFiles, $this->patches->deletedFiles);

		$expected = [
			'app/ThirdParty/TestSource/lorem.txt',
			'app/ThirdParty/TestSource/src/codex.json',
		];

		$this->assertEquals($expected, $mergedFiles);
	}

	public function testReturnsConflictFiles()
	{
		list($mergedFiles, $conflictFiles) = $this->handler->run($this->config, $this->patches->getWorkspace(), $this->patches->changedFiles, $this->patches->addedFiles, $this->patches->deletedFiles);

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
		];

		$this->assertEquals($expected, $conflictFiles);
	}

	public function testChangesFile()
	{
		list($mergedFiles, $conflictFiles) = $this->handler->run($this->config, $this->patches->getWorkspace(), $this->patches->changedFiles, $this->patches->addedFiles, $this->patches->deletedFiles);

		$expected = 'All your base are belong to us.';
		$contents = file_get_contents($this->project . 'app/ThirdParty/TestSource/lorem.txt');

		$this->assertEquals($expected, $contents);
	}

	public function testAddsFile()
	{
		list($mergedFiles, $conflictFiles) = $this->handler->run($this->config, $this->patches->getWorkspace(), $this->patches->changedFiles, $this->patches->addedFiles, $this->patches->deletedFiles);

		$this->assertFileExists($this->project . 'app/ThirdParty/TestSource/src/codex.json');
	}
}
