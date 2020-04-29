<?php namespace Tatter\Patches\Test;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Patches;
use Tatter\Patches\Interfaces\MergerInterface;

class MockMerger implements MergerInterface
{
	/**
	 * Do nothing.
	 *
	 * @param BaseConfig $config
	 * @param string $workspace
	 * @param array $changedFiles
	 * @param array $addedFiles
	 * @param array $deletedFiles
	 *
	 * @return array [array mergedFiles, array conflictFiles]
	 */
	public function run(BaseConfig $config, string $workspace, array $changedFiles, array $addedFiles, array $deletedFiles): array
	{
	}
}
