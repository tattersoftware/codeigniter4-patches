<?php namespace Tatter\Patches\Interfaces;

interface HandlerInterface
{
	/**
	 * Copy each path to its relative destination.
	 *
	 * @param string|null $target  Path to the project to patch; usually ROOTPATH
	 *
	 * @return array  Array of the actual new file paths
	 */
	public function patch(string $target = null): array
}
