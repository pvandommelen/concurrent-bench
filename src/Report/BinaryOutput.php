<?php

namespace Pvandommelen\ConcurrentBench\Report;

use Pvandommelen\ConcurrentBench\BenchEntry;

class BinaryOutput implements ResultAggregator {
	/**
	 * @psalm-var resource|null
	 * @var resource
	 */
	private $handle;

	/**
	 * @param resource $handle
	 */
	public function __construct($handle) {
		$this->handle = $handle;
	}

	public static function createForFile(string $filepath): self {
		$handle = fopen($filepath, 'w+');
		return new self($handle);
	}

	public function pushEntry(BenchEntry $entry): void {
		assert($this->handle !== null);
		if ($entry->isSuccesful() === true) {
			$packed = pack("e", $entry->getTimeTaken());
		} else {
			$packed = pack("e", 0);
		}
		fwrite($this->handle, $packed);
	}

	public function finalize(float $total_time): void {
		assert($this->handle !== null);
		fflush($this->handle);
		$handle = $this->handle;
		$this->handle = null;
		fclose($handle);
	}
}
