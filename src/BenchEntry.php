<?php

namespace Pvandommelen\ConcurrentBench;

class BenchEntry {
	private bool $succesful;
	private ?float $time_taken;

	private function __construct(bool $succesful, ?float $time_taken) {
		$this->succesful = $succesful;
		$this->time_taken = $time_taken;
	}

	public static function createFailed(): self {
		return new self(false, null);
	}

	public static function createSuccesful(float $time_taken): self {
		return new self(true, $time_taken);
	}

	public function isSuccesful(): bool {
		return $this->succesful;
	}

	public function getTimeTaken(): float {
		assert($this->time_taken !== null);
		return $this->time_taken;
	}
}
