<?php

namespace Pvandommelen\ConcurrentBench\Worker;

use Pvandommelen\ConcurrentBench\BenchEntry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends Command {
	public const SEND_SIGNAL_READY = "Ready";
	public const RECV_SIGNAL_START = "Start";

	private TaskRepository $task_repository;

	public function __construct(TaskRepository $task_repository) {
		parent::__construct();
		$this->task_repository = $task_repository;
	}



	public static function getDefaultName(): string {
		return "bench:start-worker";
	}

	protected function configure(): void {
		$this
			->setHidden(true)
			->addArgument("task", InputArgument::REQUIRED)
			->addOption("iterations", null, InputOption::VALUE_OPTIONAL, "", 1);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$task_name = $input->getArgument("task");
		assert(is_string($task_name));
		$iterations = intval($input->getOption("iterations"));

		$task = $this->task_repository->getTask($task_name);
		$task->initialize();

		$output->writeln(self::SEND_SIGNAL_READY, Output::OUTPUT_RAW);

		while (true) {
			$received_signal = trim(fgets(STDIN, 1024));

			switch ($received_signal) {
				case self::RECV_SIGNAL_START:
					$remaining = $iterations;
					while ($remaining-- > 0) {
						$start = microtime(true);
						try {
							$task->run();
							$end = microtime(true);
							$entry = BenchEntry::createSuccesful($end - $start);
						} catch (\Throwable $e) {
							$entry = BenchEntry::createFailed();
						}
						$output->writeln(serialize($entry), Output::OUTPUT_RAW);
					}
					break 2;
				default:
					throw new \LogicException("Unknown signal `$received_signal`");
			}
		}

		return 0;
	}
}
