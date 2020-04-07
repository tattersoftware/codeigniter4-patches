<?php namespace Tatter\Patches\Handlers;

use Tatter\Patches\BaseHandler;
use Tatter\Patches\Interfaces\HandlerInterface;

class MockPatcher extends BaseHandler implements HandlerInterface
{
	/**
	 * Copy each path to its relative destination.
	 *
	 * @param string $destination  Directory to copy files into
	 *
	 * @return array  Array of the actual new file paths
	 */
	public function copyPaths(string $destination): array
	{
		$filenames   = [];
		$destination = rtrim($destination, '/') . '/';

		// Copy each file to its relative destination
		foreach ($this->gatherPaths() as $path)
		{
			$filenames[] = $destination . $path['to'];
		}

		return $filenames;
	}

	/**
	 * Compare each updated file with its prepatch equivalent.
	 */
	public function patch(string $target = null): array
	{
		
	}
}
