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
		$this->project = tempdir() . '/';

		// Copy the mock project to it
		copy_directory_recursive(SUPPORTPATH . 'MockProject', $this->project);
	}

	public function tearDownProject()
	{
		if (! function_exists('delete_files'))
		{
			helper('filesystem');
		}

		// Hidden directories need to be removed manually
		@unlink($this->project . 'vendor/laminas/laminas-zendframework-bridge/.github/FUNDING.yml');
		@rmdir($this->project . 'vendor/laminas/laminas-zendframework-bridge/.github');

		delete_files($this->project, true);
	}
}
