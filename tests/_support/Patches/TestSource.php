<?php namespace Tests\Support\Patches;

use Tatter\Patches\BaseSource;
use Tatter\Patches\Interfaces\SourceInterface;
use Tests\Support\MockProjectTestCase;

class TestSource extends BaseSource implements SourceInterface
{
	/**
	 * Array of paths to check during patching.
	 * Required: from, to
	 * Optional: exclude
	 *
	 * @var array
	 */
	public $paths;

	/**
	 * Record the project path.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->paths = [
			[
				'from'    => MockProjectTestCase::$project . 'vendor/testsource',
				'to'      => 'app/ThirdParty/TestSource',
			],
		];
	}

	/**
	 * Run a simulate prepatch event.
	 *
	 * @param string $directory   Directory where the files are located
	 * @param array $legacyFiles  Legacy files staged by beforeUpdate
	 *
	 * @return bool  Whether or not the event succeeded
	 */
	public function prepatch(string $directory, array $legacyFiles): bool
	{
		$GLOBALS['testSourceDidPrepatch'] = true;

		return true;
	}
}
