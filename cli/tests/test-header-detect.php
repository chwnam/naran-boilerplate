<?php

use \PHPUnit\Framework\TestCase;

class Test_Header_Detect extends TestCase {
	/**
	 * @dataProvider provide_detect()
	 */
	public function test_detect( array|false $expected, string $root_dir ) {
		$actual = NBPC_CLI_Header_Detect::detect( $root_dir );
		$this->assertEquals( $expected, $actual );
	}

	public function provide_detect(): array {
		return [
			[
				[
					'name'    => 'Test Plugin',
					'path'    => __DIR__ . '/stuffs/header-detect/plugins/test-plugin/index.php',
					'type'    => NBPC_CLI_Header_Detect::TYPE_PLUGIN,
					'version' => '0.1.0',
				],
				__DIR__ . '/stuffs/header-detect/plugins/test-plugin',
			],
			[
				[
					'name'    => 'Test Theme One',
					'path'    => __DIR__ . '/stuffs/header-detect/themes/test-theme-1/style.css',
					'type'    => NBPC_CLI_Header_Detect::TYPE_THEME,
					'version' => '1.1.0',
				],
				__DIR__ . '/stuffs/header-detect/themes/test-theme-1',

			],
			[
				[
					'name'    => 'Test Theme Two',
					'path'    => __DIR__ . '/stuffs/header-detect/themes/test-theme-2/resources/style.css',
					'type'    => NBPC_CLI_Header_Detect::TYPE_THEME,
					'version' => '1.2.0',
				],
				__DIR__ . '/stuffs/header-detect/themes/test-theme-2',
			],
		];
	}

	/**
	 * @dataProvider provide_get_header_fields()
	 */
	public function test_get_header_fields( array $expected, string $file, array $fields ) {
		$actual = NBPC_CLI_Header_Detect::get_header_fields( $file, $fields );
		$this->assertEquals( $expected, $actual );
	}

	public function provide_get_header_fields(): array {
		return [
			[
				[
					'name'    => 'Test Plugin',
					'desc'    => 'A fake plugin for unit testing.',
					'version' => '0.1.0',
				],
				__DIR__ . '/stuffs/header-detect/plugins/test-plugin/index.php',
				[
					'name'    => 'Plugin Name',
					'desc'    => 'Description',
					'version' => 'Version',
				],
			],
			[
				[
					'name'    => 'Test Theme One',
					'version' => '1.1.0',
				],
				__DIR__ . '/stuffs/header-detect/themes/test-theme-1/style.css',
				[
					'name'    => 'Theme Name',
					'version' => 'Version',
				],
			],
			[
				[
					'name'    => 'Test Theme Two',
					'version' => '1.2.0',
				],
				__DIR__ . '/stuffs/header-detect/themes/test-theme-2/resources/style.css',
				[
					'name'    => 'Theme Name',
					'version' => 'Version',
				],
			],
		];
	}

	/**
	 * @dataProvider provide_extract_header()
	 */
	public function test_extract_header( string $content, array $cases ) {
		foreach ( $cases as $case ) {
			$actual = NBPC_CLI_Header_Detect::extract_header( $content, $case['header'] );
			$this->assertEquals( $case['expected'], $actual, $case['header'] );
		}
	}

	public function provide_extract_header(): array {
		return [
			[
				'content' => '<?' . "php\n" .
				             "/** Plugin Name: Test Plugin\n" .
				             " * Foo:       Bar       \n" .
				             " * Version: 1.0.0 */\n",
				'cases'   => [
					[
						'expected' => [ 'Test Plugin', 23 ],
						'header'   => 'Plugin Name',
					],
					[
						'expected' => [ 'Bar', 49 ],
						'header'   => 'Foo',
					],
					[
						'expected' => [ '1.0.0', 72 ],
						'header'   => 'Version',
					],
				],
			],
			[
				'content' => "/*\n" .
				             "Plugin Name:     Test Plugin    \n" .
				             "Foo:\t\tBar\t\t\n" .
				             "Version:  1.0.0  \n" .
				             "*/\n",
				'cases'   => [
					[
						'expected' => [ 'Test Plugin', 20 ],
						'header'   => 'Plugin Name',
					],
					[
						'expected' => [ 'Bar', 42 ],
						'header'   => 'Foo',
					],
					[
						'expected' => [ '1.0.0', 58 ],
						'header'   => 'Version',
					],
				],
			],
		];
	}
}
