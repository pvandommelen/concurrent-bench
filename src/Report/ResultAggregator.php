<?php

namespace Pvandommelen\ConcurrentBench\Report;

use Pvandommelen\ConcurrentBench\BenchEntry;

interface ResultAggregator {
	public function pushEntry(BenchEntry $entry): void;

	public function setTotalTime(float $total_time): void;
}
