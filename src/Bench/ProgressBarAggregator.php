<?php

namespace Pvandommelen\ConcurrentBench\Bench;

use Pvandommelen\ConcurrentBench\BenchEntry;
use Pvandommelen\ConcurrentBench\Report\ResultAggregator;
use Symfony\Component\Console\Helper\ProgressBar;

class ProgressBarAggregator implements ResultAggregator {
	private ProgressBar $progress_bar;

	public function __construct(ProgressBar $progress_bar) {
		$this->progress_bar = $progress_bar;
	}

	public function pushEntry(BenchEntry $entry): void {
		$this->progress_bar->advance(1);
	}

	public function finalize(float $total_time): void {
		$this->progress_bar->finish();
	}
}
