<?php

namespace Pvandommelen\ConcurrentBench\Bench;

use Pvandommelen\ConcurrentBench\BenchEntry;
use Pvandommelen\ConcurrentBench\Report\ResultAggregator;
use Symfony\Component\Console\Helper\ProgressBar;

class ProgressBarAggregator implements ResultAggregator {
	private ResultAggregator $internal;
	private ProgressBar $progress_bar;

	public function __construct(ResultAggregator $internal, ProgressBar $progress_bar) {
		$this->internal = $internal;
		$this->progress_bar = $progress_bar;
	}

	public function pushEntry(BenchEntry $entry): void {
		$this->internal->pushEntry($entry);
		$this->progress_bar->advance(1);
	}

	public function finalize(float $total_time): void {
		$this->internal->finalize($total_time);
		$this->progress_bar->finish();
	}
}
