<?php namespace Tatter\Patches\Test;

use Tatter\Patches\Patches;

class MockPatches extends Patches
{
	/**
	 * Pretend to copy each path to its relative destination.
	 *
	 * @param string $destination  Directory to copy files into
	 *
	 * @return array  Array of the actual new file paths
	 */
	public function copyPaths(string $destination): array
	{
		$filenames   = [];
		$destination = rtrim($destination, '/') . '/';

		// Record each file and its relative destination
		foreach ($this->gatherPaths() as $path)
		{
			$filenames[] = $destination . $path['to'];
		}

		return $filenames;
	}

	/**
	 * Use the mock handler to fake an update to vendor sources.
	 *
	 * @return bool  True if the update succeeds
	 */
	public function update(): bool
	{
		// Force the MockUpdater handler
		$tmpUpdater = $this->config->updater;
		$this->config->updater = 'Tatter\Patches\Test\MockUpdater';

		$result = parent::update();

		// Restore the handler
		$this->config->updater = $tmpUpdater;

		return $result;
	}

	/**
	 * Run the patch handler and call the postpatch event (as needed)
	 *
	 * @return bool  Success or failure
	 */
	public function merge(): bool
	{
		// Force the MockMerger handler
		$tmpMerger = $this->config->updater;
		$this->config->merger = 'Tatter\Patches\Test\MockMerger';

		$result = parent::merge();

		// Restore the handler
		$this->config->merger = $tmpMerger;

		return $result;
	}
}
