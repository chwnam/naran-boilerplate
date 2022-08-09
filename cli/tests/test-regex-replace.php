<?php

use \PHPUnit\Framework\TestCase;

class Test_Regex_Replace extends TestCase {
	public function provide_replace(): array {
		return [
			[
				'nbpc',
				'smpl',
				__DIR__ . '/stuffs/regex-replace-input-1.php',
				__DIR__ . '/stuffs/regex-replace-answer-1.php',
			],
			[
				'nbpc',
				'smpl',
				__DIR__ . '/stuffs/regex-replace-input-2.php',
				__DIR__ . '/stuffs/regex-replace-answer-2.php',
			],
		];
	}

	/** @dataProvider provide_replace */
	public function test_replace(
		string $old_slug,
		string $new_slug,
		string $input_file,
		string $answer_file
	) {
		$instance = new NBPC_CLI_Regex_Replace( $old_slug, $new_slug );

		$input  = file_get_contents( $input_file );
		$answer = file_get_contents( $answer_file );

		$output = $instance->replace( $input );
		$this->assertEquals( $answer, $output );
	}
}
