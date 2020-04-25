<?php namespace Tatter\Patches\Handlers;

use Tatter\Patches\BaseHandler;
use Tatter\Patches\Interfaces\HandlerInterface;

class CopyHandler extends BaseHandler implements HandlerInterface
{
	/**
	 * Compare files and replace as needed, tracking conflicts
	 */
	public function patch(string $destination = null): array
	{
		$this->patchedFiles  = [];
		$this->conflictFiles = [];

		if (is_null($destination))
		{
			$destination = ROOTPATH;
		}
		$destination = rtrim($destination, '/') . '/';

		// Check every changed file against the destination
		foreach ($this->changedFiles as $file)
		{
			$current = $this->workspace . 'current/' . $file;
			$legacy  = $this->workspace . 'legacy/'  . $file;
			$project = $destination . $file;

			// Check if the project is missing this file or has the legacy version
			if (! file_exists($project) || same_file($project, $legacy))
			{
				// Copy in the new version
				copy_path($current, $project);
				$this->patchedFiles[] = $file;
			}
			// Mark it as a conflict
			else
			{
				$this->conflictFiles[] = $file;
			}
		}

		// Try to copy in every added file
		foreach ($this->addedFiles as $file)
		{
			$current = $this->workspace . 'current/' . $file;
			$project = $destination . $file;

			if (is_file($project))
			{
				// See if it is already the same
				if (! same_file($project, $current))
				{
					// Mark it as a conflict
					$this->conflictFiles[] = $file;
				}
			}
			else
			{
				copy_path($current, $project);
				$this->patchedFiles[] = $file;
			}
		}

		// Add deleted files to the conflict list for now
		$this->conflictFiles = array_merge($this->conflictFiles, $this->deletedFiles);

		return [];
	}
}
