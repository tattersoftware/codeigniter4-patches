<?php

class HelperTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		helper('patches');
	}

	public function testCopyPathCreatesDirectory()
	{
		copy_path($this->source . 'lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertDirectoryExists(VIRTUALPATH . 'foobar');
	}

	public function testCopyPathCopiesFile()
	{
		copy_path($this->source . 'lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertFileExists(VIRTUALPATH . 'foobar/lorem.txt');
	}

	public function testSameFileSucceeds()
	{
		copy_path($this->source . 'lorem.txt', VIRTUALPATH . 'foobar/lorem.txt');

		$this->assertTrue(same_file($this->source . 'lorem.txt', VIRTUALPATH . 'foobar/lorem.txt'));
	}

	public function testSameFileFailsDifferentFiles()
	{
		$this->assertFalse(same_file($this->source . 'lorem.txt', SUPPORTPATH . 'files/images/cat.jpg'));
	}

	public function testSameFileFailsFileMissing()
	{
		$this->assertFalse(same_file($this->source . 'lorem.txt', SUPPORTPATH . 'notafile.pdf'));
	}
}
