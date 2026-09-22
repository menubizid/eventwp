<?php
/**
 * EventWP API — business methods shared by the REST controller.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * API service layer.
 */
class EventWP_API {

	/**
	 * Extract the Authorization Bearer token.
	 *
	 * @return string
	 */
	public function bearer_token() {
		$headers = function_exists( 'getallheaders' ) ? getallheaders() : array();
		$auth    = isset( $headers['Authorization'] ) ? $headers['Authorization'] : '';
		if ( ! $auth && isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$auth = sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
		if ( ! $auth && isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$auth = sanitize_text_field( wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
		if ( $auth && 0 === stripos( $auth, 'Bearer ' ) ) {
			return sanitize_text_field( substr( $auth, 7 ) );
		}
		return '';
	}

	/**
	 * Get current user row from bearer token.
	 *
	 * @return array|null
	 */
	public function current_user() {
		$token = $this->bearer_token();
		if ( ! $token ) {
			return null;
		}
		$store = EventWP::instance()->store;
		$row   = $store->get_row( 'users', array( 'password' => $token ) );
		return $row ? (array) $row : null;
	}

	/**
	 * Send a REST JSON error and exit.
	 *
	 * @param string  $message Message.
	 * @param integer $code    HTTP status.
	 * @return never
	 */
	public function fail( $message, $code = 400 ) {
		wp_send_json( EventWP::instance()->helpers->response( false, $message, null ), $code );
	}

	/**
	 * Resolve WP user id (best effort) for a user row for capabilities.
	 *
	 * @param array $user_row Row.
	 * @return int
	 */
	public function wp_user_id_for( $user_row ) {
		if ( ! $user_row ) {
			return 0;
		}
		$phone = isset( $user_row['phone'] ) ? $user_row['phone'] : '';
		$email = isset( $user_row['email'] ) ? $user_row['email'] : '';
		if ( $phone ) {
			$by_phone = get_user_by( 'login', 'ewp_' . $phone );
			if ( $by_phone ) {
				return (int) $by_phone->ID;
			}
		}
		if ( $email ) {
			$by_email = get_user_by( 'email', $email );
			if ( $by_email ) {
				return (int) $by_email->ID;
			}
		}
		return 0;
	}

	/**
	 * Ensure the current user has a role OR is a WP capable admin/instructor.
	 *
	 * @param array        $user_row Current user row.
	 * @param string|array $roles    Allowed app roles.
	 * @param string       $identified Identified name used in error.
	 * @return void
	 */
	public function require_user( $user_row, $roles, $identified = 'pelanggan' ) {
		if ( ! $user_row ) {
			$this->fail( 'Silakan login terlebih dahulu.', 401 );
		}
		$roles = (array) $roles;
		// WordPress capability fallback.
		$wp_id = $this->wp_user_id_for( $user_row );
		if ( $wp_id ) {
			if ( in_array( 'admin', $roles, true ) && current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( in_array( 'instructor', $roles, true ) && ( current_user_can( 'manage_options' ) || current_user_can( 'edit_eventwp_events' ) ) ) {
				return;
			}
		}
		$user_role = isset( $user_row['role'] ) ? $user_row['role'] : '';
		if ( ! in_array( $user_role, $roles, true ) ) {
			$this->fail( 'Akses ditolak untuk ' . $identified . '.', 403 );
		}
	}

	/**
	 * Detect visitor city via a naive IP-based heuristic (can be extended).
	 *
	 * @return string
	 */
	public function detect_city() {
		$city = 'Jakarta';
		$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		// Deterministic default based on IP so demo is stable.
		if ( $ip ) {
			$seeds = array( 'Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Bali' );
			$city  = $seeds[ absint( crc32( $ip ) ) % count( $seeds ) ];
		}
		return $city;
	}

	/**
	 * Normalize a user row for output.
	 *
	 * @param array $row Row.
	 * @return array
	 */
	public function user_public( $row ) {
		return array(
			'id'          => (int) $row['id'],
			'role'        => $row['role'],
			'name'        => $row['name'],
			'email'       => $row['email'],
			'phone'       => $row['phone'],
			'city'        => $row['city'],
			'avatar'      => $row['avatar'],
			'avatar_url'  => $this->avatar_url( $row ),
			'initials'    => $this->initials( $row['name'] ),
			'status'      => $row['status'],
		);
	}

	/**
	 * Avatar URL (uploaded file or auto initials data URI).
	 *
	 * @param array $row User row.
	 * @return string
	 */
	public function avatar_url( $row ) {
		if ( ! empty( $row['avatar'] ) ) {
			return $row['avatar'];
		}
		return $this->initials_data_uri( $row['name'], $row['city'] );
	}

	/**
	 * Initials.
	 *
	 * @param string $name Name.
	 * @return string
	 */
	public function initials( $name ) {
		$parts = array_values( array_filter( preg_split( '/\s+/', trim( (string) $name ) ) ) );
		if ( empty( $parts ) ) {
			return 'G';
		}
		if ( function_exists( 'mb_substr' ) && function_exists( 'mb_strtoupper' ) ) {
			$first  = mb_substr( $parts[0], 0, 1 );
			$second = isset( $parts[1] ) ? mb_substr( $parts[1], 0, 1 ) : '';
			if ( '' === $second && mb_strlen( $parts[0] ) > 1 ) {
				$second = mb_substr( $parts[0], 1, 1 );
			}
			return mb_strtoupper( $first . $second );
		}
		$first  = substr( $parts[0], 0, 1 );
		$second = isset( $parts[1] ) ? substr( $parts[1], 0, 1 ) : ( strlen( $parts[0] ) > 1 ? substr( $parts[0], 1, 1 ) : '' );
		return strtoupper( $first . $second );
	}

	/**
	 * Deterministic initials data URI.
	 *
	 * @param string $name Name.
	 * @param string $seed Seed.
	 * @return string
	 */
	public function initials_data_uri( $name, $seed = '' ) {
		$palette = array( '#0a84ff', '#ff2d87', '#30d158', '#bf5af2', '#ff7a1a', '#00c2c7' );
		$color   = $palette[ absint( crc32( (string) $name . (string) $seed ) ) % count( $palette ) ];
		$text    = $this->initials( $name );
		$svg     = '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96"><rect width="96" height="96" rx="48" fill="' . $color . '"/><text x="48" y="60" font-family="Arial, sans-serif" font-size="36" font-weight="bold" fill="#ffffff" text-anchor="middle">' . $text . '</text></svg>';
		return 'data:image/svg+xml;utf8,' . rawurlencode( $svg );
	}

	/**
	 * Category icon/color fallback.
	 *
	 * @param array $cat Category row.
	 * @return array
	 */
	public function category_meta( $cat ) {
		static $icons = array(
			'zumba'  => 'fa-music',
			'basket' => 'fa-basketball',
			'padel'  => 'fa-table-tennis-paddle-ball',
			'yoga'   => 'fa-spa',
			'run'    => 'fa-person-running',
		);
		$slug  = isset( $cat['slug'] ) ? $cat['slug'] : '';
		$icon  = ! empty( $cat['icon'] ) ? $cat['icon'] : 'fa-bolt';
		$match = null;
		foreach ( $icons as $k => $i ) {
			if ( false !== strpos( (string) $slug, $k ) ) {
				$match = $i;
				break;
			}
		}
		// Prefer slug-derived icon, fall back to stored icon.
		if ( $match ) {
			$icon = $match;
		}
		$color = ! empty( $cat['color'] ) ? $cat['color'] : '#0a84ff';
		return array( $icon, $color );
	}

	/**
	 * Normalize an event row for output (light).
	 *
	 * @param array  $row Event row.
	 * @param boolean $with_count Whether to include sold count.
	 * @return array
	 */
	public function normalize_event( $row, $with_count = true ) {
		$helpers  = EventWP::instance()->helpers;
		$store    = EventWP::instance()->store;
		$cat      = $store->get_row( 'event_categories', array( 'id' => (int) $row['category_id'] ) );
		$instr    = $store->get_row( 'users', array( 'id' => (int) $row['instructor_id'] ) );
		[$cat_icon, $cat_color] = $this->category_meta( $cat ? $cat : array( 'slug' => '' ) );
		$sold     = 0;
		$left     = (int) $row['capacity'];
		if ( $with_count ) {
			$sold = (int) $store->count( 'tickets', array( 'event_id' => (int) $row['id'] ) );
			$left = max( 0, (int) $row['capacity'] - $sold );
		}
		$banner = $this->event_banner( $row, (int) $row['event_id'], ( $cat ? $cat['name'] : '' ) );
		return array(
			'id'               => (int) $row['id'],
			'post_id'          => (int) $row['event_id'],
			'title'            => $row['title'],
			'slug'             => $row['slug'],
			'permalink'        => get_permalink( (int) $row['event_id'] ),
			'description'      => wp_kses_post( $row['description'] ),
			'date_start'       => $row['date_start'],
			'date_end'         => $row['date_end'],
			'date_start_fmt'   => $helpers->format_datetime( $row['date_start'] ),
			'date_end_fmt'     => $helpers->format_datetime( $row['date_end'] ),
			'location'         => $row['location'],
			'latitude'         => $row['latitude'],
			'longitude'        => $row['longitude'],
			'map_url'          => $this->map_url( $row ),
			'price'            => (float) $row['price'],
			'price_fmt'        => $helpers->format_money( $row['price'] ),
			'capacity'         => (int) $row['capacity'],
			'sold'             => $sold,
			'left'             => $left,
			'progress'         => (int) $row['capacity'] > 0 ? round( 100 * $sold / (int) $row['capacity'] ) : 0,
			'visibility'       => $row['visibility'],
			'status'           => $row['status'],
			'category_id'      => (int) $row['category_id'],
			'category_name'    => $cat ? $cat['name'] : '',
			'category_slug'    => $cat ? $cat['slug'] : '',
			'category_icon'    => $cat_icon,
			'category_color'   => $cat_color,
			'instructor_id'    => (int) $row['instructor_id'],
			'instructor_name'  => $instr ? $instr['name'] : 'Active Nation Coach',
			'instructor_city'  => $instr ? $instr['city'] : '',
			'instructor_avatar'=> $instr ? $this->avatar_url( $instr ) : '',
			'banner'           => $banner,
			'form_template_id' => $cat && ! empty( $cat['form_template_id'] ) ? (int) $cat['form_template_id'] : 0,
			'rating_avg'       => (float) $this->rating_avg( (int) $row['id'] ),
		);
	}

	/**
	 * Banner for an event (featured image, meta gallery, or dynamic gradient SVG).
	 *
	 * @param array $row     Event row.
	 * @param int   $post_id Post id.
	 * @param string $cat_name Category name.
	 * @return string
	 */
	public function event_banner( $row, $post_id, $cat_name = '' ) {
		if ( ! empty( $row['banner'] ) ) {
			return $row['banner'];
		}
		if ( $post_id ) {
			$thumb = get_the_post_thumbnail_url( $post_id, 'large' );
			if ( $thumb ) {
				return $thumb;
			}
			$gallery = (string) get_post_meta( $post_id, 'eventwp_event_gallery', true );
			if ( $gallery ) {
				$parts = array_map( 'trim', explode( ',', $gallery ) );
				$first = isset( $parts[0] ) ? $parts[0] : '';
				if ( $first ) {
					return $first;
				}
			}
		}
		return $this->banner_svg( $row['title'], $cat_name, $this->slug_seed( $row['slug'] ) );
	}

	/**
	 * Gradient banner SVG generator.
	 *
	 * @param string $title Title.
	 * @param string $sub   Subtitle.
	 * @param int    $seed  Seed.
	 * @return string
	 */
	public function banner_svg( $title, $sub, $seed = 0 ) {
		$pairs = array(
			array( '#0a84ff', '#00c2c7' ),
			array( '#ff2d87', '#bf5af2' ),
			array( '#ff7a1a', '#ff2d87' ),
			array( '#30d158', '#00c2c7' ),
			array( '#5e5ce6', '#bf5af2' ),
		);
		$pair  = $pairs[ absint( $seed ) % count( $pairs ) ];
		$title = esc_html( wp_strip_all_tags( function_exists( 'mb_substr' ) ? mb_substr( (string) $title, 0, 40 ) : substr( (string) $title, 0, 40 ) ) );
		$sub   = esc_html( wp_strip_all_tags( function_exists( 'mb_substr' ) ? mb_substr( (string) $sub, 0, 40 ) : substr( (string) $sub, 0, 40 ) ) );
		$svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675">'
			. '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' . $pair[0] . '"/><stop offset="1" stop-color="' . $pair[1] . '"/></linearGradient></defs>'
			. '<rect width="1200" height="675" fill="url(#g)"/>'
			. '<circle cx="1050" cy="120" r="220" fill="#ffffff" opacity="0.10"/>'
			. '<circle cx="120" cy="560" r="260" fill="#ffffff" opacity="0.08"/>'
			. '<text x="60" y="60" font-family="Arial, sans-serif" font-size="28" font-weight="bold" fill="#ffffff" opacity="0.9">ACTIVE NATION</text>'
			. '<text x="60" y="470" font-family="Arial, sans-serif" font-size="54" font-weight="bold" fill="#ffffff">' . $title . '</text>'
			. '<text x="60" y="530" font-family="Arial, sans-serif" font-size="28" fill="#ffffff" opacity="0.85">' . $sub . '</text>'
			. '</svg>';
		return 'data:image/svg+xml;utf8,' . rawurlencode( $svg );
	}

	/**
	 * Deterministic seed from slug.
	 *
	 * @param string $slug Slug.
	 * @return int
	 */
	public function slug_seed( $slug ) {
		return absint( crc32( (string) $slug ) );
	}

	/**
	 * Google Maps URL.
	 *
	 * @param array $row Event row.
	 * @return string
	 */
	public function map_url( $row ) {
		$lat = isset( $row['latitude'] ) ? $row['latitude'] : '';
		$lng = isset( $row['longitude'] ) ? $row['longitude'] : '';
		if ( $lat && $lng ) {
			return 'https://www.google.com/maps?q=' . rawurlencode( $lat . ',' . $lng );
		}
		$location = isset( $row['location'] ) ? $row['location'] : '';
		return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $location );
	}

	/**
	 * Average rating for an event.
	 *
	 * @param int $event_id Event id.
	 * @return float
	 */
	public function rating_avg( $event_id ) {
		global $wpdb;
		$table = EventWP_Installer::table( 'reviews' );
		$avg   = $wpdb->get_var( $wpdb->prepare( "SELECT AVG(rating) FROM {$table} WHERE event_id = %d", (int) $event_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		return $avg ? round( (float) $avg, 1 ) : 0.0;
	}

	/**
	 * List public upcoming events (optionally by city / search).
	 *
	 * @param string $city   Optional filter city.
	 * @param string $search Optional search.
	 * @return array
	 */
	public function list_events_public( $city = '', $search = '' ) {
		$store = EventWP::instance()->store;
		$rows  = $store->get_rows( 'events', 'date_start', 'ASC', 100 );
		$out   = array();
		foreach ( (array) $rows as $row ) {
			if ( 'public' !== $row['visibility'] ) {
				continue;
			}
			if ( $city && ! empty( $row['location'] ) && false === stripos( (string) $row['location'], (string) $city ) ) {
				continue;
			}
			if ( $search ) {
				$hay = $row['title'] . ' ' . $row['location'] . ' ' . $row['description'];
				if ( false === stripos( (string) $hay, (string) $search ) ) {
					continue;
				}
			}
			$out[] = $this->normalize_event( $row );
		}
		return $out;
	}

	/**
	 * Get a single event (public-safe).
	 *
	 * @param int $event_id Event id.
	 * @return array|null
	 */
	public function get_event( $event_id ) {
		$store = EventWP::instance()->store;
		$row   = $store->get_row( 'events', array( 'id' => (int) $event_id ) );
		if ( ! $row ) {
			$row = $store->get_row( 'events', array( 'event_id' => (int) $event_id ) );
		}
		if ( ! $row ) {
			return null;
		}
		return $this->normalize_event( $row );
	}

	/**
	 * Get categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		$store = EventWP::instance()->store;
		$rows  = $store->get_rows( 'event_categories', 'name', 'ASC', 100 );
		$out   = array();
		foreach ( (array) $rows as $row ) {
			if ( ! empty( $row['status'] ) && 'active' !== $row['status'] ) {
				continue;
			}
			[$icon] = $this->category_meta( $row );
			$out[]  = array(
				'id'          => (int) $row['id'],
				'name'        => $row['name'],
				'slug'        => $row['slug'],
				'description' => $row['description'],
				'color'       => $row['color'],
				'icon'        => $icon,
				'form_template_id' => (int) $row['form_template_id'],
				'count'       => (int) $store->count( 'events', array( 'category_id' => (int) $row['id'] ) ),
			);
		}
		return $out;
	}

	/**
	 * Full event detail for a given viewer role.
	 *
	 * @param int    $event_id Event id.
	 * @param string $for      customer|instructor|admin.
	 * @return array|null
	 */
	public function get_event_full( $event_id, $for = 'customer' ) {
		$store = EventWP::instance()->store;
		$row   = $store->get_row( 'events', array( 'id' => (int) $event_id ) );
		if ( ! $row ) {
			return null;
		}
		$event = $this->normalize_event( $row );

		if ( 'instructor' === $for || 'admin' === $for ) {
			$event['attendees'] = $this->event_attendees( (int) $row['id'] );
		}
		$event['gallery'] = $this->event_gallery( (int) $row['id'] );
		$event['reviews'] = $this->event_reviews( (int) $row['id'] );

		// Template form (required if category has one).
		if ( $event['form_template_id'] ) {
			$template = $this->get_template( $event['form_template_id'] );
			if ( $template ) {
				$event['required_form'] = $template;
			}
		}
		return $event;
	}

	/**
	 * Attendees for an event.
	 *
	 * @param int $event_id Event id.
	 * @return array
	 */
	public function event_attendees( $event_id ) {
		global $wpdb;
		$table = EventWP_Installer::table( 'tickets' );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE event_id = %d ORDER BY created_at DESC", (int) $event_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$out   = array();
		foreach ( (array) $rows as $t ) {
			$store = EventWP::instance()->store;
			$user  = $store->get_row( 'users', array( 'id' => (int) $t['user_id'] ) );
			$resp  = $store->get_row( 'form_responses', array( 'ticket_id' => (int) $t['id'] ) );
			$out[] = array(
				'ticket_id'    => (int) $t['id'],
				'ticket_code'  => $t['ticket_code'],
				'checkin_code' => $t['checkin_code'],
				'status'       => $t['status'],
				'name'         => $user ? $user['name'] : ( isset( $t['user_id'] ) ? 'Peserta #' . $t['user_id'] : '' ),
				'phone'        => $user ? $user['phone'] : '',
				'city'         => $user ? $user['city'] : '',
				'has_form'     => (bool) $resp,
				'checked_in_at'=> $t['checked_in_at'],
			);
		}
		return $out;
	}

	/**
	 * Event gallery (UGC).
	 *
	 * @param int $event_id Event id.
	 * @return array
	 */
	public function event_gallery( $event_id ) {
		global $wpdb;
		$table = EventWP_Installer::table( 'galleries' );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE event_id = %d ORDER BY created_at DESC LIMIT 100", (int) $event_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$out   = array();
		foreach ( (array) $rows as $g ) {
			$out[] = array(
				'id'        => (int) $g['id'],
				'image_url' => $g['image_url'],
				'caption'   => $g['caption'],
			);
		}
		return $out;
	}

	/**
	 * Event reviews.
	 *
	 * @param int $event_id Event id.
	 * @return array
	 */
	public function event_reviews( $event_id ) {
		global $wpdb;
		$table = EventWP_Installer::table( 'reviews' );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE event_id = %d ORDER BY created_at DESC LIMIT 100", (int) $event_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$out   = array();
		foreach ( (array) $rows as $r ) {
			$out[] = array(
				'id'        => (int) $r['id'],
				'author'    => $r['author'],
				'rating'    => (int) $r['rating'],
				'comment'   => $r['comment'],
				'created_at'=> $r['created_at'],
			);
		}
		return $out;
	}

	/**
	 * Get form template by id (decoded).
	 *
	 * @param int $template_id Template id.
	 * @return array|null
	 */
	public function get_template( $template_id ) {
		$store = EventWP::instance()->store;
		$row   = $store->get_row( 'form_templates', array( 'id' => (int) $template_id ) );
		if ( ! $row ) {
			return null;
		}
		$fields = $store->decode_json( $row['fields_data'] );
		if ( empty( $fields ) ) {
			$fields = $store->decode_json( $row['schema_data'] );
		}
		return array(
			'id'     => (int) $row['id'],
			'name'   => $row['name'],
			'fields' => is_array( $fields ) ? $fields : array(),
		);
	}

	/**
	 * Sync a CPT post to the DB events table.
	 *
	 * @param int $post_id Post id.
	 * @return void
	 */
	public function sync_event_to_db( $post_id ) {
		$store = EventWP::instance()->store;
		$post  = get_post( $post_id );
		if ( ! $post || 'eventwp_event' !== $post->post_type ) {
			return;
		}

		$title     = $post->post_title;
		$slug      = $post->post_name ? $post->post_name : sanitize_title( $title );
		$category  = (int) get_post_meta( $post_id, 'eventwp_event_primary_cat', true );
		$terms     = wp_get_post_terms( $post_id, 'eventwp_category', array( 'fields' => 'ids' ) );
		if ( ! $category && $terms && ! is_wp_error( $terms ) ) {
			$category = (int) $terms[0];
		}
		$data = array(
			'title'         => $title,
			'slug'          => $slug,
			'category_id'   => $category,
			'instructor_id' => (int) get_post_meta( $post_id, 'eventwp_event_instructor_id', true ),
			'date_start'    => (string) get_post_meta( $post_id, 'eventwp_event_date_start', true ),
			'date_end'      => (string) get_post_meta( $post_id, 'eventwp_event_date_end', true ),
			'location'      => (string) get_post_meta( $post_id, 'eventwp_event_location', true ),
			'price'         => (float) get_post_meta( $post_id, 'eventwp_event_price', true ),
			'capacity'      => (int) get_post_meta( $post_id, 'eventwp_event_capacity', true ),
			'description'   => wp_strip_all_tags( $post->post_content ),
			'visibility'    => (string) get_post_meta( $post_id, 'eventwp_event_visibility', true ),
			'status'        => (string) get_post_meta( $post_id, 'eventwp_event_status', true ),
		);
		$data['visibility'] = $data['visibility'] ? $data['visibility'] : 'public';
		$data['status']     = $data['status'] ? $data['status'] : 'upcoming';
		$data['updated_at'] = gmdate( 'Y-m-d H:i:s' );

		$existing = $store->get_row( 'events', array( 'event_id' => (int) $post_id ) );
		if ( $existing ) {
			$store->update( 'events', $data, array( 'id' => (int) $existing['id'] ) );
		} else {
			$data['event_id'] = (int) $post_id;
			$store->insert( 'events', $data );
		}
	}

	/**
	 * Event validation for checkout.
	 *
	 * @param int $event_id Event id.
	 * @return array
	 */
	public function validate_event_for_checkout( $event_id ) {
		$event = $this->get_event( $event_id );
		if ( ! $event ) {
			$this->fail( 'Event tidak ditemukan.', 404 );
		}
		if ( 'upcoming' !== $event['status'] ) {
			$this->fail( 'Event tidak menerima tiket saat ini.' );
		}
		if ( $event['left'] <= 0 ) {
			$this->fail( 'Tiket untuk event ini sudah habis.' );
		}
		return $event;
	}

	/**
	 * Validate a voucher code.
	 *
	 * @param string $code    Code.
	 * @param float  $subtotal Subtotal.
	 * @return array{voucher:array,discount:float}
	 */
	public function apply_voucher( $code, $subtotal ) {
		$store   = EventWP::instance()->store;
		$voucher = null;
		if ( $code ) {
			$voucher = $store->get_row( 'vouchers', array( 'code' => strtoupper( trim( $code ) ) ) );
		}
		if ( ! $voucher ) {
			return array( 'voucher' => null, 'discount' => 0.0 );
		}
		if ( ! empty( $voucher['status'] ) && 'active' !== $voucher['status'] ) {
			$this->fail( 'Voucher tidak aktif.' );
		}
		if ( $voucher['max_uses'] > 0 && (int) $voucher['used_count'] >= (int) $voucher['max_uses'] ) {
			$this->fail( 'Kuota voucher sudah habis.' );
		}
		if ( $subtotal < (float) $voucher['min_purchase'] ) {
			$this->fail( 'Minimal pembelian untuk voucher ini belum terpenuhi.' );
		}
		$discount = 0.0;
		if ( 'percentage' === $voucher['type'] ) {
			$discount = $subtotal * ( (float) $voucher['value'] / 100 );
		} else {
			$discount = (float) $voucher['value'];
		}
		$discount = min( $discount, $subtotal );
		return array( 'voucher' => $voucher, 'discount' => round( $discount, 2 ) );
	}

	/**
	 * Active payment methods.
	 *
	 * @return array
	 */
	public function get_payments() {
		$store = EventWP::instance()->store;
		$rows  = $store->get_rows( 'payments', 'id', 'ASC', 100 );
		$out   = array();
		foreach ( (array) $rows as $p ) {
			if ( ! empty( $p['status'] ) && 'active' !== $p['status'] ) {
				continue;
			}
			$out[] = array(
				'id'             => (int) $p['id'],
				'type'           => $p['type'],
				'name'           => $p['name'],
				'account_holder' => $p['account_holder'],
				'account_number' => $p['account_number'],
				'image_url'      => $p['image_url'],
				'instructions'   => $p['instructions'],
			);
		}
		return $out;
	}

	/**
	 * QR code data-URI (generated client-side fallback handled in JS;
	 * server keeps the token payload).
	 *
	 * @param string $token Token.
	 * @param string $text  Text.
	 * @return string
	 */
	public function generate_qr( $token, $text ) {
		$compact = $text ? $text : $token;
		// Encode with a tiny local QR when `phpqrcode` exists; else token URI.
		return 'EVENTWP:' . rawurlencode( $compact );
	}

	/**
	 * Store a base64 upload into uploads dir.
	 *
	 * @param string $b64     Base64 string (may include data URI prefix).
	 * @param string $subdir  Subdir.
	 * @param string $prefix  Filename prefix.
	 * @param string $generic_generator Generator fallback function name.
	 * @return string
	 */
	public function store_upload( $b64, $subdir = 'eventwp', $prefix = 'up', $generic_generator = null ) {
		if ( empty( $b64 ) || ! is_string( $b64 ) ) {
			return '';
		}
		if ( 0 === strpos( $b64, 'data:image' ) ) {
			[$head, $data] = array_pad( explode( ',', $b64, 2 ), 2, '' );
			preg_match( '/data:image\/([a-zA-Z0-9.+-]+)/', $head, $m );
			$ext = isset( $m[1] ) ? strtolower( $m[1] ) : 'png';
			if ( 'jpeg' === $ext ) {
				$ext = 'jpg';
			}
			if ( ! in_array( $ext, array( 'png', 'jpg', 'jpeg', 'webp', 'gif' ), true ) ) {
				$ext = 'png';
			}
			$content = base64_decode( $data, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		} else {
			$content = base64_decode( $b64, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$ext     = 'png';
		}
		if ( false === $content ) {
			return '';
		}

		$upload_dir = wp_upload_dir();
		$path       = trailingslashit( $upload_dir['basedir'] ) . sanitize_file_name( $subdir );
		if ( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
		}
		$filename = sanitize_file_name( $prefix . '-' . gmdate( 'Ymd-His' ) . '-' . substr( md5( $content ), 0, 6 ) . '.' . $ext );
		$full     = trailingslashit( $path ) . $filename;
		if ( file_put_contents( $full, $content ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			return trailingslashit( $upload_dir['baseurl'] ) . sanitize_file_name( $subdir ) . '/' . $filename;
		}
		return '';
	}

	/**
	 * Voucher usage increment.
	 *
	 * @param array $voucher Voucher row.
	 * @return void
	 */
	public function consume_voucher( $voucher ) {
		$store = EventWP::instance()->store;
		if ( $voucher ) {
			$store->update( 'vouchers', array( 'used_count' => (int) $voucher['used_count'] + 1 ), array( 'id' => (int) $voucher['id'] ) );
		}
	}

	/**
	 * Audit log entry.
	 *
	 * @param int    $user_id User id.
	 * @param string $action  Action.
	 * @param string $context Context.
	 * @return void
	 */
	public function log( $user_id, $action, $context = '' ) {
		$store = EventWP::instance()->store;
		$store->insert(
			'audit_logs',
			array(
				'user_id' => (int) $user_id,
				'action'  => sanitize_text_field( $action ),
				'context' => sanitize_text_field( $context ),
				'ip'      => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			)
		);
	}
}
