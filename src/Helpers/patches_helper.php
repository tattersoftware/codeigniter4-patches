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

if (! function_exists('copy_directory_recursive'))
{
	/**
	 * Copies a directory and all its contents to a destination directory
	 * https://stackoverflow.com/a/2050909
	 *
	 * @param string $file1  Full path to the file
	 * @param string $file2  Full path to the new file
	 *
	 * @return bool  Success or failure
	 */
	function copy_directory_recursive($dir1, $dir2, $mode = 0755)
	{
		$handle = opendir($dir1);

		if (! is_dir($dir2))
		{
			mkdir($dir2, $mode, true);
		}

		while (false !== ($file = readdir($handle)))
		{
			if (($file != '.') && ($file != '..'))
			{
				if (is_dir($dir1 . '/' . $file))
				{
					copy_directory_recursive($dir1 . '/' . $file, $dir2 . '/' . $file);
				}
				else
				{
					copy($dir1 . '/' . $file, $dir2 . '/' . $file);
				}
			}
		}

		closedir($handle);
	}
}

if (! function_exists('tempdir'))
{
	/**
	 * https://stackoverflow.com/a/30010928
	 * Creates a random unique temporary directory, with specified parameters,
	 * that does not already exist (like tempnam(), but for dirs).
	 *
	 * Created dir will begin with the specified prefix, followed by random
	 * numbers.
	 *
	 * @link https://php.net/manual/en/function.tempnam.php
	 *
	 * @param string|null $dir Base directory under which to create temp dir.
	 *     If null, the default system temp dir (sys_get_temp_dir()) will be
	 *     used.
	 * @param string $prefix String with which to prefix created dirs.
	 * @param int $mode Octal file permission mask for the newly-created dir.
	 *     Should begin with a 0.
	 * @param int $maxAttempts Maximum attempts before giving up (to prevent
	 *     endless loops).
	 * @return string|bool Full path to newly-created dir, or false on failure.
	 */
	function tempdir($dir = null, $prefix = 'tmp_', $mode = 0700, $maxAttempts = 1000)
	{
		/* Use the system temp dir by default. */
		if (is_null($dir))
		{
			$dir = sys_get_temp_dir();
		}

		/* Trim trailing slashes from $dir. */
		$dir = rtrim($dir, DIRECTORY_SEPARATOR);

		/* If we don't have permission to create a directory, fail, otherwise we will
		 * be stuck in an endless loop.
		 */
		if (!is_dir($dir) || !is_writable($dir))
		{
			return false;
		}

		/* Make sure characters in prefix are safe. */
		if (strpbrk($prefix, '\\/:*?"<>|') !== false)
		{
			return false;
		}

		/* Attempt to create a random directory until it works. Abort if we reach
		 * $maxAttempts. Something screwy could be happening with the filesystem
		 * and our loop could otherwise become endless.
		 */
		$attempts = 0;
		do
		{
			$path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, $prefix, mt_rand(100000, mt_getrandmax()));
		} while (
			!mkdir($path, $mode) &&
			$attempts++ < $maxAttempts
		);

		return $path;
	}
}
