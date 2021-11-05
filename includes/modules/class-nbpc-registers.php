<?php
/**
 * NBPC: Registers module
 *
 * Manage all registers
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Registers' ) ) {
	/**
	 * You can remove unused registers.
	 *
	 * @property-read NBPC_Register_Activation    $activation
	 * @property-read NBPC_Register_Ajax          $ajax
	 * @property-read NBPC_Register_Comment_Meta  $comment_meta
	 * @property-read NBPC_Register_Cron          $cron
	 * @property-read NBPC_Register_Cron_Schedule $cron_schedule
	 * @property-read NBPC_Register_Deactivation  $deactivation
	 * @property-read NBPC_Register_Option        $option
	 * @property-read NBPC_Register_Post_Meta     $post_meta
	 * @property-read NBPC_Register_Post_Type     $post_type
	 * @property-read NBPC_Register_Script        $script
	 * @property-read NBPC_Register_Style         $style
	 * @property-read NBPC_Register_Submit        $submit
	 * @property-read NBPC_Register_Taxonomy      $taxonomy
	 * @property-read NBPC_Register_Term_Meta     $term_meta
	 * @property-read NBPC_Register_User_Meta     $user_meta
	 */
	class NBPC_Registers implements NBPC_Module {
		use NBPC_Submodule_Impl;

		public function __construct() {
			/**
			 * You can remove unused registers.
			 */
			$this->assign_modules(
				[
					'activation'    => NBPC_Register_Activation::class,
					'ajax'          => NBPC_Register_Ajax::class,
					'comment_meta'  => NBPC_Register_Comment_Meta::class,
					'cron'          => NBPC_Register_Cron::class,
					'cron_schedule' => NBPC_Register_Cron_Schedule::class,
					'deactivation'  => NBPC_Register_Deactivation::class,
					'option'        => NBPC_Register_Option::class,
					'post_meta'     => NBPC_Register_Post_Meta::class,
					'post_type'     => NBPC_Register_Post_Type::class,
					'script'        => NBPC_Register_Script::class,
					'style'         => NBPC_Register_Style::class,
					'submit'        => NBPC_Register_Submit::class,
					'taxonomy'      => NBPC_Register_Taxonomy::class,
					'term_meta'     => NBPC_Register_Term_Meta::class,
					// NOTE: 'uninstall' is not a part of registers submodules.
					//       Because it 'uninstall' hook requires static method callback.
					'user_meta'     => NBPC_Register_User_Meta::class,
				]
			);
		}

		public static function regs_activation( NBPC_Register_Activation $register ): Generator {
			// Define your activation regs for callback.
			yield null;
		}

		public static function regs_ajax( NBPC_Register_Ajax $register ): Generator {
			// Define your ajax regs for callback.
			yield null;
		}

		public static function regs_comment_meta( NBPC_Register_Comment_Meta $register ): Generator {
			// Define your comment meta regs for callback.
			yield null;
		}

		public static function regs_cron( NBPC_Register_Cron $register ): Generator {
			// Define your cron regs for callback.
			yield null;
		}

		public static function regs_cron_schedule( NBPC_Register_Cron_Schedule $register ): Generator {
			// Define your cron schedule regs for callback.
			yield null;
		}

		public static function regs_deactivation( NBPC_Register_Deactivation $register ): Generator {
			// Define your deactivation regs for callback.
			yield null;
		}

		public static function regs_option( NBPC_Register_Option $register ): Generator {
			// Define your option regs for callback.
			yield null;
		}

		public static function regs_post_meta( NBPC_Register_Post_Meta $register ): Generator {
			// Define your post meta regs for callback.
			yield null;
		}

		public static function regs_post_type( NBPC_Register_Post_Type $register ): Generator {
			// Define your post type regs for callback.
			yield null;
		}

		public static function regs_script( NBPC_Register_Script $register ): Generator {
			// Define your script regs for callback.
			yield null;
		}

		public static function regs_style( NBPC_Register_Style $register ): Generator {
			// Define your style regs for callback.
			yield null;
		}

		public static function regs_submit( NBPC_Register_Submit $register ): Generator {
			// Define your submit regs for callback.
			yield null;
		}

		public static function regs_taxonomy( NBPC_Register_Taxonomy $register ): Generator {
			// Define your taxonomy regs for callback.
			yield null;
		}

		public static function regs_term_meta( NBPC_Register_Term_Meta $register ): Generator {
			// Define your term meta regs for callback.
			yield null;
		}

		public static function regs_user_meta( NBPC_Register_User_Meta $register ): Generator {
			// Define your user meta regs for callback.
			yield null;
		}

		public static function regs_uninstall( NBPC_Register_Uninstall $register ): Generator {
			// Define your user meta regs for callback.
			yield null;
		}
	}
}
