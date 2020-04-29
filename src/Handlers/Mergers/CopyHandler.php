<?php namespace Tatter\Patches\Handlers\Mergers;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Interfaces\MergerInterface;
use Tatter\Patches\Patches;

class CopyHandler implements MergerInterface
{
	/**
	 * Compare files and replace as needed, tracking conflicts
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
		$mergedFiles   = [];
		$conflictFiles = [];

		// Check every changed file against the destination
		foreach ($changedFiles as $file)
		{
			$current = $workspace . 'current/' . $file;
			$legacy  = $workspace . 'legacy/'  . $file;
			$project = $config->rootPath . $file;

			// Check if the project is missing this file or has the legacy version
			if (! file_exists($project) || same_file($project, $legacy))
			{
				// Copy in the new version
				copy_path($current, $project);
				$mergedFiles[] = $file;
			}
			// Mark it as a conflict
			else
			{
				$conflictFiles[] = $file;
			}
		}

		// Try to copy in every added file
		foreach ($addedFiles as $file)
		{
			$current = $workspace . 'current/' . $file;
			$project = $config->rootPath . $file;

			if (is_file($project))
			{
				// See if it is already the same
				if (! same_file($project, $current))
				{
					// Mark it as a conflict
					$conflictFiles[] = $file;
				}
			}
			else
			{
				copy_path($current, $project);
				$mergedFiles[] = $file;
			}
		}

		// Add deleted files to the conflict list for now
		$conflictFiles = array_merge($conflictFiles, $deletedFiles);

		return [$mergedFiles, $conflictFiles];
	}
}
