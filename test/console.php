#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pvandommelen\ConcurrentBench\Worker\Task;
use Pvandommelen\ConcurrentBench\Worker\TaskRepository;
use Pvandommelen\ConcurrentBench\Worker\WorkerFactory;
use Symfony\Component\Console\Application;

$task_repository = new TaskRepository([
    "dummy" => new class implements Task {
	    public function initialize(): void {}
	    public function run(): void {}
    },
    "sleep" => new class implements Task {
	    public function initialize(): void {}
	    public function run(): void {
	        usleep(100 * 1000);
        }
    },
	"flock_blocking" => new class implements Task {
		public function initialize(): void {}
		public function run(): void {
		    $handle = fopen(__FILE__, 'r');
		    $success = flock($handle, LOCK_EX);
			if ($success === false) {
				throw new RuntimeException("Resource locked");
			}

			usleep(100 * 1000);
		}
	},
	"flock_nonblocking" => new class implements Task {
		public function initialize(): void {}
		public function run(): void {
			$handle = fopen(__FILE__, 'r');
			$success = flock($handle, LOCK_EX | LOCK_NB);
			if ($success === false) {
			    throw new RuntimeException("Resource locked");
            }

			usleep(100 * 1000);
		}
	},
	"normal" => new class implements Task {
		public function initialize(): void {}
		private function nrand(float $mean, float $stddev){
		    // https://en.wikipedia.org/wiki/Box%E2%80%93Muller_transform
			$x = rand()/getrandmax();
			$y = rand()/getrandmax();
			return sqrt(-2 * log($x)) * cos(2 * pi() * $y) * $stddev + $mean;
		}
		public function run(): void {
			$sleep_ms = max(0, min(100, $this->nrand(50, 10)));
			usleep($sleep_ms * 1000);
		}
	},
    "logsleep" => new class implements Task {
	    public function initialize(): void {}

	    private int $i = 0;
	    public function run(): void {
	        $this->i += 1;
	        usleep(10000 * log($this->i));
	    }
    }
]);
$app = new Application();
$app->add(new \Pvandommelen\ConcurrentBench\Worker\WorkerCommand($task_repository));
$app->add(new \Pvandommelen\ConcurrentBench\Bench\BenchCommand(new WorkerFactory(__FILE__)));
$app->run();
