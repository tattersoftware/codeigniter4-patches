<?php namespace Tatter\Patches\Handlers\Patchers;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Patches;
use Tatter\Patches\Interfaces\MergerInterface;

class GitHandler implements MergerInterface
{
	/**
	 * Compare each updated file with its prepatch equivalent.
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
