<?php namespace Tests\Support\Patches;

use Tatter\Patches\Patches\Framework;
use Tests\Support\MockProjectTestCase;

class FrameworkTest extends Framework
{
	/**
	 * Array of paths to check during patching.
	 * Required: from (absolute), to (relative)
	 * Optional: exclude
	 *
	 * @var array
	 */
	public $paths;

	/**
	 * Set paths dynamically from project test case.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->paths = [
			[
				'from'    => MockProjectTestCase::$project . 'vendor/codeigniter4/framework/app',
				'to'      => 'app',
			],
			[
				'from'    => MockProjectTestCase::$project . 'vendor/codeigniter4/framework/writable',
				'to'      => 'writeable',
			],
			[
				'from'    => MockProjectTestCase::$project . 'vendor/codeigniter4/framework/public',
				'to'      => 'public',
			],
			[
				'from'    => MockProjectTestCase::$project . 'vendor/codeigniter4/framework/spark',
				'to'      => '',
			],
			[
				'from'    => MockProjectTestCase::$project . 'vendor/codeigniter4/framework/env',
				'to'      => '',
			],
			[
				'from'    => MockProjectTestCase::$project . 'vendor/codeigniter4/framework/builds',
				'to'      => '',
			],
		];
	}
}
