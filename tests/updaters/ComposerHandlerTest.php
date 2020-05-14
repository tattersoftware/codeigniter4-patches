<?php

use Tatter\Patches\Codex;
use Tatter\Patches\Exceptions\UpdateException;
use Tatter\Patches\Handlers\Updaters\ComposerHandler;

class ComposerHandlerTest extends \Tests\Support\MockProjectTestCase
{
	// Virtual paths don't support chdir() so we need to test on the filesystem
	use \Tests\Support\LocalTestTrait;

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

		$this->config->updater = 'Tatter\Patches\Handlers\Updaters\ComposerHandler';
		$this->codex = new Codex($this->config);

		$this->handler = new ComposerHandler($this->codex);
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
