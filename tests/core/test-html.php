<?php
/**
 * Naran Boilerplate Core Tests
 *
 * tests/test-html.php
 */
class_alias( NBPC_HTML::class, 'HTML' );

class Test_HTML extends WP_UnitTestCase {
	public function test_enclose() {
		$this->assertEquals( '"foo"', HTML::enclose( 'foo' ) );

		$this->assertEquals( '"&quot;&#039;&lt;&gt;"', HTML::enclose( '"\'<>' ) );

		$this->assertEquals( '"&quot;&quot;&#039;&lt;&gt;&quot;"', HTML::enclose( HTML::enclose( '"\'<>' ) ) );
	}

	public function test_attrs() {
		$attrs = HTML::attrs(
			[
				'id'       => 'id',
				'name'     => 'name',
				'accept'   => [ 'text/plain', 'image/jpeg', 'application/json' ],
				'class'    => 'class-1 class-2 class-1 class-3',
				'href'     => 'https://sample.com',
				'selected' => true,
				'value'    => 'value',
			]
		);
		$this->assertEquals(
			'id="id" name="name" accept="text/plain, image/jpeg, application/json" class="class-1 class-2 class-3" href="https://sample.com" selected="selected" value="value"',
			$attrs
		);

		$attrs = HTML::attrs( [ 'value' => '0' ] );
		$this->assertEquals( 'value="0"', $attrs );

		$attrs = HTML::attrs( [ 'selected' => '' ] );
		$this->assertEquals( 'selected', $attrs );
	}

	public function test_tag_open() {
		$this->assertEquals(
			'<table data-label="LABEL" id="table" class="form-table">',
			HTML::tag_open(
				'table',
				[
					'data-label' => 'LABEL',
					'id'         => 'table',
					'class'      => 'form-table',
				],
				false
			)
		);
	}

	public function test_tag_close() {
		$this->assertEquals( '</table>', HTML::tag_close( 'table', false ) );
	}

	public function test_option() {
		$this->assertEquals(
			'<option aria-label="Our store coffee" value="coffee" selected="selected">Coffee</option>',
			HTML::option(
				'coffee',
				'Coffee',
				true,
				[ 'aria-label' => 'Our store coffee' ]
			)
		);

		$this->assertEquals(
			'<option value="0">0</option>',
			HTML::option( 0, '0', false, [] )
		);
	}

	public function test_select() {
		$this->assertEquals(
			'<select id="id" name="name"><option value="0">0</option><option class="opt" value="1" selected="selected">1</option></select>',
			HTML::select(
				[ 0 => '0', 1 => '1' ],
				1,
				[ 'id' => 'id', 'name' => 'name' ],
				[ 1 => [ 'class' => 'opt' ] ]
			)
		);

		$this->assertEquals(
			'<select><optgroup label="A"><option value="0">0</option><option value="1">1</option></optgroup><optgroup label="B"><option value="2">2</option><option value="3">3</option></optgroup></select>',
			HTML::select(
				[
					'A' => [ 0 => '0', 1 => '1' ],
					'B' => [ 2 => '2', 3 => '3' ],
				], '', [], [] )
		);
	}

	public function test_input() {
		$this->assertEquals(
			'<input id="foo" name="foo" type="text" class="text large-text" value="foo"/>',
			HTML::input( [
				'id'    => 'foo',
				'name'  => 'foo',
				'type'  => 'text',
				'class' => 'text large-text',
				'value' => 'foo',
			] )
		);
	}

	public function test_nested() {
		$this->assertEquals(
			'<p>The paragraph</p>',
			HTML::nested( 'p', [], 'The paragraph' )
		);

		$this->assertEquals(
			'<ul><li>X</li><li>Y</li><li>Z</li></ul>',
			HTML::nested( 'ul', [],
				HTML::nested( 'li', [], 'X' ),
				[
					HTML::nested( 'li', [], 'Y' ),
					HTML::nested( 'li', [], 'Z' ),
				]
			)
		);
	}

	public function test_kses_input() {
		$this->assertEquals(
			'<input id="foo" name="foo" type="text" class="text large-text" value="foo" />',
			HTML::kses_input(
				HTML::input( [
					'id'        => 'foo',
					'name'      => 'foo',
					'type'      => 'text',
					'class'     => 'text large-text',
					'value'     => 'foo',
					'evil-prop' => '?',
				] )
			)
		);
	}

	public function test_kses_select() {
		$this->assertEquals(
			'<select data-x="1"><optgroup label="A"><option value="0">0</option><option value="1">1</option></optgroup><optgroup label="B"><option value="2">2</option><option value="3">3</option></optgroup></select>Button//JavascriptCode',
			HTML::kses_select(
				HTML::select(
					[
						'A' => [ 0 => '0', 1 => '1' ],
						'B' => [ 2 => '2', 3 => '3' ],
					],
					'',
					[ 'data-x' => '1' ],
					[ 'A' => [ 'not-allowed' => 'no' ] ]
				) . '<button>Button</button><script>//JavascriptCode</script>',
				[
					'select' => [ 'data-x' => true ],
				]
			)
		);
	}

	public function test_kses_nested() {
		$this->assertEquals(
			'<div id="foo" class="foo-container"><p>Nested Content</p></div>',
			HTML::kses_nested(
				HTML::nested(
					'div',
					[
						'id'    => 'foo',
						'class' => 'foo-container',
					],
					HTML::nested( 'p', [], "Nested Content" )
				),
				[
					'div' => [],
					'p'   => [],
				]
			)
		);
	}
}
