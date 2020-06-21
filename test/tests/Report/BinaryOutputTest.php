<?php

use PHPUnit\Framework\TestCase;
use Pvandommelen\ConcurrentBench\BenchEntry;
use Pvandommelen\ConcurrentBench\Report\BinaryOutput;

class BinaryOutputTest extends TestCase {

	public function testFloatsAreEncodedInMemoryStream(): void {
		$handle = fopen('php://memory', 'w+');
		$aggregator = new BinaryOutput($handle);

		$aggregator->pushEntry(BenchEntry::createSuccesful(0));
		$aggregator->pushEntry(BenchEntry::createSuccesful(1));
		$aggregator->pushEntry(BenchEntry::createSuccesful(2));

		fseek($handle, 0);
		$output_string = fread($handle, 1000);
		$output_array = unpack("e*", $output_string);
		$this->assertEquals([
			0.0,
			1.0,
			2.0,
		], array_values($output_array));
	}

	public function testFloatsAreEncodedInFile(): void {
		$temp_output_file = $path = tempnam(sys_get_temp_dir(), 'bench_');
		$aggregator = BinaryOutput::createForFile($temp_output_file);

		$aggregator->pushEntry(BenchEntry::createSuccesful(0));
		$aggregator->pushEntry(BenchEntry::createSuccesful(1));
		$aggregator->pushEntry(BenchEntry::createSuccesful(2));
		$aggregator->finalize(0);

		$output_string = file_get_contents($temp_output_file);
		$output_array = unpack("e*", $output_string);
		$this->assertEquals([
			0.0,
			1.0,
			2.0,
		], array_values($output_array));
	}

}
