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
		$this->root = vfsStream::copyFromFileSystem(SUPPORTPATH . 'project', $this->root);

		defined('VIRTUALPATH') || define('VIRTUALPATH', $this->root->url() . '/');

		// Standard testing config
		$this->config           = new \Tatter\Patches\Config\Patches();
		$this->config->basePath = VIRTUALPATH . 'writable/patches';
		$this->config->composer = VIRTUALPATH;
	}

	public function tearDown(): void
	{
		parent::tearDown();

		$this->root = null;
	}
}
