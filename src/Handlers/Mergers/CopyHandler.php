<?php namespace Tatter\Patches\Handlers\Mergers;

use Tatter\Patches\Interfaces\MergerInterface;
use Tatter\Patches\Patches;

class CopyHandler implements MergerInterface
{
	/**
	 * Compare files and replace as needed, tracking conflicts
	 *
	 * @param Patches $patches  Instance of the library to run against
	 */
	public function run(Patches &$patches)
	{
		$patches->patchedFiles  = [];
		$patches->conflictFiles = [];

		// Check every changed file against the destination
		foreach ($patches->changedFiles as $file)
		{
			$current = $patches->workspace . 'current/' . $file;
			$legacy  = $patches->workspace . 'legacy/'  . $file;
			$project = $patches->config->rootPath . $file;

			// Check if the project is missing this file or has the legacy version
			if (! file_exists($project) || same_file($project, $legacy))
			{
				// Copy in the new version
				copy_path($current, $project);
				$patches->patchedFiles[] = $file;
			}
			// Mark it as a conflict
			else
			{
				$patches->conflictFiles[] = $file;
			}
		}

		// Try to copy in every added file
		foreach ($patches->addedFiles as $file)
		{
			$current = $patches->workspace . 'current/' . $file;
			$project = $patches->config->rootPath . $file;

			if (is_file($project))
			{
				// See if it is already the same
				if (! same_file($project, $current))
				{
					// Mark it as a conflict
					$patches->conflictFiles[] = $file;
				}
			}
			else
			{
				copy_path($current, $project);
				$patches->patchedFiles[] = $file;
			}
		}

		// Add deleted files to the conflict list for now
		$patches->conflictFiles = array_merge($patches->conflictFiles, $patches->deletedFiles);
	}
}
