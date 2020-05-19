<?php namespace Tests\Support;

use org\bovigo\vfs\vfsStream;

trait VirtualTestTrait
{
	/**
	 * @var vfsStream
	 */
	protected $root;

	public function setUpProject()
	{
		// Create the VFS
		$this->root = vfsStream::setup();
		vfsStream::copyFromFileSystem(SUPPORTPATH . 'MockProject', $this->root);

		self::$project = $this->root->url() . '/';
	}

	public function tearDownProject()
	{
		$this->root = null;
	}
}
