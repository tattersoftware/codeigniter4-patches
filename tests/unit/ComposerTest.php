<?php

use Tatter\Patches\Patches;

class ComposerTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testComposerCreatesVendor()
	{
		$patches = new Patches($this->config);
		$patches->composer();

		$this->assertTrue(is_dir($this->config->composer . 'vendor'));
	}
}
