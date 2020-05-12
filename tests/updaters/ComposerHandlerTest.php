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
		$this->config->rootPath = SUPPORTPATH . 'MockProject/';
		$this->codex = new Codex($this->config);

		$this->handler = new ComposerHandler($this->codex);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		// Remove any files created
		$this->removeComposerFiles();
	}

	public function testComposerSucceeds()
	{
		$result = $this->handler->update();

		$this->assertNull($result);
	}

	public function testComposerErrorOnFailure()
	{
		$this->codex->config->rootPath = '/foo/bar';

		$this->expectException(UpdateException::class);
		$this->expectExceptionMessage(lang('Patches.composerFailure', [1]));

		$this->handler->update();
	}

	public function testComposerCreatesVendor()
	{
		$this->handler->update();

		$this->assertTrue(is_dir($this->config->rootPath . 'vendor'));
	}
}
