<?php

use \PHPUnit\Framework\TestCase;

class Test_Textdomain_Replace extends TestCase {
	public function provide_replace(): array {
		return [
			[
				'nbpc',
				'smpl',
				__DIR__ . '/stuffs/textdomain-replace-input-1.php',
				__DIR__ . '/stuffs/textdomain-replace-answer-1.php',
			],
		];
	}

	/** @dataProvider provide_replace */
	public function test_replace(
		string $old_textdomain,
		string $new_textdomain,
		string $input_file,
		string $answer_file
	) {
		$instance = new NBPC_CLI_Textdomain_Replace( $old_textdomain, $new_textdomain );

		$input  = file_get_contents( $input_file );
		$answer = file_get_contents( $answer_file );

		$output = $instance->replace( $input );
		$this->assertEquals( $answer, $output );
	}
}
