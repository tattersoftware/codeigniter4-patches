<?php

class HelperTest extends \Tests\Support\MockProjectTestCase
{
	use \Tests\Support\VirtualTestTrait;

	public function testCopyPathCreatesDirectory()
	{
		copy_path($this->source . 'lorem.txt', self::$project . 'foobar/lorem.txt');

		$this->assertDirectoryExists(self::$project . 'foobar');
	}

	public function testCopyPathCopiesFile()
	{
		copy_path($this->source . 'lorem.txt', self::$project . 'foobar/lorem.txt');

		$this->assertFileExists(self::$project . 'foobar/lorem.txt');
	}

	public function testSameFileSucceeds()
	{
		copy_path($this->source . 'lorem.txt', self::$project . 'foobar/lorem.txt');

		$this->assertTrue(same_file($this->source . 'lorem.txt', self::$project . 'foobar/lorem.txt'));
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
