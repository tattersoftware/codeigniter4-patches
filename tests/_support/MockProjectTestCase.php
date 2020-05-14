<?php namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;

class MockProjectTestCase extends CIUnitTestCase
{
	/**
	 * @var string  Path to the mocked project, set by setUpProject
	 */
	protected $project;

	/**
	 * @var string  Path to the mocked package source
	 */
	protected $source;

	public function setUp(): void
	{
		parent::setUp();

		$this->setUpProject();

		defined('MOCKPROJECTPATH') || define('MOCKPROJECTPATH', $this->project);
		$this->source = $this->project . 'vendor/testsource/';

		// Standardize testing config
		$this->config           = new \Tatter\Patches\Config\Patches();
		$this->config->basePath = $this->project . 'writable/patches';
		$this->config->rootPath = $this->project;
		$this->config->updater  = 'Tatter\Patches\Test\MockUpdater';
	}

	public function tearDown(): void
	{
		parent::tearDown();

		$this->tearDownProject();
	}
}
