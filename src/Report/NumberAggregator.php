<?php

namespace Pvandommelen\ConcurrentBench\Report;

class NumberAggregator {
	/** @var float[] */
	private array $entries = [];

	public function push(float $number): void {
		$this->entries[] = $number;
	}

	public function getSum(): float {
		// todo: Handle floating point errors
		$sum = 0;
		foreach ($this->entries as $entry) {
			$sum += $entry;
		}
		return $sum;
	}

	public function getCount(): int {
		return count($this->entries);
	}

	public function getMean(): float {
		$count = $this->getCount();
		if ($count === 0) {
			return 0;
		}
		return $this->getSum() / $count;
	}

	public function getStandardDeviation(): float {
		$count = $this->getCount();
		if ($count === 0) {
			return 0;
		}

		$mean = $this->getMean();

		$variance_sum = 0;
		foreach ($this->entries as $entry) {
			$variance_sum += ($entry - $mean) * ($entry - $mean);
		}

		return sqrt($variance_sum / ($count - 1));
	}
}
