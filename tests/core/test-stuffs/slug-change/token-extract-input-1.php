<?php
/**
 * File for slug change testing.
 *
 * Upper slug: NBPC, for class, interface, trait names.
 * Lower slug: nbpc, for variable, method names.
 *
 * Sluges will be replaced only if they are used as whole words.
 * e.g. Replaced: NBPC, nbpc, NBPC-Foo, NBPC_Foo, Foo-NBPC, Foo_NBPC, nbpc-foo, nbpc_foo, get_nbpc, get_nbpc_some
 *      Ignored:  NBPCFoo, FooNBPC, nbpcfoo, foonbpc
 */

if ( ! interface_exists( 'NBPC_ID_Interface' ) ) {
	interface NBPC_ID_Interface {
		public function get_id(): int;
	}
}

if ( ! interface_exists( 'NBPC_Minor_Interface' ) ) {
	interface NBPC_Minor_Interface {
		public function do_minor_thing();
	}
}

if ( ! interface_exists( 'NBPC_Side_Interface' ) ) {
	interface NBPC_Side_Interface {
		public function do_side_thing();
	}
}

if ( ! interface_exists( 'NBPC_Major_Interface' ) ) {
	interface NBPC_Major_Interface extends NBPC_Minor_Interface, NBPC_ID_Interface {
		public function do_major_thing();
	}
}

if ( ! class_exists( 'NBPC_Major_Impl' ) ) {
	class NBPC_Major_Impl implements NBPC_Major_Interface {
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

if ( ! class_exists( 'NBPC_Side_Impl' ) ) {
	class NBPC_Side_Impl implements NBPC_Side_Interface, NBPC_ID_Interface {
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

if ( ! trait_exists( 'NBPC_Extraction_Trait' ) ) {
	trait NBPC_Extraction_Trait {
		protected string $trait_value;

		public function nbpc_get_trait_value(): string {
			return $this->trait_value;
		}
	}
}

if ( ! class_exists( 'NBPC_Extraction_Base ' ) ) {
	abstract class NBPC_Extraction_Base {
		abstract protected function get_extractions(): array;
	}
}

if ( ! class_exists( 'NBPC_Extraction_Input_1' ) ) {
	/**
	 * NBPC_Extraction_Input_1
	 *
	 * Lower slug: nbpc
	 * Upper slug: NBPC
	 */
	class NBPC_Extraction_Input_1 extends NBPC_Extraction_Base implements NBPC_Major_Interface, NBPC_Side_Interface {
		use NBPC_Extraction_Trait;

		public const EXT = 'nbpc_ext';

		private array $nbpc_doubled;

		private array $nbpc_tripled;

		private array $nbpc_quadrupled;

		private NBPC_Major_Interface $major;

		private NBPC_Side_Interface $side;

		public function __construct( NBPC_Major_Interface $major, NBPC_Side_Interface $side ) {
			$this->major = $major;
			$this->side  = $side;

			$this->nbpc_doubled = array_map(
				[ $this, 'nbpc_make_double' ],
				[ __CLASS__, 'nbpc_get_basic_array' ]
			);

			$this->nbpc_tripled = array_map(
				function ( $value ) { return nbpc_get_three() * $value; },
				[ __CLASS__, 'nbpc_get_basic_array' ]
			);

			$this->nbpc_quadrupled = array_map(
				fn( $value ) => nbpc_get_four() * $value,
				[ $this, 'nbpc_get_basic_array' ]
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
				NBPC_Extraction_Input_1::class,
				NBPC_Minor_Interface::class,
				NBPC_Side_Interface::class,
			];
		}

		public function get_nbpc_items(): string {
			return implode( ', ', [ ...$this->nbpc_doubled, ...$this->nbpc_tripled, ...$this->nbpc_quadrupled ] );
		}

		public function nbpc_make_double( int $value ): int {
			return nbpc_get_two() * $value;
		}

		public function create_another(): NBPC_Extraction_Input_1 {
			return new NBPC_Extraction_Input_1(
				new NBPC_Major_Impl( $this->get_major()->get_id() + 1 ),
				new NBPC_Side_Impl( $this->get_side()->get_id() + 1 )
			);
		}

		public function get_major(): NBPC_Major_Interface {
			return $this->major;
		}

		public function set_major( NBPC_Major_Interface $major ) {
			$this->major = $major;
		}

		public function get_side(): NBPC_Side_Interface {
			return $this->side;
		}

		public function set_side( NBPC_Side_Interface $side ) {
			$this->side = $side;
		}

		public function get_id(): int {
			return $this->major->get_id();
		}

		public static function nbpc_get_basic_array(): array {
			return [ 1, 2, 3 ];
		}
	}
}

if ( ! function_exists( 'nbpc_get_two' ) ) {
	function nbpc_get_two(): int {
		return 2;
	}
}

if ( ! function_exists( 'nbpc_get_three' ) ) {
	function nbpc_get_three(): int {
		return 3;
	}
}

if ( ! function_exists( 'nbpc_get_four' ) ) {
	function nbpc_get_four(): int {
		return 4;
	}
}

if ( ! function_exists( 'nbpc_double_major' ) ) {
	function nbpc_double_major( NBPC_Major_Interface $major ): NBPC_Major_Impl {
		return new NBPC_Major_Impl( $major->get_id() * nbpc_get_two() );
	}
}

if ( ! function_exists( 'nbpc_create_side' ) ) {
	function nbpc_create_side( int $id ): NBPC_Side_Impl {
		return new NBPC_Side_Impl( $id );
	}
}

if ( ! function_exists( 'nbpcfoo' ) ) {
	// Intentionally testing wrong slug.
	function nbpcfoo(): void {
	}
}

// Commnet and nbpc.
esc_html_e( NBPC_Extraction_Input_1::EXT, 'nbpc' );

// Do silly things, just for testing.
$nbpc_major = new NBPC_Major_Impl( 1 );
$nbpc_side  = new NBPC_Side_Impl( 1 );

// Comment and NBPC.
$ex1 = new NBPC_Extraction_Input_1( $nbpc_major, $nbpc_side );
echo $ex1->get_nbpc_items();

# Command and NBPC.
$ex2 = $ex1->create_another();

$ex1->set_major( nbpc_double_major( $ex2->get_major() ) );
$ex1->set_side( nbpc_create_side( 10 ) );

printf(
	'<a href="%s" title="%s">%s</a">',
	esc_url( get_home_url() ),
	esc_attr( __( 'Link for test', 'nbpc' ) ),
	esc_html( _n( 'A Link', 'Links', 2, 'nbpc' ) . ' ' )
);
?>

    <p><?php echo esc_html( $ex1->nbpc_get_trait_value() ); ?><p>

<?php

$arr_nbpc = NBPC_Extraction_Input_1::nbpc_get_basic_array();
echo esc_html( $arr_nbpc[1] );
