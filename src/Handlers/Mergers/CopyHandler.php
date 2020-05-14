<?php namespace Tatter\Patches\Handlers\Mergers;

use CodeIgniter\Config\BaseConfig;
use Tatter\Patches\Codex;
use Tatter\Patches\Handlers\BaseHandler;
use Tatter\Patches\Interfaces\MergerInterface;

class CopyHandler extends BaseHandler implements MergerInterface
{
	/**
	 * Compare files and replace as needed, tracking conflicts
	 */
	public function merge()
	{
		// Check every changed file against the destination
		foreach ($this->codex->changedFiles as $file)
		{
			$current = $this->codex->workspace . 'current/' . $file;
			$legacy  = $this->codex->workspace . 'legacy/'  . $file;
			$project = $this->codex->config->rootPath . $file;

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
				$this->codex->mergedFiles[] = $file;
			}
			// Check for a conflict (file exists but is different)
			elseif (file_exists($project))
			{
				$this->codex->conflicts['changed'][] = $file;
			}
		}

		// Try to copy in every added file
		foreach ($this->codex->addedFiles as $file)
		{
			$current = $this->codex->workspace . 'current/' . $file;
			$project = $this->codex->config->rootPath . $file;

			// Check if the project already has the new version
			if (same_file($project, $current))
			{
				continue;
			}
			// Check for a conflict
			elseif (file_exists($project))
			{
				$this->codex->conflicts['added'][] = $file;
			}
			else
			{
				copy_path($current, $project);
				$this->codex->mergedFiles[] = $file;
			}
		}

		// Try to remove deleted files
		foreach ($this->codex->deletedFiles as $file)
		{
			$legacy  = $this->codex->workspace . 'legacy/'  . $file;
			$project = $this->codex->config->rootPath . $file;

			// Check if the project has the legacy version
			if (same_file($project, $legacy))
			{
				// See if deletes are allowed
				if ($this->codex->config->allowDeletes)
				{
					unlink($project);
				}
				// WIP - For now consider this a conflict
				else
				{
					$this->codex->conflicts['deleted'][] = $file;
				}
			}
			// Check for a conflict
			elseif (file_exists($project))
			{
				$this->codex->conflicts['deleted'][] = $file;
			}
		}
	}
}
