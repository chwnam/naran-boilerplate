<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-block.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Block' ) ) {
	/**
	 * @property string        $title            Human-readable block type label.
	 * @property string|null   $category         Block type category classification, used in
	 *                                           search interfaces to arrange block types by category.
	 * @property array|null    $parent           Setting parent lets a block require that it is only
	 *                                           available when nested within the specified blocks.
	 * @property string|null   $icon             Block type icon.
	 * @property string        $description      A detailed block type description.
	 * @property array         $keywords         Additional keywords to produce block type as
	 *                                           result in search interfaces.
	 * @property string|null   $textdomain       The translation textdomain.
	 * @property array         $styles           Alternative block styles.
	 * @property array|null    $supports         Supported features.
	 * @property array|null    $example          Structured data for the block preview.
	 * @property callable|null $render_callback  Block type render callback.
	 * @property array|null    $attributes       Block type attributes property schemas.
	 * @property array         $uses_context     Context values inherited by blocks of this type.
	 * @property array|null    $provides_context Context provided by blocks of this type.
	 * @property string|null   $editor_script    Block type editor script handle.
	 * @property string|null   $script           Block type front end script handle.
	 * @property string|null   $editor_style     Block type editor style handle.
	 * @property string|null   $style            Block type front end style handle.
	 */
	class NBPC_Reg_Block implements NBPC_Reg {
		/**
		 * Constructor method
		 *
		 * @param string $block_type
		 * @param array  $args
		 *
		 * @see WP_Block_Type::__construct() for details
		 */
		public function __construct(
			public string $block_type,
			public array $args = []
		) {
		}

		public function __get( string $key ) {
			return $this->args[ $key ] ?? null;
		}

		public function __set( string $key, $value ) {
			$this->args[ $key ] = $value;
		}

		public function __isset( string $key ): bool {
			return isset( $this->args[ $key ] );
		}

		public function register( $dispatch = null ): void {
			register_block_type( $this->block_type, $this->args );
		}
	}
}
