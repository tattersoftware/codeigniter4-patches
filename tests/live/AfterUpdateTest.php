<?php

use Tatter\Patches\BaseHandler;

class AfterUpdateTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		// Framework has way too many files so we will ignore it for now
		$this->config->ignoredSources[] = 'Framework';

		$this->patches = new BaseHandler($this->config);
		$this->patches->beforeUpdate();

		// Stage some changes to the package
		file_put_contents($this->source . 'lorem.txt', 'All your base are belong to us.');
		unlink($this->source . 'images/cat.jpg');
	}

	public function testAfterUpdateSetsCurrentFiles()
	{
		$this->patches->afterUpdate();

		$expected = [
			'app/ThirdParty/TestSource/lorem.txt',
		];

		$this->assertEquals($expected, $this->patches->currentFiles);
	}

	public function testAfterUpdateCreatesCurrent()
	{
		$this->patches->afterUpdate();

		$this->assertDirectoryExists($this->patches->getWorkspace() . 'current');
	}

	public function testAfterUpdateCopiesFiles()
	{
		$this->patches->afterUpdate();

		$this->assertFileExists($this->patches->getWorkspace() . 'current/app/ThirdParty/TestSource/lorem.txt');
	}

	public function testAfterUpdateSetsDeletedFiles()
	{
		$this->patches->afterUpdate();

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
		];

		$this->assertEquals($expected, $this->patches->deletedFiles);
	}
}
