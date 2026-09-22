<?php
/**
 * EventWP Admin — WP admin menu, dashboard, settings, backup download.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin UI.
 */
class EventWP_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'handle_export' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'wp_ajax_eventwp_quick_events', array( $this, 'ajax_quick_events' ) );
	}

	/**
	 * Register menus.
	 *
	 * @return void
	 */
	public function menu() {
		$cap = 'manage_options';

		add_menu_page(
			__( 'Active Nation', 'eventwp' ),
			__( 'Active Nation', 'eventwp' ),
			$cap,
			'eventwp',
			array( $this, 'render_dashboard' ),
			'dashicons-tickets-alt',
			5
		);

		add_submenu_page( 'eventwp', __( 'Ringkasan', 'eventwp' ), __( 'Ringkasan', 'eventwp' ), $cap, 'eventwp', array( $this, 'render_dashboard' ) );

		add_submenu_page( 'eventwp', __( 'Sport Events', 'eventwp' ), __( 'Sport Events', 'eventwp' ), $cap, 'edit.php?post_type=eventwp_event' );

		add_submenu_page( 'eventwp', __( 'Kategori Event', 'eventwp' ), __( 'Kategori Event', 'eventwp' ), $cap, 'edit-tags.php?taxonomy=eventwp_category&post_type=eventwp_event' );

		add_submenu_page( 'eventwp', __( 'Pengaturan', 'eventwp' ), __( 'Pengaturan', 'eventwp' ), $cap, 'eventwp-settings', array( $this, 'render_settings' ) );
	}

	/**
	 * Dashboard renderer.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$store  = EventWP::instance()->store;
		$api    = EventWP::instance()->api;
		$cards  = array(
			'events'       => $store->count( 'events' ),
			'pending'      => $store->count( 'orders', array( 'status' => 'pending' ) ),
			'tickets'      => $store->count( 'tickets' ),
			'attended'     => $store->count( 'tickets', array( 'status' => 'attended' ) ),
			'customers'    => $store->count( 'users', array( 'role' => 'customer' ) ),
			'instructors'  => $store->count( 'users', array( 'role' => 'instructor' ) ),
		);
		$events = $api->list_events_public( '', '' );
		?>
		<div class="wrap eventwp-admin-wrap">
			<h1><?php esc_html_e( 'Active Nation — Sport Event', 'eventwp' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Kelola event olahraga, tiket, dan aplikasi web mobile dari sini.', 'eventwp' ); ?></p>

			<div class="eventwp-admin-cards">
				<?php foreach ( $cards as $key => $value ) : ?>
					<div class="eventwp-admin-card">
						<span class="eventwp-admin-card__num"><?php echo esc_html( number_format_i18n( (int) $value ) ); ?></span>
						<span class="eventwp-admin-card__label"><?php echo esc_html( ucfirst( $key ) ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>

			<h2><?php esc_html_e( 'Shortcode & Blok', 'eventwp' ); ?></h2>
			<table class="widefat striped eventwp-admin-shortcodes">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Shortcode', 'eventwp' ); ?></th>
						<th><?php esc_html_e( 'Fungsi', 'eventwp' ); ?></th>
						<th><?php esc_html_e( 'Blok Gutenberg', 'eventwp' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td><code>[eventwp_app]</code></td><td><?php esc_html_e( 'Aplikasi web mobile full (login, checkout, tiket QR)', 'eventwp' ); ?></td><td><?php esc_html_e( 'EventWP — App Sport Event', 'eventwp' ); ?></td></tr>
					<tr><td><code>[eventwp_events limit="6"]</code></td><td><?php esc_html_e( 'Grid event dengan filter kategori', 'eventwp' ); ?></td><td><?php esc_html_e( 'EventWP — Daftar Event', 'eventwp' ); ?></td></tr>
					<tr><td><code>[eventwp_event id="1"]</code></td><td><?php esc_html_e( 'Detail satu event + tombol beli', 'eventwp' ); ?></td><td><?php esc_html_e( 'EventWP — Detail Event', 'eventwp' ); ?></td></tr>
					<tr><td><code>[eventwp_landing]</code></td><td><?php esc_html_e( 'Landing page premium penjualan tiket', 'eventwp' ); ?></td><td><?php esc_html_e( '— (Shortcode)', 'eventwp' ); ?></td></tr>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Event Terbaru', 'eventwp' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr><th><?php esc_html_e( 'Event', 'eventwp' ); ?></th><th><?php esc_html_e( 'Jadwal', 'eventwp' ); ?></th><th><?php esc_html_e( 'Harga', 'eventwp' ); ?></th><th><?php esc_html_e( 'Slot', 'eventwp' ); ?></th></tr>
				</thead>
				<tbody>
					<?php foreach ( array_slice( $events, 0, 10 ) as $e ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( $e['permalink'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $e['title'] ); ?></a></td>
							<td><?php echo esc_html( $e['date_start_fmt'] ); ?></td>
							<td><?php echo esc_html( $e['price_fmt'] ); ?></td>
							<td><?php echo esc_html( $e['sold'] . ' / ' . $e['capacity'] ); ?></td>
						</tr>
					<?php endforeach; ?>
					<?php if ( empty( $events ) ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'Belum ada event.', 'eventwp' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Settings renderer.
	 *
	 * @return void
	 */
	public function render_settings() {
		$store = EventWP::instance()->store;
		$get   = function ( $key, $default = '' ) use ( $store ) {
			$row = $store->get_row( 'settings', array( 'setting_key' => $key ) );
			return $row ? $row['setting_value'] : $default;
		};
		?>
		<div class="wrap eventwp-admin-wrap">
			<h1><?php esc_html_e( 'Pengaturan Active Nation', 'eventwp' ); ?></h1>

			<h2><?php esc_html_e( 'Backup Data', 'eventwp' ); ?></h2>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=eventwp-settings&eventwp_export=1' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Download Backup JSON (semua data)', 'eventwp' ); ?></a></p>
			<p class="description"><?php esc_html_e( 'Mencakup users, events, kategori, orders, tickets, templates, responses, payments, vouchers, reviews, galleries, settings — tersimpan di database yang sama dengan WordPress (phpMyAdmin).', 'eventwp' ); ?></p>

			<hr />

			<h2><?php esc_html_e( 'Brand & Tema', 'eventwp' ); ?></h2>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'Nama Brand', 'eventwp' ); ?></th><td><input type="text" value="<?php echo esc_attr( $get( 'brand_name', 'Active Nation' ) ); ?>" disabled class="regular-text" /> <span class="description"><?php esc_html_e( 'Kelola dari aplikasi web mobile (Admin → Pengaturan).', 'eventwp' ); ?></span></td></tr>
				<tr><th><?php esc_html_e( 'Warna Primer', 'eventwp' ); ?></th><td><input type="color" value="<?php echo esc_attr( $get( 'theme_primary', '#0a84ff' ) ); ?>" disabled /> <span class="description"><?php esc_html_e( 'Kelola dari aplikasi web mobile.', 'eventwp' ); ?></span></td></tr>
			</table>

			<h2><?php esc_html_e( 'WhatsApp Automation', 'eventwp' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Kelola provider, token, dan script variabel ({name}, {event}, {ticket_id}, {link}, {form_link}) dari aplikasi web mobile → Admin → Otomasi WhatsApp.', 'eventwp' ); ?></p>
			<p><?php esc_html_e( 'Status Reminder Check-in:', 'eventwp' ); ?> <strong><?php echo '1' === $get( 'wa_reminder_active' ) ? esc_html__( 'Aktif', 'eventwp' ) : esc_html__( 'Nonaktif', 'eventwp' ); ?></strong> ·
				<?php esc_html_e( 'Status Thank You:', 'eventwp' ); ?> <strong><?php echo '1' === $get( 'wa_thanks_active' ) ? esc_html__( 'Aktif', 'eventwp' ) : esc_html__( 'Nonaktif', 'eventwp' ); ?></strong></p>
		</div>
		<?php
	}

	/**
	 * Handle one-click export.
	 *
	 * @return void
	 */
	public function handle_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_GET['eventwp_export'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$json = EventWP_Installer::export_json();
		$filename = 'eventwp-backup-' . gmdate( 'Ymd-His' ) . '.json';
		header( 'Content-Description: EventWP Backup' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: application/json; charset=utf-8' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Admin assets.
	 *
	 * @return void
	 */
	public function admin_assets() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 0 !== strpos( $screen->id, 'eventwp' ) ) {
			return;
		}
		wp_enqueue_style( 'eventwp-admin', EVENTWP_URL . '/assets/css/admin.css', array(), EVENTWP_VERSION );
	}

	/**
	 * AJAX quick events (used by Gutenberg editor selector).
	 *
	 * @return void
	 */
	public function ajax_quick_events() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( null, 403 );
		}
		$list = EventWP::instance()->api->list_events_public( '', '' );
		$out  = array();
		foreach ( $list as $e ) {
			$out[] = array( 'id' => $e['id'], 'title' => $e['title'] );
		}
		wp_send_json_success( $out );
	}
}
