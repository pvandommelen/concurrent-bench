<?php

namespace Pvandommelen\ConcurrentBench\Test\Bench;

use PHPUnit\Framework\TestCase;
use Pvandommelen\ConcurrentBench\Bench\BenchProcessFactory;
use Pvandommelen\ConcurrentBench\Report\InMemoryAggregator;
use Pvandommelen\ConcurrentBench\Worker\WorkerFactory;

class BenchProcessTest extends TestCase {

	private BenchProcessFactory $process_factory;

	protected function setUp(): void {
		$this->process_factory = new BenchProcessFactory(new WorkerFactory(__DIR__ . "\..\..\console.php"));
	}

	public function testSingleTask() {
		$process = $this->process_factory->create("dummy");

		$aggregator = new InMemoryAggregator();
		$process->execute($aggregator);

		$this->assertEquals(1, $aggregator->getIterationCount());
	}

	public function testManyTasks() {
		$process = $this->process_factory->create("dummy", 3);

		$aggregator = new InMemoryAggregator();
		$process->execute($aggregator);

		$this->assertEquals(3, $aggregator->getIterationCount());
	}

	public function testManyTasksConcurrently() {
		$process = $this->process_factory->create("dummy", 6, 2);

		$aggregator = new InMemoryAggregator();
		$process->execute($aggregator);

		$this->assertEquals(6, $aggregator->getIterationCount());
	}

	public function testUnevenTasksGetDistributed() {
		$process = $this->process_factory->create("dummy", 5, 3);

		$aggregator = new InMemoryAggregator();
		$process->execute($aggregator);

		$this->assertEquals(5, $aggregator->getIterationCount());
	}

	public function testCanHandleFailures() {
		$process = $this->process_factory->create("flock_nonblocking", 2, 2);

		$aggregator = new InMemoryAggregator();
		$process->execute($aggregator);

		$this->assertEquals(2, $aggregator->getIterationCount());
		$this->assertEquals(1, $aggregator->getSuccesfulCount());
		$this->assertEquals(1, $aggregator->getFailureCount());
	}
}
