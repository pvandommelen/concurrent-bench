<?php

namespace Pvandommelen\ConcurrentBench\Bench;

use Pvandommelen\ConcurrentBench\BenchMarkingException;
use Pvandommelen\ConcurrentBench\Report\ResultAggregator;
use Pvandommelen\ConcurrentBench\Worker\WorkerFactory;
use Pvandommelen\ConcurrentBench\Worker\WorkerProxy;

class BenchProcess {
	private WorkerFactory $worker_factory;
	private string $task_name;
	private int $total_iterations;
	private int $concurrency;

	public function __construct(WorkerFactory $worker_factory, string $task_name, int $total_iterations, int $concurrency) {
		$this->worker_factory = $worker_factory;
		$this->task_name = $task_name;
		$this->total_iterations = $total_iterations;
		$this->concurrency = $concurrency;
	}

	public function execute(ResultAggregator $aggregator): void {
		/** @var WorkerProxy[] $workers */
		$workers = [];
		for ($i = 0; $i < $this->concurrency; ++$i) {
			$worker_iterations = intval($this->total_iterations / $this->concurrency);
			if ($this->total_iterations % $this->concurrency > $i) {
				$worker_iterations += 1;
			}
			$workers[] = $this->worker_factory->create($this->task_name, $worker_iterations);
		}

		$start = microtime(true);
		foreach ($workers as $worker) {
			$worker->start();
		}

		$messages_returned = 0;
		while (true) {
			$all_done = true;
			foreach ($workers as $worker) {
				$messages = $worker->poll();
				foreach ($messages as $message) {
					$aggregator->pushEntry($message);
					$messages_returned += 1;
				}

				$all_done = $all_done && $worker->isComplete();
			}

			if ($all_done === true) {
				break;
			}
		}

		$end = microtime(true);

		if ($messages_returned !== $this->total_iterations) {
			throw new BenchMarkingException("Number of executed iterations `$messages_returned` did not match expected number of iterations `$this->total_iterations`");
		}

		$aggregator->setTotalTime($end - $start);
	}
}
