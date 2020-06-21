<?php

namespace Pvandommelen\ConcurrentBench\Report;

use Pvandommelen\ConcurrentBench\BenchEntry;

interface ResultAggregator {
	public function pushEntry(BenchEntry $entry): void;

	public function finalize(float $total_time): void;
}
