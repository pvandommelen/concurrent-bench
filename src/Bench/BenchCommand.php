<?php

namespace Pvandommelen\ConcurrentBench\Bench;

use Pvandommelen\ConcurrentBench\Report\AggregateAggregator;
use Pvandommelen\ConcurrentBench\Report\InMemoryAggregator;
use Pvandommelen\ConcurrentBench\Worker\WorkerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BenchCommand extends Command {
	private WorkerFactory $worker_factory;

	public function __construct(WorkerFactory $worker_factory) {
		parent::__construct();
		$this->worker_factory = $worker_factory;
	}

	public static function getDefaultName(): string {
		return "bench:bench";
	}

	protected function configure(): void {
		$this
			->addArgument("task", InputArgument::REQUIRED)
			->addOption("iterations", "-i", InputOption::VALUE_OPTIONAL, "Total number of iterations to run", 1)
			->addOption("concurrency", "-c", InputOption::VALUE_OPTIONAL, "Number of concurrent workers to use", 1);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$task_name = $input->getArgument("task");
		assert(is_string($task_name));
		$total_iterations = intval($input->getOption("iterations"));
		$concurrency = intval($input->getOption("concurrency"));

		$process = new BenchProcess($this->worker_factory, $task_name, $total_iterations, $concurrency);

		$progress = new ProgressBar($output);
		$memory_aggregator = new InMemoryAggregator();
		$aggregator = new AggregateAggregator([
			$memory_aggregator,
			new ProgressBarAggregator($progress),
		]);

		$progress->start($total_iterations);
		$process->execute($aggregator);

		$output->writeln("");

		$table = new Table($output);
		$table->addRow([ "Failure rate", sprintf("%d/%d", $memory_aggregator->getFailureCount(), $memory_aggregator->getIterationCount()) ]);
		$table->addRow([ "Total time", $memory_aggregator->getTimeTaken()->getSum() ]);
		$table->addRow([ "Mean time per iteration", sprintf("%.3f +- %3f", $memory_aggregator->getTimeTaken()->getMean(), $memory_aggregator->getTimeTaken()->getStandardDeviation()) ]);
		$table->addRow([ "Total time across workers", $memory_aggregator->getTotalTimeAcrossWorkers() ]);
		$table->addRow([ "Mean time per iteration across workers", $memory_aggregator->getMeanTimeAcrossWorkers() ]);
		$table->render();

		return 0;
	}
}
