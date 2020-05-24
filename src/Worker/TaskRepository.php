<?php

namespace Pvandommelen\ConcurrentBench\Worker;

use Pvandommelen\ConcurrentBench\BenchMarkingException;

class TaskRepository {
	/** @var Task[] */
	private array $tasks;

	/**
	 * @param Task[] $tasks
	 */
	public function __construct(array $tasks) {
		$this->tasks = $tasks;
	}

	public function getTask(string $name): Task {
		if (isset($this->tasks[$name]) === false) {
			throw new BenchMarkingException("Task not found `$name`");
		}
		return $this->tasks[$name];
	}
}
