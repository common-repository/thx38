<?php
/*
Plugin Name: THX_38
Plugin URI:
Description: THX stands for THeme eXperience. Our work for themes.php was included in version 3.8 of WordPress. Moving focus to the installation part of the theme experience. <strong>This is only for development work and the brave of heart</strong>.
Version: 1.0
Author: THX_38 Team
*/

class THX_38 {

	function __construct() {

		// Browse themes
		add_action( 'load-theme-install.php',  array( $this, 'install_themes_screen' ) );
		add_action( 'admin_print_scripts-theme-install.php', array( $this, 'enqueue' ) );

	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue() {

		// Relies on Backbone.js
		wp_enqueue_script( 'thx-38', plugins_url( 'thx-38.js', __FILE__ ), array( 'wp-backbone' ), '20130817', true );
		wp_enqueue_style( 'thx-38', plugins_url( 'thx-38.css', __FILE__ ), array(), '20130817', 'screen' );

		$themes = null;//wp_prepare_themes_for_js( array( wp_get_theme() ) );
		// Passes the theme data and settings
		// These are the bones of the application
		wp_localize_script( 'thx-38', '_wpThemeSettings', array(
			'themes'   => $themes,
			'settings' => array(
				'isBrowsing'    => (bool) ( get_current_screen()->id == 'theme-install' ),
				'canInstall'    => ( ! is_multisite() && current_user_can( 'install_themes' ) ),
				'installURI'    => ( ! is_multisite() && current_user_can( 'install_themes' ) ) ? admin_url( 'theme-install.php' ) : null,
				'root'          => parse_url( admin_url( 'themes.php' ), PHP_URL_PATH ),
			),
		 	'l10n' => array(
		 		'addNew' => __( 'Add New Theme' ),
		 		'search'  => __( 'Search Installed Themes' ),
		 		'searchPlaceholder' => __( 'Search installed themes...' ),
		  	),
		  	'browse' => array(
				'sections' => apply_filters( 'thx_theme_sections', array(
					'featured' => __( 'Featured Themes' ),
					'popular'  => __( 'Popular Themes' ),
					'new'      => __( 'Newest Themes' ),
				) ),
				'publicThemes' => ( get_current_screen()->id == 'theme-install' ) ? $this->get_default_public_themes() : null,
			),
		) );
	}

	/**
	 * The main template file for the theme-install.php screen
	 *
	 * Replaces entire contents of theme-install.php
	 * @require admin-header.php and admin-footer.php
	 */
	function install_themes_screen() {

		// Admin header
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
		?>
		<div id="appearance" class="wrap">
			<h2>
				<?php esc_html_e( 'Themes' ); ?>
				<a href="<?php echo admin_url( 'themes.php' ); ?>" class="add-new-h2"><?php echo esc_html( _x( 'Your installed themes', 'Go back to the themes page' ) ); ?></a>
			</h2>
			<div class="theme-categories"><span><?php esc_html_e( 'Categories:' ); ?></span> <a href="" class="current">All</a> <a href="">Photography</a> <a href="">Magazine</a> <a href="">Blogging</a>
				<div class="theme-overlay"></div>
		</div>
		<?php

		// Get the templates
		self::templates();

		// Admin footer
		require( ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}

	/**
	 * Array containing the supported directory sections
	 *
	 * @return array
	 */
	protected function themes_directory_sections() {
		$sections = array(
			'featured' => __( 'Featured Themes' ),
			'popular'  => __( 'Popular Themes' ),
			'new'      => __( 'Newest Themes' ),
		);
		return $sections;
	}

	/**
	 * Gets public themes from the themes directory
	 * Used to populate the initial views
	 *
	 * @uses themes_api themes_directory_sections
	 * @return array with $theme objects
	 */
	protected function get_default_public_themes( $themes = array() ) {
		$sections = self::themes_directory_sections();
		$sections = array_keys( $sections );

		$args = array(
			'page' => 1,
			'per_page' => 4,
		);

		foreach ( $sections as $section ) {
			$args['browse'] = $section;
			$themes[ $section ] = themes_api( 'query_themes', $args );
		}

		return $themes;
	}

	/**
	 * Ajax request handler for public themes
	 *
	 * @uses get_public_themes
	 */
	public function ajax_public_themes() {
		$colors = self::get_public_themes( $_REQUEST );
		header( 'Content-Type: text/javascript' );
		echo json_encode( $response );
		die;
	}

	/**
	 * Gets public themes from the themes directory
	 *
	 * @uses get_public_themes
	 */
	public function get_public_themes( $args = array() ) {
		$defaults = array(
			'page' => 1,
			'per_page' => 4,
			'browse' => 'new',
		);

		$args = wp_parse_args( $args, $defaults );
		$themes = themes_api( 'query_themes', $args );
		return $themes;
	}


	/**
	 * These are the templates that will be used to render the final HTML
	 *
	 * ------------------------
	 * Underscores.js Templates
	 * ------------------------
	 */

	/**
	 * Underscores template for rendering the Theme views
	 */
	public function templates() {
		?>
		<script id="tmpl-theme" type="text/template">
			<# if ( data.screenshot_url ) { #>
				<div class="theme-screenshot">
					<img src="{{ data.screenshot_url }}" alt="" />
				</div>
			<# } else { #>
				<div class="theme-screenshot blank"></div>
			<# } #>
			<span class="more-details"><?php _e( 'Theme Details' ); ?></span>
			<div class="theme-author"><?php printf( __( 'By %s' ), '{{{ data.author }}}' ); ?></div>
			<h3 class="theme-name">{{{ data.name }}}</h3>

			<div class="theme-actions">
				<a class="button button-secondary preview"><?php esc_html_e( 'Install' ); ?></a>
			</div>
		</script>

		<script id="tmpl-theme-single" type="text/template">
			<div class="theme-backdrop"></div>
			<div class="theme-wrap">
				<div class="theme-header">
					<button alt="<?php _e( 'Show previous theme' ); ?>" class="left dashicons dashicons-no"></button>
					<button alt="<?php _e( 'Show next theme' ); ?>" class="right dashicons dashicons-no"></button>
					<button alt="<?php _e( 'Close overlay' ); ?>" class="close dashicons dashicons-no"></button>
				</div>
				<div class="theme-about">
					<div class="theme-screenshots">
					<# if ( data.screenshot_url ) { #>
						<div class="screenshot"><img src="{{ data.screenshot_url }}" alt="" /></div>
					<# } else { #>
						<div class="screenshot blank"></div>
					<# } #>
					</div>

					<div class="theme-info">
						<h3 class="theme-name">{{{ data.name }}}<span class="theme-version"><?php printf( __( 'Version: %s' ), '{{{ data.version }}}' ); ?></span></h3>
						<h4 class="theme-author"><?php printf( __( 'By %s' ), '{{{ data.author }}}' ); ?></h4>
						<p class="theme-description">{{{ data.description }}}</p>
					</div>
				</div>

				<div class="theme-actions">
					<a href="" class="button button-secondary load-customize hide-if-no-customize"><?php _e( 'Install' ); ?></a>
				</div>
			</div>
		</script>
		<?php
	}

}

/**
 * Initialize
 */
new THX_38;