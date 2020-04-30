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
		$this->codex   = $this->patches->getCodex();
	}

	public function testReturnsMergedFiles()
	{
		
		$this->handler->run($this->codex);

		$expected = [
			'app/ThirdParty/TestSource/lorem.txt',
			'app/ThirdParty/TestSource/src/codex.json',
		];

		$this->assertEquals($expected, $this->codex->mergedFiles);
	}

	public function testReturnsConflictFiles()
	{
		$this->handler->run($this->codex);

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
		];

		$this->assertEquals($expected, $this->codex->conflictFiles);
	}

	public function testChangesFile()
	{
		$this->handler->run($this->codex);

		$expected = 'All your base are belong to us.';
		$contents = file_get_contents($this->project . 'app/ThirdParty/TestSource/lorem.txt');

		$this->assertEquals($expected, $contents);
	}

	public function testAddsFile()
	{
		$this->handler->run($this->codex);

		$this->assertFileExists($this->project . 'app/ThirdParty/TestSource/src/codex.json');
	}
}
