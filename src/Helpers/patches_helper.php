<?php

if (! function_exists('same_file'))
{
	/**
	 * Checks if two files both exist and have identical hashes
	 *
	 * @param string $file1
	 * @param string $file2
	 *
	 * @return bool  Same or not
	 */
	function same_file(string $file1, string $file2): bool
	{
		return is_file($file1) && is_file($file2) && md5_file($file1) == md5_file($file2);
	}
}

if (! function_exists('ensure_file_dir'))
{
	/**
	 * Make sure the directory and parent directories to a file exist
	 *
	 * @param string $file  Full path to the file
	 *
	 * @return bool  Success or failure
	 */
	function ensure_file_dir(string $file): bool
	{
		$dir = pathinfo($file, PATHINFO_DIRNAME);

		if (! file_exists($dir))
		{
			return mkdir($dir, 0775, true);
		}

		return true;
	}
}

if (! function_exists('copy_path'))
{
	/**
	 * Copies a file to a destination creating directories as needed
	 *
	 * @param string $file1  Full path to the file
	 * @param string $file2  Full path to the new file
	 *
	 * @return bool  Success or failure
	 */
	function copy_path(string $file1, string $file2): bool
	{
		if (! is_file($file1))
		{
			return false;
		}

		// Make sure the destination directory exists
		ensure_file_dir($file2);

		// Copy the file
		try
		{
			return copy($file1, $file2);
		}
		catch (\Throwable $e)
		{
			return false;
		}
	}
}
