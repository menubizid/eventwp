<?php
/**
 * EventWP Installer — database schema, seed data and lifecycle.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/**
 * Installer.
 */
class EventWP_Installer {

	/**
	 * Singleton instance.
	 *
	 * @var EventWP_Installer|null
	 */
	public static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return EventWP_Installer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Full table list.
	 *
	 * @return string[]
	 */
	public static function tables() {
		return array(
			'users',
			'events',
			'event_categories',
			'tickets',
			'orders',
			'form_templates',
			'form_responses',
			'payments',
			'vouchers',
			'reviews',
			'galleries',
			'settings',
			'audit_logs',
		);
	}

	/**
	 * Base table name (with prefix).
	 *
	 * @param string $table Table without prefix.
	 * @return string
	 */
	public static function table( $table ) {
		global $wpdb;
		$map = array(
			'events'           => 'eventwp_events',
			'event_categories' => 'eventwp_event_categories',
			'reviews'          => 'eventwp_reviews',
			'galleries'        => 'eventwp_galleries',
		);
		if ( isset( $map[ $table ] ) ) {
			return $wpdb->prefix . $map[ $table ];
		}
		return $wpdb->prefix . 'eventwp_' . $table;
	}

	/**
	 * Update DB.
	 *
	 * @return void
	 */
	public function update_db() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		// Users — customers, instructors, admins.
		$users = self::table( 'users' );
		dbDelta( "CREATE TABLE {$users} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			role VARCHAR(20) NOT NULL DEFAULT 'customer',
			name VARCHAR(255) NOT NULL DEFAULT '',
			email VARCHAR(190) NOT NULL DEFAULT '',
			password VARCHAR(255) NOT NULL DEFAULT '',
			phone VARCHAR(40) NOT NULL DEFAULT '',
			city VARCHAR(190) NOT NULL DEFAULT '',
			avatar VARCHAR(500) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY phone (phone),
			KEY city (city),
			KEY role (role)
		) {$charset};" );

		// Events.
		$events = self::table( 'events' );
		dbDelta( "CREATE TABLE {$events} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT(20) UNSIGNED NULL,
			title VARCHAR(255) NOT NULL DEFAULT '',
			slug VARCHAR(255) NOT NULL DEFAULT '',
			category_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			instructor_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			date_start DATETIME NULL,
			date_end DATETIME NULL,
			location VARCHAR(500) NOT NULL DEFAULT '',
			latitude VARCHAR(60) NOT NULL DEFAULT '',
			longitude VARCHAR(60) NOT NULL DEFAULT '',
			price DECIMAL(14,2) NOT NULL DEFAULT 0,
			capacity INT(11) NOT NULL DEFAULT 0,
			description TEXT NULL,
			banner VARCHAR(500) NOT NULL DEFAULT '',
			visibility VARCHAR(20) NOT NULL DEFAULT 'public',
			status VARCHAR(20) NOT NULL DEFAULT 'upcoming',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY slug (slug),
			KEY category_id (category_id),
			KEY status (status)
		) {$charset};" );

		// Event Categories.
		$cats = self::table( 'event_categories' );
		dbDelta( "CREATE TABLE {$cats} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(190) NOT NULL DEFAULT '',
			slug VARCHAR(190) NOT NULL DEFAULT '',
			description TEXT NULL,
			color VARCHAR(20) NOT NULL DEFAULT '#0a84ff',
			icon VARCHAR(40) NOT NULL DEFAULT 'fa-bolt',
			form_template_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY slug (slug)
		) {$charset};" );

		// Orders.
		$orders = self::table( 'orders' );
		dbDelta( "CREATE TABLE {$orders} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			qty INT(11) NOT NULL DEFAULT 1,
			amount DECIMAL(14,2) NOT NULL DEFAULT 0,
			subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
			discount DECIMAL(14,2) NOT NULL DEFAULT 0,
			total DECIMAL(14,2) NOT NULL DEFAULT 0,
			voucher_code VARCHAR(60) NOT NULL DEFAULT '',
			payment_method VARCHAR(40) NOT NULL DEFAULT '',
			billing_name VARCHAR(255) NOT NULL DEFAULT '',
			billing_phone VARCHAR(60) NOT NULL DEFAULT '',
			payment_proof VARCHAR(500) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY user_id (user_id),
			KEY status (status)
		) {$charset};" );

		// Tickets.
		$tickets = self::table( 'tickets' );
		dbDelta( "CREATE TABLE {$tickets} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			order_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			ticket_code VARCHAR(60) NOT NULL DEFAULT '',
			qr_token VARCHAR(60) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			checkin_code VARCHAR(60) NOT NULL DEFAULT '',
			checked_in_at DATETIME NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY ticket_code (ticket_code),
			UNIQUE KEY checkin_code (checkin_code),
			KEY event_id (event_id),
			KEY user_id (user_id)
		) {$charset};" );

		// Form Templates (schema JSON).
		$templates = self::table( 'form_templates' );
		dbDelta( "CREATE TABLE {$templates} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(190) NOT NULL DEFAULT '',
			schema_data LONGTEXT NULL,
			fields_data LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) {$charset};" );

		// Form Responses.
		$responses = self::table( 'form_responses' );
		dbDelta( "CREATE TABLE {$responses} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			ticket_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			template_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			response_data LONGTEXT NULL,
			submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY ticket_id (ticket_id)
		) {$charset};" );

		// Payments.
		$payments = self::table( 'payments' );
		dbDelta( "CREATE TABLE {$payments} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			type VARCHAR(20) NOT NULL DEFAULT 'bank_transfer',
			name VARCHAR(190) NOT NULL DEFAULT '',
			account_holder VARCHAR(190) NOT NULL DEFAULT '',
			account_number VARCHAR(60) NOT NULL DEFAULT '',
			image_url VARCHAR(500) NOT NULL DEFAULT '',
			instructions TEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) {$charset};" );

		// Vouchers.
		$vouchers = self::table( 'vouchers' );
		dbDelta( "CREATE TABLE {$vouchers} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			code VARCHAR(60) NOT NULL DEFAULT '',
			type VARCHAR(20) NOT NULL DEFAULT 'percentage',
			value DECIMAL(14,2) NOT NULL DEFAULT 0,
			min_purchase DECIMAL(14,2) NOT NULL DEFAULT 0,
			max_uses INT(11) NOT NULL DEFAULT 0,
			used_count INT(11) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code)
		) {$charset};" );

		// Reviews.
		$reviews = self::table( 'reviews' );
		dbDelta( "CREATE TABLE {$reviews} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			ticket_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			author VARCHAR(190) NOT NULL DEFAULT '',
			rating TINYINT(1) NOT NULL DEFAULT 5,
			comment TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id)
		) {$charset};" );

		// Galleries (UGC).
		$galleries = self::table( 'galleries' );
		dbDelta( "CREATE TABLE {$galleries} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			image_url VARCHAR(500) NOT NULL DEFAULT '',
			caption VARCHAR(255) NOT NULL DEFAULT '',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id)
		) {$charset};" );

		// Settings (WA scripts etc.).
		$settings = self::table( 'settings' );
		dbDelta( "CREATE TABLE {$settings} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			setting_key VARCHAR(190) NOT NULL DEFAULT '',
			setting_value LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY setting_key (setting_key)
		) {$charset};" );

		// Audit Logs.
		$logs = self::table( 'audit_logs' );
		dbDelta( "CREATE TABLE {$logs} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			action VARCHAR(120) NOT NULL DEFAULT '',
			context VARCHAR(255) NOT NULL DEFAULT '',
			ip VARCHAR(60) NOT NULL DEFAULT '',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) {$charset};" );

		update_option( 'eventwp_db_version', EVENTWP_DB_VERSION );
	}

	/**
	 * Maybe install/upgrade.
	 *
	 * @return void
	 */
	public static function maybe_install() {
		if ( get_option( 'eventwp_db_version' ) !== EVENTWP_DB_VERSION ) {
			$installer = self::instance();
			$installer->update_db();
			$installer->seed_if_empty();
			update_option( 'eventwp_db_version', EVENTWP_DB_VERSION );
		}
	}

	/**
	 * Seed defaults when tables are empty.
	 *
	 * @return void
	 */
	public function seed_if_empty() {
		global $wpdb;

		// Admin + demo instructor + demo customer.
		if ( 0 === (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table( 'users' ) ) ) {
			$admin_pass = wp_hash_password( 'admin123' );
			$wpdb->insert(
				self::table( 'users' ),
				array(
					'role'     => 'admin',
					'name'     => 'Active Nation Admin',
					'email'    => get_bloginfo( 'admin_email' ),
					'password' => $admin_pass,
					'phone'    => '6281230000001',
					'city'     => 'Jakarta',
					'status'   => 'active',
				)
			);
			$wpdb->insert(
				self::table( 'users' ),
				array(
					'role'     => 'instructor',
					'name'     => 'Coach Rina Maharani',
					'email'    => 'rina@activenation.id',
					'password' => wp_hash_password( 'coach123' ),
					'phone'    => '6281230000002',
					'city'     => 'Jakarta',
					'status'   => 'active',
				)
			);
			$wpdb->insert(
				self::table( 'users' ),
				array(
					'role'     => 'customer',
					'name'     => 'Ayu Lestari',
					'email'    => 'ayu@example.com',
					'password' => wp_hash_password( 'customer123' ),
					'phone'    => '6281230000003',
					'city'     => 'Jakarta',
					'status'   => 'active',
				)
			);
		}

		// Categories.
		if ( 0 === (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table( 'event_categories' ) ) ) {
			$cats = array(
				array( 'Zumba Step', 'zumba-step', '#ff2d87', 'fa-music' ),
				array( '3x3 Basket', '3x3-basket', '#ff7a1a', 'fa-basketball' ),
				array( 'Padel', 'padel', '#0a84ff', 'fa-table-tennis-paddle-ball' ),
				array( 'Yoga', 'yoga', '#30d158', 'fa-spa' ),
				array( 'Run', 'run', '#bf5af2', 'fa-person-running' ),
			);
			foreach ( $cats as $c ) {
				$wpdb->insert(
					self::table( 'event_categories' ),
					array(
						'name'        => $c[0],
						'slug'        => $c[1],
						'description' => 'Event ' . $c[0] . ' dari Active Nation.',
						'color'       => $c[2],
						'icon'        => $c[3],
						'status'      => 'active',
					)
				);
			}
		}

		// Payments.
		if ( 0 === (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table( 'payments' ) ) ) {
			$wpdb->insert(
				self::table( 'payments' ),
				array(
					'type'           => 'bank_transfer',
					'name'           => 'BCA',
					'account_holder' => 'PT Active Nation Indonesia',
					'account_number' => '8123456789',
					'instructions'   => 'Transfer sesuai nominal "Total" dan unggah bukti pembayaran. E-ticket diterbitkan setelah verifikasi admin.',
					'status'         => 'active',
				)
			);
			$wpdb->insert(
				self::table( 'payments' ),
				array(
					'type'           => 'qris',
					'name'           => 'QRIS',
					'account_holder' => 'Active Nation',
					'account_number' => '',
					'image_url'      => EVENTWP_URL . '/assets/img/qris-placeholder.svg',
					'instructions'   => 'Scan QRIS dengan aplikasi e-wallet / m-banking favoritmu.',
					'status'         => 'active',
				)
			);
		}

		// Vouchers.
		if ( 0 === (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table( 'vouchers' ) ) ) {
			$wpdb->insert(
				self::table( 'vouchers' ),
				array(
					'code'         => 'NATION10',
					'type'         => 'percentage',
					'value'        => 10,
					'min_purchase' => 50000,
					'max_uses'     => 0,
					'used_count'   => 0,
					'status'       => 'active',
				)
			);
			$wpdb->insert(
				self::table( 'vouchers' ),
				array(
					'code'         => 'ACTIVE15',
					'type'         => 'nominal',
					'value'        => 15000,
					'min_purchase' => 100000,
					'max_uses'     => 0,
					'used_count'   => 0,
					'status'       => 'active',
				)
			);
		}

		// Settings.
		if ( 0 === (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table( 'settings' ) ) ) {
			$this->seed_settings();
		}

		// Events via wp_insert_post (default when DB events empty).
		$this->seed_events();
	}

	/**
	 * Seed settings keys.
	 *
	 * @return void
	 */
	private function seed_settings() {
		global $wpdb;
		$defaults = array(
			'wa_api_clients'      => wp_json_encode(
				array(
					array( 'id' => 'ruangwa', 'name' => 'RuangWA' ),
					array( 'id' => 'fonnte', 'name' => 'Fonnte' ),
					array( 'id' => 'wablas', 'name' => 'Wablas' ),
				),
				JSON_UNESCAPED_SLASHES
			),
			'wa_default_provider' => 'ruangwa',
			'wa_default_token'    => '',
			'wa_default_sender'   => '',
			'wa_reminder_active'  => '0',
			'wa_reminder_script'  => "Halo {name} 👋, jangan lupa check-in event *{event}* hari ini ya!\nTiket: {ticket_id}\nCheck-in: {link}",
			'wa_thanks_active'    => '0',
			'wa_thanks_script'    => "Terima kasih {name} sudah hadir di *{event}*! 🎉\nBeri ulasan & bagikan momenmu: {link}",
			'theme_primary'       => '#0a84ff',
			'theme_accent'        => '#ff2d87',
			'brand_name'          => 'Active Nation',
		);
		foreach ( $defaults as $key => $value ) {
			$wpdb->insert(
				self::table( 'settings' ),
				array(
					'setting_key'   => $key,
					'setting_value' => $value,
				)
			);
		}
	}

	/**
	 * Seed five showcase events (creates CPT posts, meta, and mirrored DB rows).
	 *
	 * @return void
	 */
	public function seed_events() {
		global $wpdb;
		if ( 0 !== (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table( 'events' ) ) ) {
			return;
		}

		$categories = $wpdb->get_results( 'SELECT id, slug FROM ' . self::table( 'event_categories' ), ARRAY_A );
		$cat_ids    = array();
		foreach ( (array) $categories as $c ) {
			$cat_ids[ $c['slug'] ] = (int) $c['id'];
		}
		$instructor = (int) $wpdb->get_var( "SELECT id FROM " . self::table( 'users' ) . " WHERE role='instructor' ORDER BY id ASC LIMIT 1" );

		$demo = array(
			array(
				'title'       => 'Zumba Step Sunset Jakarta',
				'slug'        => 'zumba-step-sunset-jakarta',
				'category'    => 'zumba-step',
				'date_start'  => '+7 days 17:00',
				'date_end'    => '+7 days 19:00',
				'location'    => 'Gelora Bung Karno, Senayan, Jakarta',
				'price'       => 75000,
				'capacity'    => 120,
				'description' => 'Sesi Zumba Step penuh energi di golden hour. Cocok untuk semua level — bakar kalori sambil bersenang-senang bersama komunitas Active Nation.',
			),
			array(
				'title'       => '3x3 Basket Street League',
				'slug'        => '3x3-basket-street-league',
				'category'    => '3x3-basket',
				'date_start'  => '+10 days 13:00',
				'date_end'    => '+10 days 18:00',
				'location'    => 'Lapangan Banteng, Jakarta Pusat',
				'price'       => 120000,
				'capacity'    => 64,
				'description' => 'Turnamen 3x3 kompetitif untuk tim streetball. Satu tiket satu pemain — bawa squad terbaikmu dan rebut gelar juara.',
			),
			array(
				'title'       => 'Padel Pro Clinic Bandung',
				'slug'        => 'padel-pro-clinic-bandung',
				'category'    => 'padel',
				'date_start'  => '+14 days 09:00',
				'date_end'    => '+14 days 12:00',
				'location'    => 'Dago Padel Club, Bandung',
				'price'       => 150000,
				'capacity'    => 40,
				'description' => 'Clinic intensif teknik padel bersama coach bersertifikat. Racket tersedia di lokasi — datang membawa semangat saja.',
			),
			array(
				'title'       => 'Sunrise Yoga Session',
				'slug'        => 'sunrise-yoga-session',
				'category'    => 'yoga',
				'date_start'  => '+5 days 06:00',
				'date_end'    => '+5 days 07:30',
				'location'    => 'Tebet Eco Park, Jakarta Selatan',
				'price'       => 50000,
				'capacity'    => 80,
				'description' => 'Mulai harimu dengan flow yoga yang menenangkan di ruang terbuka hijau. Matras sewa tersedia untuk peserta.',
			),
			array(
				'title'       => 'Active Nation Night Run 5K',
				'slug'        => 'active-nation-night-run-5k',
				'category'    => 'run',
				'date_start'  => '+21 days 19:00',
				'date_end'    => '+21 days 22:00',
				'location'    => 'Monumen Nasional (Monas), Jakarta',
				'price'       => 95000,
				'capacity'    => 500,
				'description' => 'Lari malam 5K di bawah gemerlap kota. Tersedia race pack, medali finisher, dan refreshment di sepanjang rute.',
			),
		);

		foreach ( $demo as $i => $d ) {
			$meta = array(
				'eventwp_event_status'       => 'upcoming',
				'eventwp_event_date_start'   => gmdate( 'Y-m-d H:i:s', strtotime( $d['date_start'] ) ),
				'eventwp_event_date_end'     => gmdate( 'Y-m-d H:i:s', strtotime( $d['date_end'] ) ),
				'eventwp_event_location'     => $d['location'],
				'eventwp_event_price'        => $d['price'],
				'eventwp_event_capacity'     => $d['capacity'],
				'eventwp_event_visibility'   => 'public',
				'eventwp_event_gallery'      => '',
				'eventwp_event_primary_cat'  => isset( $cat_ids[ $d['category'] ] ) ? $cat_ids[ $d['category'] ] : 0,
				'eventwp_event_instructor_id' => $instructor,
			);

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'eventwp_event',
					'post_status'  => 'publish',
					'post_title'   => $d['title'],
					'post_name'    => $d['slug'],
					'post_content' => $d['description'],
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			foreach ( $meta as $k => $v ) {
				update_post_meta( $post_id, $k, $v );
			}

			$cat_id = isset( $cat_ids[ $d['category'] ] ) ? $cat_ids[ $d['category'] ] : 0;
			if ( $cat_id ) {
				wp_set_object_terms( $post_id, (int) $cat_id, 'eventwp_category' );
			}

			// Mirror into DB.
			$wpdb->insert(
				self::table( 'events' ),
				array(
					'event_id'      => (int) $post_id,
					'title'         => $d['title'],
					'slug'          => $d['slug'],
					'category_id'   => $cat_id,
					'instructor_id' => $instructor,
					'date_start'    => $meta['eventwp_event_date_start'],
					'date_end'      => $meta['eventwp_event_date_end'],
					'location'      => $d['location'],
					'price'         => $d['price'],
					'capacity'      => $d['capacity'],
					'description'   => $d['description'],
					'visibility'    => 'public',
					'status'        => 'upcoming',
				)
			);
		}
	}

	/**
	 * Activation.
	 *
	 * @return void
	 */
	public static function activate() {
		$installer = self::instance();
		$installer->update_db();
		$installer->ensure_caps();
		$installer->seed_if_empty();
		flush_rewrite_rules();
	}

	/**
	 * Ensure administrator capabilities (EventWP reuses manage_options).
	 *
	 * @return void
	 */
	private function ensure_caps() {
		$role = get_role( 'administrator' );
		if ( $role ) {
			$role->add_cap( 'read' );
		}
	}

	/**
	 * Export everything relevant as a JSON backup.
	 *
	 * @return string
	 */
	public static function export_json() {
		$store = EventWP::instance()->store;
		$out   = array(
			'meta'       => array(
				'plugin'         => 'EventWP',
				'version'        => EVENTWP_VERSION,
				'exported_at'    => gmdate( 'c' ),
				'site'           => get_bloginfo( 'url' ),
				'table_prefix'   => $GLOBALS['wpdb']->prefix,
				'wordpress_core' => array(
					'note' => 'Events are also mirrored as CPT posts (eventwp_event) + postmeta; export those with Tools → Export as well.',
				),
			),
			'users'      => array(),
			'events'     => array(),
			'categories' => array(),
			'orders'     => array(),
			'tickets'    => array(),
			'templates'  => array(),
			'responses'  => array(),
			'payments'   => array(),
			'vouchers'   => array(),
			'reviews'    => array(),
			'galleries'  => array(),
			'settings'   => array(),
		);
		$order = array(
			'users'      => 'users',
			'events'     => 'events',
			'categories' => 'event_categories',
			'orders'     => 'orders',
			'tickets'    => 'tickets',
			'templates'  => 'form_templates',
			'responses'  => 'form_responses',
			'payments'   => 'payments',
			'vouchers'   => 'vouchers',
			'reviews'    => 'reviews',
			'galleries'  => 'galleries',
			'settings'   => 'settings',
		);
		foreach ( $order as $key => $table ) {
			$rows = $store->get_rows( $table );
			$out[ $key ] = is_array( $rows ) ? $rows : array();
		}
		return wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}
}
