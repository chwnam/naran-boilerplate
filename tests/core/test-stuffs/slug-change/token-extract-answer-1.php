<?php
/**
 * File for slug change testing.
 *
 * Upper slug: SMPL, for class, interface, trait names.
 * Lower slug: smpl, for variable, method names.
 *
 * Sluges will be replaced only if they are used as whole words.
 * e.g. Replaced: SMPL, smpl, SMPL-Foo, SMPL_Foo, Foo-SMPL, Foo_SMPL, smpl-foo, smpl_foo, get_smpl, get_smpl_some
 *      Ignored:  NBPCFoo, FooNBPC, nbpcfoo, foonbpc
 */

if ( ! interface_exists( 'SMPL_ID_Interface' ) ) {
	interface SMPL_ID_Interface {
		public function get_id(): int;
	}
}

if ( ! interface_exists( 'SMPL_Minor_Interface' ) ) {
	interface SMPL_Minor_Interface {
		public function do_minor_thing();
	}
}

if ( ! interface_exists( 'SMPL_Side_Interface' ) ) {
	interface SMPL_Side_Interface {
		public function do_side_thing();
	}
}

if ( ! interface_exists( 'SMPL_Major_Interface' ) ) {
	interface SMPL_Major_Interface extends SMPL_Minor_Interface, SMPL_ID_Interface {
		public function do_major_thing();
	}
}

if ( ! class_exists( 'SMPL_Major_Impl' ) ) {
	class SMPL_Major_Impl implements SMPL_Major_Interface {
		public function __construct( public int $id ) {
		}

		public function do_minor_thing() {
			echo "Minor\n";
		}

		public function do_major_thing() {
			echo "Major: {$this->get_id()}\n";
		}

		public function get_id(): int {
			return $this->id;
		}
	}
}

if ( ! class_exists( 'SMPL_Side_Impl' ) ) {
	class SMPL_Side_Impl implements SMPL_Side_Interface, SMPL_ID_Interface {
		public function __construct( public int $id ) {
		}

		public function do_side_thing() {
			echo "Side: {$this->get_id()}\n";
		}

		public function get_id(): int {
			return $this->id;
		}
	}
}

if ( ! trait_exists( 'SMPL_Extraction_Trait' ) ) {
	trait SMPL_Extraction_Trait {
		protected string $trait_value;

		public function smpl_get_trait_value(): string {
			return $this->trait_value;
		}
	}
}

if ( ! class_exists( 'SMPL_Extraction_Base ' ) ) {
	abstract class SMPL_Extraction_Base {
		abstract protected function get_extractions(): array;
	}
}

if ( ! class_exists( 'SMPL_Extraction_Input_1' ) ) {
	/**
	 * SMPL_Extraction_Input_1
	 *
	 * Lower slug: smpl
	 * Upper slug: SMPL
	 */
	class SMPL_Extraction_Input_1 extends SMPL_Extraction_Base implements SMPL_Major_Interface, SMPL_Side_Interface {
		use SMPL_Extraction_Trait;

		public const EXT = 'smpl_ext';

		private array $smpl_doubled;

		private array $smpl_tripled;

		private array $smpl_quadrupled;

		private SMPL_Major_Interface $major;

		private SMPL_Side_Interface $side;

		public function __construct( SMPL_Major_Interface $major, SMPL_Side_Interface $side ) {
			$this->major = $major;
			$this->side  = $side;

			$this->smpl_doubled = array_map(
				[ $this, 'smpl_make_double' ],
				[ __CLASS__, 'smpl_get_basic_array' ]
			);

			$this->smpl_tripled = array_map(
				function ( $value ) { return smpl_get_three() * $value; },
				[ __CLASS__, 'smpl_get_basic_array' ]
			);

			$this->smpl_quadrupled = array_map(
				fn( $value ) => smpl_get_four() * $value,
				[ $this, 'smpl_get_basic_array' ]
			);

			$this->trait_value = static::EXT;
		}

		public function do_minor_thing() {
			$this->major->do_minor_thing();
		}

		public function do_major_thing() {
			$this->major->do_major_thing();
		}

		public function do_side_thing() {
			$this->side->do_side_thing();
		}

		public function get_extractions(): array {
			return [
				SMPL_Extraction_Input_1::class,
				SMPL_Minor_Interface::class,
				SMPL_Side_Interface::class,
			];
		}

		public function get_smpl_items(): string {
			return implode( ', ', [ ...$this->smpl_doubled, ...$this->smpl_tripled, ...$this->smpl_quadrupled ] );
		}

		public function smpl_make_double( int $value ): int {
			return smpl_get_two() * $value;
		}

		public function create_another(): SMPL_Extraction_Input_1 {
			return new SMPL_Extraction_Input_1(
				new SMPL_Major_Impl( $this->get_major()->get_id() + 1 ),
				new SMPL_Side_Impl( $this->get_side()->get_id() + 1 )
			);
		}

		public function get_major(): SMPL_Major_Interface {
			return $this->major;
		}

		public function set_major( SMPL_Major_Interface $major ) {
			$this->major = $major;
		}

		public function get_side(): SMPL_Side_Interface {
			return $this->side;
		}

		public function set_side( SMPL_Side_Interface $side ) {
			$this->side = $side;
		}

		public function get_id(): int {
			return $this->major->get_id();
		}

		public static function smpl_get_basic_array(): array {
			return [ 1, 2, 3 ];
		}
	}
}

if ( ! function_exists( 'smpl_get_two' ) ) {
	function smpl_get_two(): int {
		return 2;
	}
}

if ( ! function_exists( 'smpl_get_three' ) ) {
	function smpl_get_three(): int {
		return 3;
	}
}

if ( ! function_exists( 'smpl_get_four' ) ) {
	function smpl_get_four(): int {
		return 4;
	}
}

if ( ! function_exists( 'smpl_double_major' ) ) {
	function smpl_double_major( SMPL_Major_Interface $major ): SMPL_Major_Impl {
		return new SMPL_Major_Impl( $major->get_id() * smpl_get_two() );
	}
}

if ( ! function_exists( 'smpl_create_side' ) ) {
	function smpl_create_side( int $id ): SMPL_Side_Impl {
		return new SMPL_Side_Impl( $id );
	}
}

if ( ! function_exists( 'nbpcfoo' ) ) {
	// Intentionally testing wrong slug.
	function nbpcfoo(): void {
	}
}

// Commnet and smpl.
esc_html_e( SMPL_Extraction_Input_1::EXT, 'smpl' );

// Do silly things, just for testing.
$smpl_major = new SMPL_Major_Impl( 1 );
$smpl_side  = new SMPL_Side_Impl( 1 );

// Comment and SMPL.
$ex1 = new SMPL_Extraction_Input_1( $smpl_major, $smpl_side );
echo $ex1->get_smpl_items();

# Command and SMPL.
$ex2 = $ex1->create_another();

$ex1->set_major( smpl_double_major( $ex2->get_major() ) );
$ex1->set_side( smpl_create_side( 10 ) );

printf(
	'<a href="%s" title="%s">%s</a">',
	esc_url( get_home_url() ),
	esc_attr( __( 'Link for test', 'smpl' ) ),
	esc_html( _n( 'A Link', 'Links', 2, 'smpl' ) . ' ' )
);
?>

    <p><?php echo esc_html( $ex1->smpl_get_trait_value() ); ?><p>

<?php

$arr_smpl = SMPL_Extraction_Input_1::smpl_get_basic_array();
echo esc_html( $arr_smpl[1] );
