<?php

use Tatter\Patches\BaseHandler;

class ComposerTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		helper('filesystem');

		// Virtual paths don't support chdir() so we need to test on the filesystem
		$this->config->composer = SUPPORTPATH . 'project/';
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
		$patches = new BaseHandler($this->config);
		$result  = $patches->composerUpdate();

		$this->assertTrue($result);
	}

	public function testComposerErrorOnFailure()
	{
		$this->config->composer = '/foo/bar';

		$patches = new BaseHandler($this->config);
		$result  = $patches->composerUpdate();

		$this->assertFalse($result);
		
		$errors = $patches->getErrors();
		$this->assertCount(1, $errors);
	}

	public function testComposerCreatesVendor()
	{
		$patches = new BaseHandler($this->config);
		$patches->composerUpdate();

		$this->assertTrue(is_dir($this->config->composer . 'vendor'));
	}
}
