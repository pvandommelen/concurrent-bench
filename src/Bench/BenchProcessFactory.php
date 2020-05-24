<?php

namespace Pvandommelen\ConcurrentBench\Bench;

use Pvandommelen\ConcurrentBench\Worker\WorkerFactory;

class BenchProcessFactory {
	private WorkerFactory $worker_factory;

	public function __construct(WorkerFactory $worker_factory) {
		$this->worker_factory = $worker_factory;
	}
	public function create(string $task_name, int $iterations = 1, int $concurrency = 1): BenchProcess {
		return new BenchProcess($this->worker_factory, $task_name, $iterations, $concurrency);
	}
}
