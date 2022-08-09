<?php

use \PHPUnit\Framework\TestCase;

class Test_Command_Slug_Change extends TestCase {
	public function provide_validate_slug(): array {
		return [
			[ false, 'nbpc' ], // Reserved slug.
			[ false, 'cpbn' ], // Reserved slug.
			[ false, '0x1slug' ], // Starting with a digit.
			[ false, '_slug' ], // Starting with a underscore.
			[ false, '-slug' ], // Starting with a hyphen.
			[ false, 'Slug' ], // Uppercase letters.
			[ false, 'sl__ug' ], // Double underscores.
			[ false, 'hope--it' ], // Double hyphens.
			[ false, 'wp_' ], // Ends with underscore.
			[ false, 'wp-' ], // Ends with hyphens.
			[ false, 'a_long_slug_longer_than_25' ], // Too long!
			[ true, 'a_valid_slug' ],
			[ true, 'rgb_a0ca5a' ],
		];
	}

	/**
	 * @dataProvider provide_validate_slug
	 */
	public function test_validate_slug( bool $expected, string $slug ) {
		if ( ! $expected ) {
			$this->expectException( RuntimeException::class );
		}

		$actual = NBPC_CLI_Command_Slug_Change::validate_slug( $slug );
		$this->assertSame( $expected, $actual );
	}

	public function provide_validate_textdomain(): array {
		return [
			[ false, 'nbpc' ], // Reserved textdomain.
			[ false, 'cpbn' ], // Reserved textdomain.
			[ false, 'MyTextDomain' ], // Upperscore
			[ false, 'a_textdomain_longer_than_25' ], // Too long!
			[ false, 'textdomain-@seoul' ], // @ is not allowed.
			[ false, '_textdomain' ], // Starts with underscore.
			[ false, 'textdomain_' ], // Ends with underscore.
			[ false, '-textdomain' ], // Starts with dash.
			[ false, 'textdomain-' ], // Ends with dash.
			[ true, 'my-textdomain' ],
			[ true, 'my_textdomain' ],
		];
	}

	/** @dataProvider provide_validate_textdomain */
	public function test_validate_textdomain( bool $expected, string $textdomain ) {
		if ( ! $expected ) {
			$this->expectException( RuntimeException::class );
		}

		$actual = NBPC_CLI_Command_Slug_Change::validate_textdomain( $textdomain );
		$this->assertSame( $expected, $actual );
	}
}
