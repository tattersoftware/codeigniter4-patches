<?php

use Tatter\Patches\Patches;

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
		$array = json_decode(file_get_contents($this->project . 'composer.json'), true);
		$array['require']['codeigniter4/framework'] = '4.0.2';

		// Write out the new composer.json and run `composer install`
		file_put_contents($this->project . 'composer.json', json_encode($array, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . PHP_EOL);
		$this->composerInstall();

		$this->patches = new Patches($this->config);
		$this->patches->run();
	}

	public function testHasFiles()
	{
		$this->assertFileExists($this->project . 'vendor/codeigniter4/framework/app/Config/Kint.php');
	}
}
