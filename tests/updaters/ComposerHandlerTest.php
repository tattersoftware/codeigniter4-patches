<?php

use Tatter\Patches\Codex;
use Tatter\Patches\Exceptions\UpdateException;
use Tatter\Patches\Handlers\Updaters\ComposerHandler;

class ComposerHandlerTest extends \Tests\Support\VirtualTestCase
{
	/**
	 * @var Codex
	 */
	protected $codex;

	/**
	 * @var ComposerHandler
	 */
	protected $handler;

	public function setUp(): void
	{
		parent::setUp();

		helper('filesystem');

		$this->config->updater = 'Tatter\Patches\Handlers\Updaters\ComposerHandler';

		// Virtual paths don't support chdir() so we need to test on the filesystem
		$this->config->rootPath = SUPPORTPATH . 'Source/Project/';
		$this->codex = new Codex($this->config);

		$this->handler = new ComposerHandler();
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

	public function testComposerSucceeds()
	{
		$result = $this->handler->run($this->codex);

		$this->assertNull($result);
	}

	public function testComposerErrorOnFailure()
	{
		$this->codex->config->rootPath = '/foo/bar';

		$this->expectException(UpdateException::class);
		$this->expectExceptionMessage(lang('Patches.composerFailure', [1]));

		$this->handler->run($this->codex);
	}

	public function testComposerCreatesVendor()
	{
		$this->handler->run($this->codex);

		$this->assertTrue(is_dir($this->config->rootPath . 'vendor'));
	}
}
