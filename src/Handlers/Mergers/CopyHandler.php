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

			// Check if the project already has the new version
			if (same_file($project, $current))
			{
				continue;
			}
			// Check for missing file, or a copy of the legacy file
			elseif (! file_exists($project) || same_file($project, $legacy))
			{
				// Copy in the new version
				copy_path($current, $project);
				$codex->mergedFiles[] = $file;
			}
			// Check for a conflict (file exists but is different)
			elseif (file_exists($project))
			{
				$codex->conflicts['changed'][] = $file;
			}
		}

		// Try to copy in every added file
		foreach ($codex->addedFiles as $file)
		{
			$current = $codex->workspace . 'current/' . $file;
			$project = $codex->config->rootPath . $file;

			// Check if the project already has the new version
			if (same_file($project, $current))
			{
				continue;
			}
			// Check for a conflict
			elseif (file_exists($project))
			{
				$codex->conflicts['added'][] = $file;
			}
			else
			{
				copy_path($current, $project);
				$codex->mergedFiles[] = $file;
			}
		}

		// Try to remove deleted files
		foreach ($codex->deletedFiles as $file)
		{
			$legacy  = $codex->workspace . 'legacy/'  . $file;
			$project = $codex->config->rootPath . $file;

			// Check if the project has the legacy version
			if (same_file($project, $legacy))
			{
				// See if deletes are allowed
				if ($codex->config->allowDeletes)
				{
					unlink($project);
				}
				// WIP - For now consider this a conflict
				if ($codex->config->allowDeletes)
				{
					$codex->conflicts['deleted'][] = $file;
				}
			}
			// Check for a conflict
			elseif (file_exists($project))
			{
				$codex->conflicts['deleted'][] = $file;
			}
		}
	}
}
