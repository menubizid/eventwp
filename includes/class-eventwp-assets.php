<?php
/**
 * EventWP Assets — register/enqueue frontend scripts & styles.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assets manager.
 */
class EventWP_Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_all' ) );
	}

	/**
	 * Register all frontend assets.
	 *
	 * @return void
	 */
	public function register_all() {
		wp_register_style(
			'eventwp-frontend',
			EVENTWP_URL . '/assets/css/frontend.css',
			array(),
			EVENTWP_VERSION
		);
		wp_register_style(
			'eventwp-app',
			EVENTWP_URL . '/assets/css/app.css',
			array(),
			EVENTWP_VERSION
		);
		wp_register_style(
			'eventwp-landing',
			EVENTWP_URL . '/assets/css/landing.css',
			array(),
			EVENTWP_VERSION
		);
		wp_register_script(
			'eventwp-landing',
			EVENTWP_URL . '/assets/js/landing.js',
			array(),
			EVENTWP_VERSION,
			true
		);
		wp_localize_script(
			'eventwp-landing',
			'EventWPLanding',
			array(
				'galeri'     => __( 'Galeri', 'eventwp' ),
				'pesan'      => __( 'Pesan', 'eventwp' ),
				'tutup'      => __( 'Tutup', 'eventwp' ),
				'cta'        => __( 'Tampilkan Aplikasi', 'eventwp' ),
				'apps'       => get_permalink( (int) get_option( 'eventwp_app_page' ) ),
			)
		);
		wp_register_script(
			'eventwp-app',
			EVENTWP_URL . '/assets/js/app.js',
			array(),
			EVENTWP_VERSION,
			true
		);
		wp_register_script(
			'eventwp-vendor',
			EVENTWP_URL . '/assets/js/vendor.js',
			array(),
			EVENTWP_VERSION,
			true
		);
		wp_localize_script(
			'eventwp-app',
			'EVENTWP',
			array(
			'root'       => esc_url_raw( rest_url( EVENTWP_REST_NAMESPACE . '/' ) ),
			'nonce'      => wp_create_nonce( 'wp_rest' ),
			'siteUrl'    => esc_url_raw( home_url() ),
			'wpAdmin'    => esc_url_raw( admin_url( 'admin.php?page=eventwp' ) ),
			'imageFallback' => EVENTWP_URL . '/assets/img/og-default.svg',
				'i18n'       => array(
					'loading'  => __( 'Memuat…', 'eventwp' ),
					'login'    => __( 'Masuk', 'eventwp' ),
					'register' => __( 'Daftar', 'eventwp' ),
				),
			)
		);

		// Gutenberg block scripts (for the editor).
		$js_path  = EVENTWP_DIR . '/build/blocks.js';
		$css_path = EVENTWP_DIR . '/build/blocks.css';
		if ( file_exists( $js_path ) ) {
			wp_register_script(
				'eventwp-blocks-js',
				EVENTWP_URL . '/build/blocks.js',
				array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
				EVENTWP_VERSION,
				true
			);
		}
		if ( file_exists( $css_path ) ) {
			wp_register_style( 'eventwp-blocks-css', EVENTWP_URL . '/build/blocks.css', array(), EVENTWP_VERSION );
		}
	}
}
