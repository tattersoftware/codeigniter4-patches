<?php

use Tatter\Patches\BaseHandler;

class AfterUpdateTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		// Framework has way too many files so we will ignore it for now
		$this->config->ignoredSources[] = 'Framework';

		$this->patches = new BaseHandler($this->config);
		$this->patches->beforeUpdate();

		$this->mockUpdate();
	}

	public function testAfterUpdateSetsChangedFiles()
	{
		$this->patches->afterUpdate();

		$expected = [
			'app/ThirdParty/TestSource/lorem.txt',
		];

		$this->assertEquals($expected, $this->patches->changedFiles);
	}

	public function testAfterUpdateCreatesCurrent()
	{
		$this->patches->afterUpdate();

		$this->assertDirectoryExists($this->patches->getWorkspace() . 'current');
	}

	public function testAfterUpdateCopiesChangedFiles()
	{
		$this->patches->afterUpdate();

		$this->assertFileExists($this->patches->getWorkspace() . 'current/app/ThirdParty/TestSource/lorem.txt');
	}

	public function testAfterUpdateSetsAddedFiles()
	{
		$this->patches->afterUpdate();

		$expected = [
			'app/ThirdParty/TestSource/src/codex.json',
		];

		$this->assertEquals($expected, $this->patches->addedFiles);
	}

	public function testAfterUpdateCopiesAddedFiles()
	{
		$this->patches->afterUpdate();

		$this->assertFileExists($this->patches->getWorkspace() . 'current/app/ThirdParty/TestSource/src/codex.json');
	}

	public function testAfterUpdateSetsDeletedFiles()
	{
		$this->patches->afterUpdate();

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
		];

		$this->assertEquals($expected, $this->patches->deletedFiles);
	}
}
