<?php namespace Tatter\Patches\Interfaces;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Patches;

interface MergerInterface
{
	/**
	 * Merge each path to its relative destination.
	 *
	 * @param BaseConfig $config
	 * @param string $workspace
	 * @param array $changedFiles
	 * @param array $addedFiles
	 * @param array $deletedFiles
	 *
	 * @return array [array mergedFiles, array conflictFiles]
	 */
	public function run(BaseConfig $config, string $workspace, array $changedFiles, array $addedFiles, array $deletedFiles): array;
}
