<?php
/**
 * EventWP Shortcodes — for use in Gutenberg / classic pages.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode registrar.
 */
class EventWP_Shortcodes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'eventwp_app', array( $this, 'render_app' ) );
		add_shortcode( 'eventwp_events', array( $this, 'render_events' ) );
		add_shortcode( 'eventwp_event', array( $this, 'render_event' ) );
		add_shortcode( 'eventwp_landing', array( $this, 'render_landing' ) );
	}

	/**
	 * [eventwp_app] — full app.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function render_app( $atts ) {
		$atts = shortcode_atts(
			array(
				'height' => '85vh',
			),
			$atts,
			'eventwp_app'
		);

		EventWP::instance()->helpers->enqueue_app_assets( false );

		ob_start();
		?>
		<div class="eventwp-app-shell" id="eventwp-app" data-eventwp-app style="height:<?php echo esc_attr( (string) $atts['height'] ); ?>">
			<div class="ewp-app" id="ewp-root">
				<div class="ewp-app__loading" role="status" aria-live="polite">
					<span class="ewp-spinner" aria-hidden="true"></span>
				</div>
			</div>
		</div>
		<noscript>
			<div class="eventwp-noscript"><?php esc_html_e( 'Aplikasi EventWP membutuhkan JavaScript yang aktif.', 'eventwp' ); ?></div>
		</noscript>
		<?php
		return ob_get_clean();
	}

	/**
	 * [eventwp_events limit="6" category="" view="grid" filter="1"].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function render_events( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'    => 6,
				'category' => '',
				'view'     => 'grid',
				'filter'   => '1',
				'title'    => '',
			),
			$atts,
			'eventwp_events'
		);
		$attrs = array(
			'limit'    => (int) $atts['limit'],
			'category' => sanitize_text_field( $atts['category'] ),
			'view'     => sanitize_key( $atts['view'] ),
			'filter'   => (bool) $atts['filter'],
			'title'    => sanitize_text_field( $atts['title'] ),
		);
		return EventWP::instance()->blocks->render_events( $attrs );
	}

	/**
	 * [eventwp_event id="123"].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function render_event( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'eventwp_event' );
		return EventWP::instance()->blocks->render_event_details( array( 'eventId' => (int) $atts['id'] ) );
	}

	/**
	 * [eventwp_landing] — marketing landing page (sales ticket).
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function render_landing( $atts ) {
		$atts = shortcode_atts( array(), $atts, 'eventwp_landing' );
		wp_enqueue_style( 'eventwp-landing' );
		wp_enqueue_script( 'eventwp-landing' );
		ob_start();
		include EVENTWP_DIR . '/templates/landing.php';
		return ob_get_clean();
	}
}
