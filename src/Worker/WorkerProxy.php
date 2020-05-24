<?php

namespace Pvandommelen\ConcurrentBench\Worker;

use Pvandommelen\ConcurrentBench\BenchEntry;
use Pvandommelen\ConcurrentBench\BenchMarkingException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class WorkerProxy {
	private Process $process;
	private InputStream $input_stream;

	public function __construct(string $path_to_console, string $task_name, int $worker_iterations) {
		$this->init($path_to_console, $task_name, $worker_iterations);
	}

	private function init(string $path_to_console, string $task_name, int $worker_iterations): void {
		$this->input_stream = new InputStream();
		$php_executable_finder = new PhpExecutableFinder();
		$this->process = new Process(
			[$php_executable_finder->find(), $path_to_console, 'bench:start-worker', $task_name, '--iterations', $worker_iterations],
			null,
			null,
			$this->input_stream
		);
		$this->process->start();
		while (true) {
			$this->assertProcessNoErrors();
			$signal = trim($this->process->getIncrementalOutput());
			switch ($signal) {
				case "":
					usleep(100 * 1000);
					break;
				case WorkerCommand::SEND_SIGNAL_READY:
					return;
					break;
				default:
					throw new \LogicException("Unknown signal `$signal`");
			}
		}
	}

	private function assertProcessNoErrors(): void {
		$error_output = trim($this->process->getIncrementalErrorOutput());
		if ($error_output !== "") {
			throw new BenchMarkingException("Worker failed to initialize: $error_output");
		}
	}

	public function start(): void {
		$this->input_stream->write(WorkerCommand::RECV_SIGNAL_START . "\n");
	}

	public function isComplete(): bool {
		return $this->process->isRunning() === false;
	}

	/**
	 * @return BenchEntry[]
	 * @psalm-return list<BenchEntry>
	 */
	public function poll(): array {
		$output = trim($this->process->getIncrementalOutput());
		if ($output === "") {
			return [];
		}
		$serialized_entries = explode("\n", $output);

		$poll_results = [];
		foreach ($serialized_entries as $serialized_entry) {
			/** @var mixed $entry */
			$entry = @unserialize($serialized_entry);
			if ($entry === false) {
				throw new BenchMarkingException("Unable to deserialize message `$serialized_entry`");
			}
			if (is_object($entry) === false || !$entry instanceof BenchEntry) {
				throw new BenchMarkingException("Expected only messages of type BenchEntry `$serialized_entry`");
			}
			$poll_results[] = $entry;
		}
		return $poll_results;
	}

}
