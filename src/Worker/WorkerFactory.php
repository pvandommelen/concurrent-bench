<?php

namespace Pvandommelen\ConcurrentBench\Worker;

class WorkerFactory {
	private string $path_to_console;

	public function __construct(string $path_to_console) {
		$this->path_to_console = $path_to_console;
	}

	public function create(string $task_name, int $iteration_count): WorkerProxy {
		return new WorkerProxy($this->path_to_console, $task_name, $iteration_count);
	}
}
