<?php

use Tatter\Patches\Patches;
use Tatter\Patches\Patches\Framework;

class FrameworkTest extends \Tests\Support\MockProjectTestCase
{
	use \Tests\Support\LocalTestTrait;

	/**
	 * @var Tatter\Patches\Test\MockUpdater
	 */
	protected $updater;

	public function setUp(): void
	{
		parent::setUp();

		// Add an old version of the framework to composer.json
		$array = json_decode(file_get_contents(self::$project . 'composer.json'), true);
		$array['require']['codeigniter4/framework'] = 'v4.0.0-rc3';

		// Write out to composer.json and run `composer install`
		file_put_contents(self::$project . 'composer.json', json_encode($array, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . PHP_EOL);
		$this->composerInstall();

		// Copy in the folders
		copy_directory_recursive(self::$project . 'vendor/codeigniter4/framework', self::$project);

		// Set composer.json back to latest
		$array['require']['codeigniter4/framework'] = '^4.0';
		file_put_contents(self::$project . 'composer.json', json_encode($array, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . PHP_EOL);

		// Set the updater and source and run the patch
		$this->config->updater        = 'Tatter\Patches\Handlers\Updaters\ComposerHandler';
		$this->config->ignoredSources = ['Framework'];

		$this->patches = new Patches($this->config);
		$this->patches->run();

		$this->codex = $this->patches->getCodex();
	}

	public function testSetsFiles()
	{
		$this->assertGreaterThan(0, count($this->codex->legacyFiles));
		$this->assertGreaterThan(0, count($this->codex->changedFiles));
		$this->assertGreaterThan(0, count($this->codex->addedFiles));
		$this->assertGreaterThan(0, count($this->codex->mergedFiles));
	}

	public function testHasAddedFiles()
	{
d($this->codex);
		$this->assertFileExists(self::$project . $this->codex->addedFiles[0]);
	}
}
