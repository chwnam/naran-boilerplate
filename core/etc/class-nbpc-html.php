<?php
/**
 * NBPC: HTML output helper
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_HTML' ) ) {
	/**
	 * HTML generate class
	 *
	 * @since 1.4.0-beta.3 $output is removed.
	 *                     You are required to sanitize your HTML text results by yourself right before they are echoed.
	 * @see   wp_kses
	 * @see   wp_kses_post
	 */
	class NBPC_HTML {
		protected static ?array $allowed_select = null;

		protected static ?array $allowed_input = null;

		/**
		 * Quote string
		 *
		 * @param string $input Input string.
		 * @param string $quote Quote character. Defaults to a double quote.
		 *
		 * @return string
		 */
		public static function enclose( string $input, string $quote = '"' ): string {
			return $quote . htmlentities2( $input ) . $quote;
		}

		/**
		 * Format attribute
		 *
		 * @param array $attrs Input attributes. Key for attribute name, value for attribute value.
		 *
		 * @return string
		 */
		public static function attrs( array $attrs ): string {
			$buffer = [];

			foreach ( $attrs as $key => $val ) {
				$key = sanitize_key( $key );
				if ( ! $key ) {
					continue;
				}

				/* @link https://html.spec.whatwg.org/multipage/indices.html#attributes-3 */
				switch ( $key ) {
					case 'accept':
						$val      = implode( ', ', static::filter_val( $val, 'sanitize_mime_type' ) );
						$buffer[] = $key . '=' . static::enclose( $val );
						break;

					case 'class':
						$val      = implode( ' ', static::filter_val( $val, 'sanitize_html_class' ) );
						$buffer[] = $key . '=' . static::enclose( $val );
						break;

					case 'action':
					case 'cite':
					case 'data':
					case 'formaction':
					case 'href':
					case 'itemid':
					case 'itemprop':
					case 'itemtype':
					case 'manifest':
					case 'ping':
					case 'poster':
					case 'src':
						$val      = esc_url( $val );
						$buffer[] = $key . '=' . static::enclose( $val );
						break;

					case 'allowfullscreen':
					case 'allowpaymentrequest':
					case 'async':
					case 'autofocus':
					case 'autoplay':
					case 'checked':
					case 'controls':
					case 'default':
					case 'defer':
					case 'disabled':
					case 'formnovalidate':
					case 'hidden':
					case 'ismap':
					case 'itemscope':
					case 'loop':
					case 'multiple':
					case 'muted':
					case 'nomodule':
					case 'novalidate':
					case 'open':
					case 'playsinline':
					case 'readonly':
					case 'required':
					case 'reversed':
					case 'selected':
						if ( is_bool( $val ) ) {
							$buffer[] = $val ? ( $key . '=' . self::enclose( $key ) ) : '';
						} else {
							$val      = esc_attr( $val );
							$buffer[] = '' !== $val ? ( $key . '=' . self::enclose( $val ) ) : $key;
						}
						break;

					default:
						$val      = esc_attr( $val );
						$buffer[] = $key . '=' . static::enclose( $val );
						break;
				}
			}

			return implode( ' ', array_filter( $buffer ) );
		}

		/**
		 * Open a tag.
		 *
		 * @param string $tag     Tag Name.
		 * @param array  $attrs   Array of attributes.
		 * @param bool   $enclose Put a closing slash at the tail of tag.
		 *
		 * @return string
		 */
		public static function tag_open( string $tag, array $attrs = [], bool $enclose = false ): string {
			$output    = '';
			$tag       = sanitize_key( $tag );
			$formatted = static::attrs( $attrs );

			if ( $tag ) {
				$output = '<' . $tag . ( $formatted ? " $formatted" : '' ) . ( $enclose ? '/' : '' ) . '>';
			}

			return $output;
		}

		/**
		 * Close a tag.
		 *
		 * @param string $tag  Closing tag.
		 *
		 * @return string
		 */
		public static function tag_close( string $tag ): string {
			$output = '';
			$tag    = sanitize_key( $tag );

			if ( $tag ) {
				$output = '</' . $tag . '>';
			}

			return $output;
		}

		/**
		 * Input tag.
		 *
		 * @param array $attrs Input tag attributes.
		 *
		 * @return string
		 */
		public static function input( array $attrs = [] ): string {
			return self::tag_open( 'input', $attrs, true );
		}

		/**
		 * Option tag
		 *
		 * @param string      $value    'value' attribute.
		 * @param string      $label    Label string.
		 * @param bool|string $selected Selected value, or more directly, boolean value.
		 * @param array       $attrs    Attributes other than 'value', and selected.
		 *
		 * @return string
		 */
		public static function option(
			string $value,
			string $label,
			$selected,
			array $attrs = []
		): string {
			$attrs['value'] = $value;

			// phpcs:disable WordPress.PHP.StrictComparisons.LooseComparison

			/**
			 * The function is for HTML attributes output. Please be more generous about types.
			 * String '1' and integer 1 may be handled the same in here.
			 *
			 * @noinspection TypeUnsafeComparisonInspection
			 */
			$attrs['selected'] = is_bool( $selected ) ? $selected : $selected == $value;

			// phpcs:enable WordPress.PHP.StrictComparisons.LooseComparison

			return self::tag_open( 'option', $attrs ) . esc_html( $label ) . self::tag_close( 'option' );
		}

		/**
		 * Select tag.
		 *
		 * @param array        $options    Associative array, where keys are for 'value' attributes of option tags,
		 *                                 values are for text node inside of option tags.
		 *                                 Value may also be an associative array that is for optgroup tags.
		 * @param string|array $selected   Selected value string. If select tag has multiple attribute, it can be an array of selected values.
		 * @param array        $attrs      <select> tag attributes.
		 * @param array        $opt_attrs  <option> tag attributes.
		 *                                 Key is option value. Value is array or attributes.
		 *
		 * @return string
		 *
		 * @example
		 * <option> tag only:
		 * $options = [
		 *   'volvo'    => 'Volvo',        // <option value="volvo">Volvo</option>
		 *   'saab'     => 'Saab',         // <option value="saab">Saab</option>
		 *   'mercedes' => 'Mercedes',     // <option value="mercedes">Mercedes</option>
		 *   'audi'     => 'Audi',         // <option value="audi">Audi</option>
		 * ]
		 *
		 * <optgroup> tag combination:
		 * $options = [
		 *   'Swedish Cars' => [             // <optgroup label="Swedish Cars">
		 *     'volvo'    => 'Volvo',        //   <option value="volvo">Volvo</option>
		 *     'saab'     => 'Saab',         //   <option value="saab">Saab</option>
		 *   ],                              // </optgroup>
		 *   'German Cars' => [              // <optgroup label="German Cars">
		 *     'mercedes' => 'Mercedes',     //   <option value="mercedes">Mercedes</option>
		 *     'audi'     => 'Audi',         //   <option value="audi">Audi</option>
		 *   ],                              // </optgroup>
		 * ]
		 *
		 * <option value="mercedes" class="mercedes-option" data-type="car-brand">Mercedes</option>
		 * $options = [
		 *   'mercedes' => 'Mercedes',
		 * ]
		 * $opt_attrs = [
		 *   'mercedes' => [
		 *     'class'      => 'mercedes-option',
		 *     'data-type'  => 'car-brand',
		 *   ]
		 * ]
		 */
		public static function select(
			array $options,
			$selected = '',
			array $attrs = [],
			array $opt_attrs = []
		): string {
			$buffer = [
				self::tag_open( 'select', $attrs ),
			];

			foreach ( $options as $key => $option ) {
				if ( is_array( $option ) ) {
					/**
					 * Optgroup part
					 *
					 * Sample:
					 * 'Swedish Cars' => [ 'volvo' => 'Volvo', 'saab' => 'Saab' ],
					 */
					$a = [];
					if ( isset( $opt_attrs[ $key ] ) ) {
						foreach ( (array) $opt_attrs[ $key ] as $k => $v ) {
							$a[ $k ] = $v;
						}
					}
					$a['label'] = $key;

					$buffer[] = self::tag_open( 'optgroup', $a );

					foreach ( $option as $v => $l ) {
						$s = is_array( $selected ) ? in_array( $v, $selected, true ) : $selected;
						$a = (array) ( $opt_attrs[ $v ] ?? [] );

						$buffer[] = self::option( $v, $l, $s, $a );
					}

					$buffer[] = self::tag_close( 'optgroup' );
				} else {
					/**
					 * Option part
					 */
					$s = is_array( $selected ) ? in_array( $key, $selected, true ) : $selected;
					$a = (array) ( $opt_attrs[ $key ] ?? [] );

					$buffer[] = self::option( $key, $option, $s, $a );
				}
			}

			$buffer[] = self::tag_close( 'select' );

			return implode( '', $buffer );
		}

		/**
		 * Filter attribute value
		 *
		 * @param array|string $val    Input.
		 * @param callable     $filter Filter function.
		 *
		 * @return array
		 */
		public static function filter_val( $val, callable $filter ): array {
			if ( ! is_array( $val ) ) {
				$val = array_map( 'trim', explode( ' ', (string) $val ) );
			}

			return array_unique( array_filter( array_map( $filter, $val ) ) );
		}

		/**
		 * Build a nested tag structure.
		 *
		 * @param string       $tag         Opening, and closing tag.
		 * @param array        $attrs       Tag's attributes.
		 * @param string|array ...$enclosed Enclosed nodes. Any number of strings or arrays.
		 *
		 * @return string
		 */
		public static function nested( string $tag, array $attrs = [], ...$enclosed ): string {
			$buffer = [
				static::tag_open( $tag, $attrs ),
			];

			if ( ! $buffer[0] ) {
				return '';
			}

			foreach ( $enclosed as $en ) {
				if ( is_string( $en ) ) {
					$buffer[] = trim( $en );
				} elseif ( is_array( $en ) ) {
					foreach ( $en as $e ) {
						if ( is_string( $e ) ) {
							$buffer[] = trim( $e );
						}
					}
				}
			}

			$buffer[] = static::tag_close( $tag );

			return implode( '', array_filter( $buffer ) );
		}

		/**
		 * Sanitize helper method for select().
		 *
		 * @param string                             $input       Select, optgroup, and option HTML text.
		 * @param array<string, array<string, bool>> $extra_rules Additional rules. Key: tag / value: list of attrs.
		 *
		 * @return string
		 */
		public static function kses_select( string $input, array $extra_rules = [] ): string {
			if ( ! static::$allowed_select ) {
				static::$allowed_select = [
					'select'   => array_merge(
						static::kses_global_attrs(),
						[
							'autocomplete' => true,
							'autofocus'    => true,
							'class'        => true,
							'disabled'     => true,
							'form'         => true,
							'id'           => true,
							'multiple'     => true,
							'name'         => true,
							'readonly'     => true,
							'required'     => true,
							'size'         => true,
							'style'        => true,
						]
					),
					'optgroup' => array_merge(
						static::kses_global_attrs(),
						[
							'class'    => true,
							'disabled' => true,
							'id'       => true,
							'label'    => true,
							'style'    => true,
						]
					),
					'option'   => array_merge(
						static::kses_global_attrs(),
						[
							'class'    => true,
							'disabled' => true,
							'id'       => true,
							'label'    => true,
							'style'    => true,
							'selected' => true,
							'value'    => true,
						]
					),
				];
			}

			return wp_kses( $input, static::merge_kses_attrs( static::$allowed_select, $extra_rules ) );
		}

		/**
		 * Sanitize helper method for input().
		 *
		 * @param string                             $input       Input HTML text.
		 * @param array<string, array<string, bool>> $extra_rules Additional rules. Key: tag / value: list of attrs.
		 *
		 * @return string
		 */
		public static function kses_input( string $input, array $extra_rules = [] ): string {
			if ( ! static::$allowed_input ) {
				static::$allowed_input = [
					'input' => array_merge(
						static::kses_global_attrs(),
						[
							'accept'         => true,
							'alt'            => true,
							'autocomplete'   => true,
							'autofocus'      => true,
							'capture'        => true,
							'checked'        => true,
							'dirname'        => true,
							'disabled'       => true,
							'form'           => true,
							'formaction'     => true,
							'formenctype'    => true,
							'formmethod'     => true,
							'formnovalidate' => true,
							'formtarget'     => true,
							'height'         => true,
							'list'           => true,
							'max'            => true,
							'maxlength'      => true,
							'min'            => true,
							'minlength'      => true,
							'multiple'       => true,
							'name'           => true,
							'pattern'        => true,
							'placeholder'    => true,
							'readonly'       => true,
							'required'       => true,
							'size'           => true,
							'src'            => true,
							'step'           => true,
							'type'           => true,
							'value'          => true,
							'width'          => true,
						]
					),
				];
			}

			return wp_kses( $input, static::merge_kses_attrs( static::$allowed_input, $extra_rules ) );
		}

		/**
		 * Sanitize helper method for nested().
		 *
		 * @param string                             $input Input HTML text.
		 * @param array<string, array<string, bool>> $rules Additional rules. Key: tag / value: list of attrs.
		 *
		 * @return string
		 */
		public static function kses_nested( string $input, array $rules = [] ): string {
			$globals = static::kses_global_attrs();

			foreach ( array_keys( $rules ) as $tag ) {
				$rules[ $tag ] = array_merge( $globals, $rules[ $tag ] );
			}

			return wp_kses( $input, $rules );
		}

		/**
		 * Merge two allowed attributes.
		 *
		 * @param array<string, array<string, bool>> $origin
		 * @param array<string, array<string, bool>> $extra
		 *
		 * @return array
		 */
		protected static function merge_kses_attrs( array $origin, array $extra ): array {
			if ( empty( $extra ) ) {
				return $origin;
			} else {
				$result = $origin;

				foreach ( $extra as $tag => $attrs ) {
					if ( isset( $result[ $tag ] ) ) {
						$result[ $tag ] = array_merge( $result[ $tag ], $attrs );
					} else {
						$result[ $tag ] = $attrs;
					}
				}

				return $result;
			}
		}

		/**
		 * Return global HTML attributes list.
		 *
		 * @return array<string, bool>
		 * @link   https://developer.mozilla.org/ko/docs/Web/HTML/Global_attributes
		 */
		protected static function kses_global_attrs(): array {
			return [
				'accesskey'       => true,
				'autocapitalize'  => true,
				'class'           => true,
				'contenteditable' => true,
				'dir'             => true,
				'draggable'       => true,
				'hidden'          => true,
				'id'              => true,
				'inputmode'       => true,
				'is'              => true,
				'itemid'          => true,
				'itemprop'        => true,
				'itemref'         => true,
				'itemscope'       => true,
				'itemtype'        => true,
				'lang'            => true,
				'part'            => true,
				'spellcheck'      => true,
				'style'           => true,
				'tabindex'        => true,
				'title'           => true,
			];
		}
	}
}
