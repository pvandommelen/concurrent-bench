<?php

namespace Pvandommelen\ConcurrentBench\Report;

use Pvandommelen\ConcurrentBench\BenchEntry;

class AggregateAggregator implements ResultAggregator {
	/** @var ResultAggregator[] */
	private array $aggregators;

	/**
	 * @param ResultAggregator[] $aggregators
	 */
	public function __construct($aggregators) {
		$this->aggregators = $aggregators;
	}

	public function pushEntry(BenchEntry $entry): void {
		foreach ($this->aggregators as $aggregator) {
			$aggregator->pushEntry($entry);
		}
	}

	public function finalize(float $total_time): void {
		foreach ($this->aggregators as $aggregator) {
			$aggregator->finalize($total_time);
		}
	}
}
