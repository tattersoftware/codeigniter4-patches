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
	 * @var string  Path to the virtual project
	 */
	protected $project;

	/**
	 * @var string  Path to the virtual pacakge source
	 */
	protected $source;

	public function setUp(): void
	{
		parent::setUp();

		// Create the VFS
		$this->root = vfsStream::setup();
		vfsStream::copyFromFileSystem(SUPPORTPATH . 'Source', $this->root);

		defined('VIRTUALPATH') || define('VIRTUALPATH', $this->root->url() . '/');
		$this->project = VIRTUALPATH . 'Project/';
		$this->source  = VIRTUALPATH . 'Package/';

		// Standardize testing config
		$this->config           = new \Tatter\Patches\Config\Patches();
		$this->config->basePath = $this->project . 'writable/patches';
		$this->config->composer = $this->project;
	}

	public function tearDown(): void
	{
		parent::tearDown();

		$this->root = null;
	}
}
