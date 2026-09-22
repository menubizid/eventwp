<?php
/**
 * EventWP Helpers — shared utility methods.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helpers.
 */
class EventWP_Helpers {

	/**
	 * Sanitize an incoming Shopify-style payload value (strings/numbers/bools/arrays).
	 *
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	public function sanitize_value( $value ) {
		if ( is_array( $value ) ) {
			$out = array();
			foreach ( $value as $k => $v ) {
				$k = is_string( $k ) ? sanitize_key( $k ) : $k;
				if ( is_scalar( $v ) ) {
					$out[ $k ] = $this->sanitize_value( $v );
				} else {
					$out[ $k ] = $this->sanitize_value( $v );
				}
			}
			return $out;
		}
		if ( is_int( $value ) || is_float( $value ) ) {
			return $value;
		}
		if ( is_bool( $value ) ) {
			return (bool) $value;
		}
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Recursive sanitization of a payload array.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public function sanitize_payload( $data ) {
		$out = array();
		foreach ( (array) $data as $key => $value ) {
			$key = sanitize_key( (string) $key );
			if ( is_array( $value ) ) {
				$out[ $key ] = $this->sanitize_payload( $value );
			} else {
				$out[ $key ] = $this->sanitize_value( $value );
			}
		}
		return $out;
	}

	/**
	 * Ordered by precedence: id, event_id, {entity}_id, id-less table.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $raw   Payload.
	 * @param string $entity Entity key for `{entity}_id`.
	 * @return array{0: array, 1: int|null, 2: string}
	 */
	public function find_record( $table, $raw, $entity = '' ) {
		$store = EventWP::instance()->store;
		$id    = null;
		$key   = 'id';

		if ( isset( $raw['id'] ) && $raw['id'] ) {
			$col = in_array( $table, array( 'users', 'payments', 'vouchers', 'audit_logs' ), true ) ? 'id' : 'event_id';
			$row = $store->get_row( $table, array( $col => (int) $raw['id'] ) );
		} elseif ( isset( $raw['event_id'] ) && $raw['event_id'] ) {
			$row = $store->get_row( $table, array( 'event_id' => (int) $raw['event_id'] ) );
			$key = 'event_id';
		} elseif ( $entity && isset( $raw[ $entity . '_id' ] ) && $raw[ $entity . '_id' ] ) {
			$row = $store->get_row( $table, array( $entity . '_id' => (int) $raw[ $entity . '_id' ] ) );
			$key = $entity . '_id';
		} else {
			$row = $store->get_row( $table, array() );
		}

		if ( $row ) {
			$id = (int) $row['id'];
		}
		return array( $row ? (array) $row : array(), $id, $key );
	}

	/**
	 * Col prefix whitelist to avoid dynamic-key footguns.
	 *
	 * @param string $table Table name.
	 * @param string $entity Entity key.
	 * @return array<string,string[]>
	 */
	public function field_map( $table, $entity = 'event' ) {
		$maps = array(
			'event_categories' => array(
				'whitelist' => array( 'id', 'name', 'slug', 'description', 'color', 'icon', 'form_template_id', 'status', 'created_at', 'updated_at' ),
				'prefix'    => 'cat_',
				'entity'    => 'category',
			),
			'event_reviews'    => array(
				'whitelist' => array( 'id', 'event_id', 'ticket_id', 'user_id', 'author', 'rating', 'comment', 'created_at' ),
				'prefix'    => 'rev_',
				'entity'    => 'review',
			),
			'event_galleries'  => array(
				'whitelist' => array( 'id', 'event_id', 'user_id', 'image_url', 'caption', 'created_at' ),
				'prefix'    => 'gal_',
				'entity'    => 'gallery',
			),
			'form_responses'   => array(
				'whitelist' => array( 'id', 'event_id', 'ticket_id', 'user_id', 'template_id', 'response_data' ),
				'prefix'    => 'resp_',
				'entity'    => 'response',
			),
			'orders'           => array(
				'whitelist' => array( 'id', 'event_id', 'user_id', 'qty', 'amount', 'subtotal', 'discount', 'total', 'voucher_code', 'payment_method', 'billing_name', 'billing_phone', 'payment_proof', 'status', 'created_at', 'updated_at' ),
				'prefix'    => 'ord_',
				'entity'    => 'order',
			),
			'tickets'          => array(
				'whitelist' => array( 'id', 'event_id', 'user_id', 'order_id', 'ticket_code', 'qr_token', 'status', 'checkin_code', 'checked_in_at' ),
				'prefix'    => 'tkt_',
				'entity'    => 'ticket',
			),
			'users'            => array(
				'whitelist' => array( 'id', 'role', 'name', 'email', 'password', 'phone', 'city', 'avatar', 'status' ),
				'prefix'    => 'u_',
				'entity'    => 'user',
			),
			'payments'         => array(
				'whitelist' => array( 'id', 'type', 'name', 'account_holder', 'account_number', 'image_url', 'instructions', 'status' ),
				'prefix'    => 'pay_',
				'entity'    => 'payment',
			),
			'vouchers'         => array(
				'whitelist' => array( 'id', 'code', 'type', 'value', 'min_purchase', 'max_uses', 'used_count', 'status' ),
				'prefix'    => 'vouch_',
				'entity'    => 'voucher',
			),
			'event_response'   => array(
				'entity' => 'response',
			),
		);
		return isset( $maps[ $table ] ) ? $maps[ $table ] : array(
			'whitelist' => array( '*' ),
			'prefix'    => 'ev_',
			'entity'    => $entity,
		);
	}

	/**
	 * Generate a random ticket/Qr token.
	 *
	 * @return string
	 */
	public function random_token() {
		if ( function_exists( 'random_bytes' ) ) {
			return strtoupper( bin2hex( random_bytes( 8 ) ) );
		}
		return strtoupper( wp_generate_password( 16, false, false ) );
	}

	/**
	 * Standard API response / drain payload.
	 *
	 * @param boolean $success Whether successful.
	 * @param string  $message Message.
	 * @param mixed   $data    Data payload.
	 * @return array
	 */
	public function response( $success, $message, $data = null ) {
		return array(
			'success'   => (bool) $success,
			'message'   => (string) $message,
			'data'      => $data,
			'timestamp' => gmdate( 'c' ),
		);
	}

	/**
	 * Human-readable date/time.
	 *
	 * @param string $value Datetime string.
	 * @return string
	 */
	public function format_datetime( $value ) {
		if ( ! $value ) {
			return '—';
		}
		$ts = strtotime( (string) $value );
		if ( ! $ts ) {
			return esc_html( (string) $value );
		}
		return esc_html( date_i18n( 'j M Y • H:i', $ts ) );
	}

	/**
	 * Money formatting (IDR aware).
	 *
	 * @param float|int|string $value Amount.
	 * @return string
	 */
	public function format_money( $value ) {
		$value = (float) $value;
		if ( $value <= 0 ) {
			return 'GRATIS';
		}
		return 'Rp ' . number_format( $value, 0, ',', '.' );
	}

	/**
	 * Enqueue frontend app/block assets on the fly (shortcode/block render).
	 *
	 * @param boolean $blocks_only Only block CSS (skip app shell JS).
	 * @return void
	 */
	public function enqueue_app_assets( $blocks_only = false ) {
		if ( ! wp_style_is( 'eventwp-fontawesome', 'registered' ) ) {
			wp_register_style( 'eventwp-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
		}
		wp_enqueue_style( 'eventwp-frontend' );
		wp_enqueue_style( 'eventwp-app' );
		wp_enqueue_style( 'eventwp-fontawesome' );
		if ( ! $blocks_only ) {
			wp_enqueue_script( 'eventwp-vendor' );
			wp_enqueue_script( 'eventwp-app' );
		}
	}

	/**
	 * Sanitize an inline SVG (whitelist tags + attributes, preserving paths).
	 *
	 * @param string $svg Raw SVG markup.
	 * @return string
	 */
	public function kses_svg( $svg ) {
		$allowed = array(
			'svg'      => array(
				'class'           => true,
				'viewBox'         => true,
				'width'           => true,
				'height'          => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'aria-hidden'     => true,
				'xmlns'           => true,
			),
			'path'     => array(
				'd'     => true,
				'fill'  => true,
				'stroke'=> true,
			),
			'circle'   => array(
				'cx'     => true,
				'cy'     => true,
				'r'      => true,
				'fill'   => true,
				'stroke' => true,
			),
			'rect'     => array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
				'fill'   => true,
				'stroke' => true,
				'opacity'=> true,
			),
			'polyline' => array(
				'points' => true,
				'fill'   => true,
				'stroke' => true,
			),
			'polygon'  => array(
				'points' => true,
				'fill'   => true,
				'stroke' => true,
			),
			'line'     => array(
				'x1'     => true,
				'y1'     => true,
				'x2'     => true,
				'y2'     => true,
				'stroke' => true,
			),
		);
		return wp_kses( $svg, $allowed );
	}

	/**
	 * Multi-byte safe first character.
	 *
	 * @param string $value String.
	 * @param int    $offset Offset.
	 * @return string
	 */
	public function char_at( $value, $offset = 0 ) {
		$value = (string) $value;
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, $offset, 1 );
		}
		return substr( $value, $offset, 1 );
	}
}


