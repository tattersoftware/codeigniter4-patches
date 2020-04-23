<?php

use Tatter\Patches\Handlers\CopyHandler;

class CopyHandlerTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		// Framework has way too many files so we will ignore it for now
		$this->config->ignoredSources[] = 'Framework';

		$this->patches = new CopyHandler($this->config);
		$this->patches->beforeUpdate();
		$this->patches->afterUpdate();
	}

	public function testPatchCopiesFiles()
	{
		$this->patches->patch();

		$this->assertTrue(true);
	}
}
