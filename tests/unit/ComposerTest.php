<?php

use Tatter\Patches\BaseHandler;

class ComposerTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testComposerSucceeds()
	{
		$patches = new BaseHandler($this->config);
		$result  = $patches->composer();

		$this->assertTrue($result);
	}

	public function testComposerErrorOnFailure()
	{
		$this->config->composer = '/foo/bar';

		$patches = new BaseHandler($this->config);
		$result  = $patches->composer();

		$this->assertFalse($result);
		
		$errors = $patches->getErrors();
		$this->assertCount(1, $errors);
	}

	public function testComposerCreatesVendor()
	{
		$patches = new BaseHandler($this->config);
		$patches->composer();

		$this->assertTrue(is_dir($this->config->composer . 'vendor'));
	}
}
