<?php

use Tatter\Patches\Codex;
use Tatter\Patches\Exceptions\UpdateException;
use Tatter\Patches\Test\MockUpdater;

class MockUpdaterTest extends \Tests\Support\VirtualTestCase
{
	/**
	 * @var Codex
	 */
	protected $codex;

	/**
	 * @var MockUpdater
	 */
	protected $handler;

	public function setUp(): void
	{
		parent::setUp();

		helper('filesystem');

		$this->config->updater = 'Tatter\Patches\Test\MockUpdater';

		$this->codex = new Codex($this->config);

		$this->handler = new MockUpdater($this->codex);
	}

	public function testMockUpdaterSetsProperties()
	{
		$this->handler->run();

		$this->assertIsArray($this->handler->changedFiles);
		$this->assertIsArray($this->handler->addedFiles);
		$this->assertIsArray($this->handler->deletedFiles);
	}
}
