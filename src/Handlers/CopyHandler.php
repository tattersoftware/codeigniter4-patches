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
		if (is_null($destination))
		{
			$destination = ROOTPATH;
		}
		$destination = rtrim($destination, '/') . '/';

		// Check every current file against the destination
		foreach ($this->gatherPaths() as $path)
		{
			$legacy  = $this->workspace . 'legacy/'  . $path['to'];
			$current = $this->workspace . 'current/' . $path['to'];
			$project = $destination . $path['to'];

			if (is_file($project))
			{
				// Check it versus the legacy version
				if ($this->isSameFile($current, $legacy))
				{
					
				}
			}
		}
	}
}
