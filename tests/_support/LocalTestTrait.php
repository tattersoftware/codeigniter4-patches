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

		delete_files($this->project, true);

		if (is_dir($this->project))
		{
			rmdir($this->project);
		}
	}
}
