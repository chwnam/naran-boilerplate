<?php

use \PHPUnit\Framework\TestCase;

class Test_Gitingore_Parse extends TestCase {
	public function provide_trim_line(): array {
		return [
			[ '# Commment', '' ], // Comment.
			[ '    # Comm', '' ], // Heading space and comment.
			[ ' \#abcd', '#abcd' ], // Heading hash is preserved.
			[ ' \?abcd', '?abcd' ], // Heading question mark is preserved.
			[ ' \!abcd', '!abcd' ], // Heading exclamation mark is preserved.
			[ 'abcd    ', 'abcd' ],
			[ 'abcd\\ ', 'abcd ' ],
			[ 'abcd \\ ', 'abcd  ' ],
		];
	}

	/**
	 * @dataProvider provide_trim_line
	 */
	public function test_trim_line( string $line, string $expected ) {
		$parser = new NBPC_CLI_Gitignore_Parse();
		$actual = $parser->trim_line( $line );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @dataProvider provider_line_to_regex
	 */
	public function test_line_to_regex( array $expected, string $input ) {
		$parser = new NBPC_CLI_Gitignore_Parse();
		$actual = $parser->line_to_regex_pattern( $input );
		$this->assertEquals( $expected, $actual );
	}

	public function provider_line_to_regex(): array {
		return [
			[
				[
					'pattern' => '/\.git(?:/|$)',
					'negated' => false,
				],
				'.git',
			],
			[
				[
					'pattern' => '/[^/]*\.bak(?:/|$)',
					'negated' => false,
				],
				'*.bak',
			],
			[
				[
					'pattern' => '^/assets/(?:|.+/)[^/]*\.js\.map(?:/|$)',
					'negated' => false,
				],
				'/assets/**/*.js.map',
			],
			[
				[
					'pattern' => '^/bin/vendor(?:/|$)',
					'negated' => false,
				],
				'/bin/vendor',
			],
			[
				[
					'pattern' => '^/vendor/[^/]*(?:/|$)',
					'negated' => false,
				],
				'/vendor/*',
			],
			[
				[
					'pattern' => '/[^/]*[0-9A-Za-z]\.[jt]sx(?:/|$)',
					'negated' => false,
				],
				'*[0-9A-Za-z].[jt]sx',
			],
			[
				[
					'pattern' => '/[^a-c](?:/|$)',
					'negated' => false,
				],
				'[!a-c]',
			],
			[
				[
					'pattern' => '/[\]-](?:/|$)',
					'negated' => false,
				],
				'[]-]',
			],
			[
				[
					'pattern' => '^.*/README\.md(?:/|$)',
					'negated' => false,
				],
				'**/README.md',
			],
			[
				[
					'pattern' => '/dir/',
					'negated' => false,
				],
				'dir/',
			],
			[
				[
					'pattern' => '^/doc/.+',
					'negated' => false,
				],
				'doc/**',
			],
			[
				[
					'pattern' => '^.*/doc/.+',
					'negated' => false,
				],
				'**/doc/**',
			],
			[
				[
					'pattern' => '^/doc/(?:|.+/)README\.md(?:/|$)',
					'negated' => false,
				],
				'doc/**/README.md',
			],
		];
	}

	/**
	 * @dataProvider provider_match
	 */
	public function test_match( string $gitignore, array $cases ) {
		$p = new NBPC_CLI_Gitignore_Parse();
		$p->parse( $gitignore );

		foreach ( $cases as [$expected, $path] ) {
			$actual = $p->match( $path );
			$this->assertEquals( $expected, $actual, $path );
		}
	}

	public function provider_match(): array {
		return [
			[
				__DIR__ . '/stuffs/gitignore-1.txt',
				[
					// frotz
					[ true, '/frotz' ],
					[ true, '/a/frotz/' ],
					[ true, '/a/b/frotz' ],
					[ true, '/a/frotz/b' ],
					[ false, '/gfrotz' ],
					[ false, '/a/gfrotz' ],
					[ false, '/frotzy' ],
					[ false, '/a/frotzy' ],

					// tv-show/simpson
					[ true, '/tv-show/simpson' ],
					[ true, '/tv-show/simpson/' ],
					[ true, '/tv-show/simpson/bart.txt' ],
					[ true, '/tv-show/simpson/games/konami.txt' ],
					[ false, '/favorite/tv-show/simpson' ],
					[ false, '/tv-show/simpsons' ],

					// /radio/bbc
					[ true, '/radio/channel/' ],
					[ true, '/radio/channel/bbc' ],
					[ false, '/media/radio/channel' ],

					// dir/
					[ true, '/dir/' ],
					[ true, '/dir/s' ],
					[ true, '/a/dir/' ],
					[ false, '/dir' ],
					[ false, '/a/dir' ],

					// doc/**/README.md
					[ true, '/doc/README.md' ],
					[ true, '/doc/a/b/README.md' ],
					[ true, '/doc/README.md/2019-version.md' ],
					[ false, '/a/b/c/doc/README.md5' ],
					[ false, '/a/b/c/doc/x/y/zREADME.md5' ],

					// **/actions
					[ true, '/actions' ],
					[ true, '/a/actions' ],
					[ true, '/a/b/actions' ],
					[ true, '/a/b/actions/init' ],
					[ false, '/actions-init' ],

					// filters/**
					[ true, '/filters/a' ],
					[ true, '/filters/a/b' ],
					[ true, '/filters/a/b/c' ],
					[ true, '/filters/a/b/c/' ],
					[ false, '/filters' ],

					// hooks/**/names.js
					[ true, '/hooks/names.js' ],
					[ true, '/hooks/x/names.js' ],
					[ true, '/hooks/x/y/names.js' ],
					[ true, '/hooks/x/y/names.js/version' ],

					// excel/*.xlsx
					[ true, '/excel/foo.xlsx' ],
					[ true, '/excel/foo.xlsx/2010.txt' ],
					[ false, '/excel/foo.xls' ],

					// database/?nap.db
					[ true, '/database/snap.db' ],
					[ true, '/database/qnap.db' ],
					[ false, '/database/nap.db' ],
					[ false, '/database/panap.db' ],

					// fnmatch/*.[tj]sx.map
					[ true, '/fnmatch/tailor.tsx.map' ],
					[ true, '/fnmatch/tailor.jsx.map' ],
					[ false, '/fnmatch/tailor.csx.map' ],

					//fnmatch/[!ap]t.js
					[ true, '/fnmatch/ct.js' ],
					[ false, '/fnmatch/t.js' ],
					[ false, '/fnmatch/at.js' ],
					[ false, '/fnmatch/pt.js' ],
				],
			],
		];
	}
}