<?php
/**
 * EventWP main plugin class — wires the whole plugin together.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin orchestrator (singleton).
 */
final class EventWP {

	/**
	 * Singleton instance.
	 *
	 * @var EventWP|null
	 */
	public static $instance = null;

	/**
	 * Service registry.
	 *
	 * @var array<string,object>
	 */
	public $services = array();

	/**
	 * Components.
	 *
	 * @var object|null
	 */
	public $cpt            = null;
	public $tax            = null;
	public $meta           = null;
	public $installer      = null;
	public $blocks         = null;
	public $shortcodes     = null;
	public $admin          = null;
	public $assets         = null;
	public $rest           = null;
	public $store          = null;
	public $api            = null;
	public $helpers        = null;

	/**
	 * Get instance.
	 *
	 * @return EventWP
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		EventWP_Installer::maybe_install();
		$this->register_services();
		$this->hooks();
	}

	/**
	 * Load the plugin file dependencies.
	 *
	 * @return void
	 */
	private function includes() {
		$dir  = EVENTWP_DIR . '/includes';
		$core = array(
			'helpers',
			'store',
			'api',
			'cpt',
			'taxonomy',
			'meta',
			'blocks',
			'shortcodes',
			'admin',
			'assets',
			'rest',
			'installer',
		);
		foreach ( $core as $name ) {
			$file = $dir . '/class-eventwp-' . $name . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}

	/**
	 * Instantiate all services.
	 *
	 * @return void
	 */
	private function register_services() {
		$this->helpers    = new EventWP_Helpers();
		$this->store      = new EventWP_Store();
		$this->api        = new EventWP_API();
		$this->cpt        = new EventWP_CPT();
		$this->tax        = new EventWP_Taxonomy();
		$this->meta       = new EventWP_Meta();
		$this->blocks     = new EventWP_Blocks();
		$this->shortcodes = new EventWP_Shortcodes();
		$this->admin      = new EventWP_Admin();
		$this->assets     = new EventWP_Assets();
		$this->rest       = new EventWP_Rest();
		$this->installer  = EventWP_Installer::instance();

		$this->services = array(
			'helpers'    => $this->helpers,
			'store'      => $this->store,
			'api'        => $this->api,
			'cpt'        => $this->cpt,
			'tax'        => $this->tax,
			'meta'       => $this->meta,
			'blocks'     => $this->blocks,
			'shortcodes' => $this->shortcodes,
			'admin'      => $this->admin,
			'assets'     => $this->assets,
			'rest'       => $this->rest,
			'installer'  => $this->installer,
		);
	}

	/**
	 * Boot hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'rest_api_init', array( $this->rest, 'register_routes' ) );
	}

	/**
	 * Safe service accessor.
	 *
	 * @param string $name Service name.
	 * @return object|null
	 */
	public function service( $name ) {
		return isset( $this->services[ $name ] ) ? $this->services[ $name ] : null;
	}

	/**
	 * Activation (network-safe).
	 *
	 * @return void
	 */
	public static function activate() {
		require_once EVENTWP_DIR . '/includes/class-eventwp-installer.php';
		EventWP_Installer::activate();
	}

	/**
	 * Deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'EventWP cannot be cloned.', 'eventwp' ), '1.0.0' );
	}

	/**
	 * Unserializing is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'EventWP cannot be unserialized.', 'eventwp' ), '1.0.0' );
	}
}
