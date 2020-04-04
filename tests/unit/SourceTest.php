<?php

use CodeIgniter\Events\Events;
use Tatter\Patches\BaseHandler;

class SourceTest extends \Tests\Support\VirtualTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testEventsTrigger()
	{
		$GLOBALS['testSourceDidPrepatch'] = false;

		$patches = new BaseHandler($this->config);

		$result  = Events::trigger('prepatch', []);

		$this->assertTrue($result);
		$this->assertTrue($GLOBALS['testSourceDidPrepatch']);
	}
}
