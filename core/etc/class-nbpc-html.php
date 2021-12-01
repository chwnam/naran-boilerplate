<?php
/**
 * NBPC: HTML output helper
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_HTML' ) ) {
	class NBPC_HTML {
		/**
		 * Quote string
		 *
		 * @param string $input
		 * @param string $quote
		 *
		 * @return string
		 */
		public static function enclose( string $input, string $quote = '"' ): string {
			return $quote . htmlentities2( $input ) . $quote;
		}

		/**
		 * Format attribute
		 *
		 * @param array $attrs
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

				/** @link https://html.spec.whatwg.org/multipage/indices.html#attributes-3 */
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
							$val = esc_attr( $val );
							// use strlen() because $val can be '0'.
							$buffer[] = strlen( $val ) ? ( $key . '=' . self::enclose( $val ) ) : $key;
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
		 * @param string $tag
		 * @param array  $attrs
		 * @param bool   $echo
		 *
		 * @return string
		 */
		public static function tag_open( string $tag, array $attrs, bool $echo = true ): string {
			$output = '';
			$tag    = sanitize_key( $tag );
			$attrs  = static::attrs( $attrs );

			if ( $tag ) {
				$output = '<' . $tag;
				if ( $attrs ) {
					$output .= ' ' . $attrs . '>';
				} else {
					$output .= '>';
				}
			}

			if ( $echo ) {
				echo $output;
				return '';
			}

			return $output;
		}

		/**
		 * Close a tag.
		 *
		 * @param string $tag
		 * @param bool   $echo
		 *
		 * @return string
		 */
		public static function tag_close( string $tag, bool $echo = true ): string {
			$output = '';
			$tag    = sanitize_key( $tag );

			if ( $tag ) {
				$output = '</' . $tag . '>';
			}

			if ( $echo ) {
				echo $output;
				return '';
			}

			return $output;
		}

		/**
		 * Input tag.
		 *
		 * @param array $attrs
		 * @param bool  $echo
		 *
		 * @return string
		 */
		public static function input( array $attrs, bool $echo = true ): string {
			return self::tag_open( 'input', $attrs, $echo );
		}

		/**
		 * Option tag
		 *
		 * @param string      $value    'value' attribute.
		 * @param string      $label    Label
		 * @param bool|string $selected Selected value, or more directly, boolean value.
		 * @param array       $attrs    Attributes other than 'value', and selected.
		 * @param bool        $echo     Echo, or return.
		 *
		 * @return string
		 */
		public static function option(
			string $value,
			string $label,
			$selected,
			array $attrs = [],
			bool $echo = true
		): string {
			$attrs['value']    = $value;
			$attrs['selected'] = is_bool( $selected ) ? $selected : $selected == $value;

			$output = self::tag_open( 'option', $attrs, false ) .
			          esc_html( $label ) .
			          self::tag_close( 'option', false );

			if ( $echo ) {
				echo $output;
				return '';
			}

			return $output;
		}

		/**
		 * select tag.
		 *
		 * @param array        $options    Associative array, where keys are for 'value' attributes of optiton tags,
		 *                                 values are for text node inside of option tags.
		 *                                 Value may also be an associative array that is for optgroup tags.
		 * @param string|array $selected   Selected value string. If select tag has multiple attribute, it can be an array of selected values.
		 * @param array        $attrs      <select> tag attributes.
		 * @param array        $opt_attrs  <option> tag attributes.
		 *                                 Key is option value. Value is array or attributes.
		 * @param bool         $echo       Echo or return.
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
			array $opt_attrs = [],
			bool $echo = true
		): string {
			$buffer = [
				self::tag_open( 'select', $attrs, false ),
			];

			foreach ( $options as $key => $option ) {
				if ( is_array( $option ) ) {
					/**
					 * Optgroup part
					 *
					 * e.g.
					 * 'Swedish Cars' => [ 'volvo' => 'Volvo', 'saab' => 'Saab' ],
					 */
					$a = [];
					if ( isset( $opt_attrs[ $key ] ) ) {
						foreach ( (array) $opt_attrs[ $key ] as $k => $v ) {
							$a[ $k ] = $v;
						}
					}
					$a['label'] = $key;

					$buffer[] = self::tag_open( 'optgroup', $a, false );

					foreach ( $option as $v => $l ) {
						$s = is_array( $selected ) ? in_array( $v, $selected ) : $selected;
						$a = (array) ( $opt_attrs[ $v ] ?? [] );

						$buffer[] = self::option( $v, $l, $s, $a, false );
					}

					$buffer[] = self::tag_close( 'optgroup', false );
				} else {
					/**
					 * Option part
					 */
					$s = is_array( $selected ) ? in_array( $key, $selected ) : $selected;
					$a = (array) ( $opt_attrs[ $key ] ?? [] );

					$buffer[] = self::option( $key, $option, $s, $a, false );
				}
			}

			$buffer[] = self::tag_close( 'select', false );

			if ( $echo ) {
				echo implode( '', $buffer );
				return '';
			}

			return implode( '', $buffer );
		}

		/**
		 * Filter attribute value
		 *
		 * @param array|string $val
		 * @param callable     $filter
		 *
		 * @return array
		 */
		public static function filter_val( $val, callable $filter ): array {
			if ( ! is_array( $val ) ) {
				$val = array_map( 'trim', explode( ' ', strval( $val ) ) );
			}

			return array_unique( array_filter( array_map( $filter, $val ) ) );
		}
	}
}
