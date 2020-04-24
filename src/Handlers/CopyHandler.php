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

			if (is_file($project))
			{
				// See if it is the same as the legacy version
				if ($this->isSameFile($project, $legacy))
				{
					// Replace it with the new version
					$this->copyFile($current, $project);
				}
				// Mark it as a conflict
				else
				{
					$this->conflictFiles[] = $file;
				}
			}
		}

		return [];
	}
}
