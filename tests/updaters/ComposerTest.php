<?php

use Tatter\Patches\Exceptions\UpdateException;
use Tatter\Patches\Patches;
use Tatter\Patches\Handlers\Updaters\ComposerHandler;

class ComposerTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		helper('filesystem');

		$this->config->updater = 'Tatter\Patches\Handlers\Updaters\ComposerHandler';

		// Virtual paths don't support chdir() so we need to test on the filesystem
		$this->config->composer = SUPPORTPATH . 'Source/Project/';
	}

	public function tearDown(): void
	{
		parent::tearDown();

		// Remove any files created
		delete_files($this->config->composer . 'vendor', true);

		if (is_dir($this->config->composer . 'vendor'))
		{
			rmdir($this->config->composer . 'vendor');
		}
		if (is_file($this->config->composer . 'composer.lock'))
		{
			unlink($this->config->composer . 'composer.lock');
		}
	}

	public function testComposerSucceeds()
	{
		$handler = new Patches(new ComposerHandler($this->config));

		$this->assertTrue($result);
	}

	public function testComposerErrorOnFailure()
	{
		$this->config->composer = '/foo/bar';

		$this->expectException(UpdateException::class);
		$this->expectExceptionMessage(lang('Patches.composerFailure', [1]));

		$handler->run($this->config);
	}

	public function testComposerCreatesVendor()
	{
		$handler = new ComposerHandler();
		$handler->run($this->config);

		$this->assertTrue(is_dir($this->config->composer . 'vendor'));
	}
}
