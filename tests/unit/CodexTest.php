<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tatter\Patches\Codex;

class CodexTest extends CIUnitTestCase
{
	public function testCanSerialize()
	{
		$codex  = new Codex(config('Patches'));
		$result = json_decode(json_encode($codex));
		
		$expected = 'Tatter\Patches\Handlers\Mergers\CopyHandler';

		$this->assertEquals($expected, $result->config->merger);
	}
}
