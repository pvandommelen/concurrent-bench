<?php

namespace Pvandommelen\ConcurrentBench\Report;

use Pvandommelen\ConcurrentBench\BenchEntry;

class InMemoryAggregator implements ResultAggregator {
	/** @var BenchEntry[] */
	private array $entries = [];

	private float $total_time = 0;

	private NumberAggregator $sucess_time_taken;

	public function __construct() {
		$this->sucess_time_taken = new NumberAggregator();
	}

	public function pushEntry(BenchEntry $entry): void {
		$this->entries[] = $entry;
		if ($entry->isSuccesful() === true) {
			$this->sucess_time_taken->push($entry->getTimeTaken());
		}
	}

	public function finalize(float $total_time): void {
		$this->total_time = $total_time;
	}

	public function getTimeTaken(): NumberAggregator {
		return $this->sucess_time_taken;
	}

	public function getTotalTimeAcrossWorkers(): float {
		return $this->total_time;
	}

	public function getMeanTimeAcrossWorkers(): float {
		if (count($this->entries) === 0) {
			return 0;
		}
		return $this->getTotalTimeAcrossWorkers() / count($this->entries);
	}

	public function getIterationCount(): int {
		return count($this->entries);
	}

	public function getSuccesfulCount(): int {
		$count = 0;
		foreach ($this->entries as $entry) {
			if ($entry->isSuccesful() === true) {
				$count += 1;
			}
		}
		return $count;
	}

	public function getFailureCount(): int {
		$count = 0;
		foreach ($this->entries as $entry) {
			if ($entry->isSuccesful() === false) {
				$count += 1;
			}
		}
		return $count;
	}
}
