<?php

namespace Pvandommelen\ConcurrentBench\Worker;

interface Task {

	/**
	 * Called once when the worker is still initializing, not part of the execution time
	 */
	public function initialize(): void;

	/**
	 * Gets called repeatedly
	 */
	public function run(): void;
}
