<?php

use Tatter\Patches\Handlers\CopyHandler;

class CopyHandlerTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		// Framework has way too many files so we will ignore it for now
		$this->config->ignoredSources[] = 'Framework';

		$this->patches = new CopyHandler($this->config);

		$this->patches->beforeUpdate();
		$this->mockUpdate();
		$this->patches->afterUpdate();
	}

	public function testPatchSetsPatchedFiles()
	{
		$this->patches->patch($this->project);

		$expected = [
			'app/ThirdParty/TestSource/lorem.txt',
			'app/ThirdParty/TestSource/src/codex.json',
		];

		$this->assertEquals($expected, $this->patches->patchedFiles);
	}

	public function testPatchSetsConflictFiles()
	{
		$this->patches->patch($this->project);

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
		];

		$this->assertEquals($expected, $this->patches->conflictFiles);
	}

	public function testPatchChangesFile()
	{
		$this->patches->patch($this->project);

		$expected = 'All your base are belong to us.';
		$contents = file_get_contents($this->project . 'app/ThirdParty/TestSource/lorem.txt');

		$this->assertEquals($expected, $contents);
	}

	public function testPatchAddsFile()
	{
		$this->patches->patch($this->project);

		$this->assertFileExists($this->project . 'app/ThirdParty/TestSource/src/codex.json');
	}
}
