<?php namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;

class VirtualTestCase extends CIUnitTestCase
{
	/**
	 * @var vfsStream
	 */
	protected $root;

	/**
	 * @var string  Path to the mock project
	 */
	protected $project;

	public function setUp(): void
	{
		parent::setUp();

		$this->root = vfsStream::setup();

		defined('VIRTUALPATH') || define('VIRTUALPATH', $this->root->url() . '/');

		$this->project = VIRTUALPATH . 'project/';

		if (! is_dir($this->project))
		{
			mkdir($this->project, 0700, true);
		}
	}

	public function tearDown(): void
	{
		parent::tearDown();

		$this->root = null;
	}
}
