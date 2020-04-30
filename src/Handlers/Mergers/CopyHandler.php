<?php namespace Tatter\Patches\Handlers\Mergers;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Codex;
use Tatter\Patches\Interfaces\MergerInterface;
use Tatter\Patches\Patches;

class CopyHandler implements MergerInterface
{
	/**
	 * Compare files and replace as needed, tracking conflicts
	 *
	 * @param Codex $codex
	 */
	public function run(Codex &$codex)
	{
		// Check every changed file against the destination
		foreach ($codex->changedFiles as $file)
		{
			$current = $codex->workspace . 'current/' . $file;
			$legacy  = $codex->workspace . 'legacy/'  . $file;
			$project = $codex->config->rootPath . $file;

			// Check if the project is missing this file or has the legacy version
			if (! file_exists($project) || same_file($project, $legacy))
			{
				// Copy in the new version
				copy_path($current, $project);
				$codex->mergedFiles[] = $file;
			}
			// Mark it as a conflict
			else
			{
				$codex->conflictFiles[] = $file;
			}
		}

		// Try to copy in every added file
		foreach ($codex->addedFiles as $file)
		{
			$current = $codex->workspace . 'current/' . $file;
			$project = $codex->config->rootPath . $file;

			if (is_file($project))
			{
				// See if it is already the same
				if (! same_file($project, $current))
				{
					// Mark it as a conflict
					$codex->conflictFiles[] = $file;
				}
			}
			else
			{
				copy_path($current, $project);
				$codex->mergedFiles[] = $file;
			}
		}

		// Add deleted files to the conflict list for now
		$codex->conflictFiles = array_merge($codex->conflictFiles, $codex->deletedFiles);
	}
}
