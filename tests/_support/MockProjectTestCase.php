<?php namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

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

	/**
	 * Helper for when packages need to be installed prior to patching.
	 *
	 * @param string|null $rootPath  Target directory with composer.json
	 *
	 * @return int  Return value of run(); 0 for success, error code otherwise
	 */
	protected function composerInstall($rootPath = null): int
	{
		$application = new Application();
		$params      = [
			'command'       => 'install',
			'--working-dir' => $rootPath ?? $this->config->rootPath,
			'--quiet'       => true,
		];

		$input = new ArrayInput($params);

		// Prevent $application->run() from exiting the script
		$application->setAutoExit(false);

		// Returns int 0 if everything went fine, or an error code
		return $application->run($input);
	}
}
