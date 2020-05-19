<?php namespace Tests\Support;

trait LocalTestTrait
{
	public function setUpProject()
	{
		if (! function_exists('tempdir'))
		{
			helper('patches');
		}

		// Create the temp directory
		self::$project = tempdir() . '/';

		// Copy the mock project to it
		copy_directory_recursive(SUPPORTPATH . 'MockProject', self::$project);
	}

	public function tearDownProject()
	{
		if (! function_exists('delete_files'))
		{
			helper('filesystem');
		}

		// Hidden directories need to be removed manually
		@unlink(self::$project . 'vendor/laminas/laminas-zendframework-bridge/.github/FUNDING.yml');
		@rmdir(self::$project . 'vendor/laminas/laminas-zendframework-bridge/.github');

		delete_files(self::$project, true);
	}
}
