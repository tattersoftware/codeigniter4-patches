<?php

use Tatter\Patches\BaseHandler;

class BeforeUpdateTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		// Framework has way too many files so we will ignore it for now
		$this->config->ignoredSources[] = 'Framework';

		$this->patches = new BaseHandler($this->config);
	}

	public function testBeforeUpdateSetsLegacyFiles()
	{
		$this->patches->beforeUpdate();

		$expected = [
			'app/ThirdParty/TestSource/images/cat.jpg',
			'app/ThirdParty/TestSource/lorem.txt',
			'app/ThirdParty/TestSource/static.js',
		];

		$this->assertEquals($expected, $this->patches->legacyFiles);
	}

	public function testBeforeUpdateCreatesLegacy()
	{
		$this->patches->beforeUpdate();

		$this->assertDirectoryExists($this->patches->getWorkspace() . 'legacy');
	}

	public function testBeforeUpdateCopiesFiles()
	{
		$this->patches->beforeUpdate();

		$this->assertFileExists($this->patches->getWorkspace() . 'legacy/app/ThirdParty/TestSource/lorem.txt');
	}

	public function testBeforeUpdateTriggersEvent()
	{
		$GLOBALS['testSourceDidPrepatch'] = false;

		$this->patches->beforeUpdate();

		$this->assertTrue($GLOBALS['testSourceDidPrepatch']);
	}
}
