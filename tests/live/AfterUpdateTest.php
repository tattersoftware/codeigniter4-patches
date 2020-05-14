<?php

use Tatter\Patches\Patches;

class AfterUpdateTest extends \Tests\Support\MockProjectTestCase
{
	use \Tests\Support\VirtualTestTrait;

	/**
	 * @var Tatter\Patches\Test\MockUpdater
	 */
	protected $updater;

	public function setUp(): void
	{
		parent::setUp();

		// Framework has way too many files so we will ignore it for now
		$this->config->ignoredSources[] = 'Framework';

		$this->patches = new Patches($this->config);

		$this->patches->beforeUpdate();
		$this->patches->update();

		// Get the MockUpdater instance to compare files
		$this->updater = $this->patches->getUpdater();
	}

	public function testSetsChangedFiles()
	{
		$this->patches->afterUpdate();

		$expected = str_replace($this->source, 'app/ThirdParty/TestSource/', $this->updater->changedFiles);

		$this->assertEqualsCanonicalizing($expected, $this->patches->getCodex()->changedFiles);
	}

	public function testCreatesCurrent()
	{
		$this->patches->afterUpdate();

		$this->assertDirectoryExists($this->patches->getWorkspace() . 'current');
	}

	public function testCopiesChangedFiles()
	{
		$this->patches->afterUpdate();

		$file = str_replace($this->source, 'app/ThirdParty/TestSource/', $this->updater->changedFiles[0]);

		$this->assertFileExists($this->patches->getWorkspace() . 'current/' . $file);
	}

	public function testSetsAddedFiles()
	{
		$this->patches->afterUpdate();

		$expected = str_replace($this->source, 'app/ThirdParty/TestSource/', $this->updater->addedFiles);

		$this->assertEqualsCanonicalizing($expected, $this->patches->getCodex()->addedFiles);
	}

	public function testCopiesAddedFiles()
	{
		$this->patches->afterUpdate();

		$file = str_replace($this->source, 'app/ThirdParty/TestSource/', $this->updater->addedFiles[0]);

		$this->assertFileExists($this->patches->getWorkspace() . 'current/' . $file);
	}

	public function testSetsDeletedFiles()
	{
		$this->patches->afterUpdate();

		$expected = str_replace($this->source, 'app/ThirdParty/TestSource/', $this->updater->deletedFiles);

		$this->assertEqualsCanonicalizing($expected, $this->patches->getCodex()->deletedFiles);
	}

	public function testExcludesDeletedFiles()
	{
		$this->patches->afterUpdate();

		$file = str_replace($this->source, 'app/ThirdParty/TestSource/', $this->updater->deletedFiles[0]);

		$this->assertFileNotExists($this->patches->getWorkspace() . 'current/' . $file);
	}
}
