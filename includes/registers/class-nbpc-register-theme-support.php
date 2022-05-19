<?php
/**
 * NBPC: Theme setup register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Theme_Support' ) ) {
	class NBPC_Register_Theme_Support extends NBPC_Register_Base_Theme_Support {
		public function __construct() {
			parent::__construct();

			if ( ! is_admin() ) {
				$this->add_action( 'pre_get_posts', 'map_front_modules' );
			}
		}

		/**
		 * Get theme support items.
		 * You do not have to declare all the items. Just select what you really need.
		 *
		 * @return Generator
		 * @link   https://developer.wordpress.org/themes/basics/theme-functions/
		 */
		public function get_items(): Generator {
			yield new NBPC_Reg_Theme_Support( 'automatic-feed-links' );

			yield new NBPC_Reg_Theme_Support( 'post-thumbnails' );

			yield new NBPC_Reg_Theme_Support(
				'post-formatst',
				[
					'aside',
					'gallery',
					'quote',
					'image',
					'video',
				]
			);

			yield new NBPC_Reg_Theme_Support(
				'html5',
				[
					'caption',
					'comment-form',
					'comment-list',
					'gallery',
					'script',
					'style',
				]
			);

			yield new NBPC_Reg_Theme_Support(
				'custom-header',
				[
					'default-image'      => get_template_directory_uri() . 'img/default-image.jpg',
					'default-text-color' => '000',
					'width'              => 1000,
					'height'             => 250,
					'flex-width'         => true,
					'flex-height'        => true,
				]
			);

			yield new NBPC_Reg_Theme_Support( 'title-tag' );
		}

		/**
		 * Map front module.
		 *
		 * @param WP_Query $query
		 *
		 * @return void
		 */
		public function map_front_modules( WP_Query $query ) {
			if ( ! $query->is_main_query() ) {
				return;
			}

			$this->remove_action( 'pre_get_posts', 'map_front_modules' );

			$hierarchy = NBPC_Theme_Hierarchy::get_instance();

			// Decide which front module will handle the front scene.
			/*
			if ( $hierarchy->is_archive() ) {
				$hierarchy->set_front_module( Archive_Front_Module::class );
			} elseif ( $hierarchy->is_singular() ) {
				$hierarchy->set_front_module( Singular_Front_Module::class );
			}
			*/

			// Call pre_get_posts for archive moudules.
			if ( $hierarchy->is_archive() ) {
				$module = $hierarchy->get_front_module();
				if ( $module instanceof NBPC_Front_Archive_Module ) {
					$module->pre_get_posts( $query );
				}
			}
		}

		/**
		 * Additional theme support items, which do not use add_theme_suppert() function.
		 * For example, navigation menus, sidebars, and so on.
		 *
		 * @return void
		 */
		protected function extra_register(): void {
			global $content_width;

			if ( ! $content_width ) {
				$content_width = 800; // Pixels
			}

			register_nav_menus(
				[
					'primary'   => __( 'Primary Menu', 'nbpc' ),
					'secondary' => __( 'Secondary Menu', 'nbpc' ),
				]
			);

			register_sidebar(
				[
					'name'          => __( 'Primary Sidebar', 'nbpc' ),
					'id'            => 'sidebar-1',
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h3 class="widget-title">',
					'after_title'   => '</h3>',
				]
			);

			register_sidebar(
				[
					'name'          => __( 'Secondary Sidebar', 'nbpc' ),
					'id'            => 'sidebar-2',
					'before_widget' => '<ul><li id="%1$s" class="widget %2$s">',
					'after_widget'  => '</li></ul>',
					'before_title'  => '<h3 class="widget-title">',
					'after_title'   => '</h3>',
				]
			);

			/** @see NBPC_Main::load_textdomain() for textdomain loading. */
		}
	}
}
