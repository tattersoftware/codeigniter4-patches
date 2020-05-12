<?php

use Tatter\Patches\Codex;
use Tatter\Patches\Exceptions\UpdateException;
use Tatter\Patches\Handlers\Updaters\ComposerHandler;
use Tatter\Patches\Patches;
use Tatter\Patches\Test\MockUpdater;

class MockUpdaterTest extends \Tests\Support\VirtualTestCase
{
	/**
	 * @var Codex
	 */
	protected $codex;

	/**
	 * @var MockUpdater
	 */
	protected $handler;

	public function setUp(): void
	{
		parent::setUp();

		helper('filesystem');

		$this->config->updater = 'Tatter\Patches\Test\MockUpdater';

		// Virtual paths don't support chdir() so we need to test on the filesystem
		$this->config->rootPath = SUPPORTPATH . 'Source/Project/';
		$this->codex = new Codex($this->config);

		// MockUpdater relies on legacyFiles so we will stage some with ComposerHandler
		$composer = new ComposerHandler($this->codex);
		$composer->update();

		$this->handler = new MockUpdater($this->codex);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		// Remove any files created
		delete_files($this->config->rootPath . 'vendor', true);

		if (is_dir($this->config->rootPath . 'vendor'))
		{
			rmdir($this->config->rootPath . 'vendor');
		}
		if (is_file($this->config->rootPath . 'composer.lock'))
		{
			unlink($this->config->rootPath . 'composer.lock');
		}
	}

	public function testMockUpdaterSetsProperties()
	{
		$this->handler->update();

		$this->assertIsArray($this->handler->changedFiles);
		$this->assertIsArray($this->handler->addedFiles);
		$this->assertIsArray($this->handler->deletedFiles);
	}

	public function testMockUpdaterModifiesFiles()
	{
		$this->handler->update();

		if (! empty($this->handler->addedFiles))
		{
			$this->assertFileExists($this->handler->addedFiles[0]);

			$contents = file_get_contents($this->handler->addedFiles[0]);
			$this->assertTrue(ctype_xdigit($contents));
		}
		if (! empty($this->handler->deletedFiles))
		{
			$this->assertFileNotExists($this->handler->deletedFiles[0]);
		}
		if (! empty($this->handler->changedFiles))
		{
			$this->assertFileExists($this->handler->changedFiles[0]);

			$contents = file_get_contents($this->handler->changedFiles[0]);
			$this->assertTrue(ctype_xdigit($contents));
		}
	}
}
