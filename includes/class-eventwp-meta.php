<?php
/**
 * EventWP Meta — event detail fields (custom-fields + REST field).
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta boxes & REST meta exposure.
 */
class EventWP_Meta {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_boxes' ) );
		add_action( 'save_post_eventwp_event', array( $this, 'save' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_fields' ) );
	}

	/**
	 * Register meta box.
	 *
	 * @return void
	 */
	public function register_boxes() {
		add_meta_box(
			'eventwp_event_details',
			__( 'Detail Event — Active Nation', 'eventwp' ),
			array( $this, 'render' ),
			'eventwp_event',
			'normal',
			'high'
		);
	}

	/**
	 * Render meta box.
	 *
	 * @param WP_Post $post Post.
	 * @return void
	 */
	public function render( $post ) {
		wp_nonce_field( 'eventwp_event_meta', 'eventwp_event_meta_nonce' );
		$fields = array(
			'eventwp_event_status'       => array( 'Jadwal Status', 'select' ),
			'eventwp_event_date_start'   => array( 'Tanggal Mulai', 'datetime-local' ),
			'eventwp_event_date_end'     => array( 'Tanggal Selesai', 'datetime-local' ),
			'eventwp_event_location'     => array( 'Lokasi', 'text' ),
			'eventwp_event_price'        => array( 'Harga Tiket (Rp)', 'number' ),
			'eventwp_event_capacity'     => array( 'Kapasitas (kuota)', 'number' ),
			'eventwp_event_visibility'   => array( 'Visibilitas', 'select' ),
			'eventwp_event_primary_cat'  => array( 'Kategori Utama (ID)', 'number' ),
			'eventwp_event_instructor_id' => array( 'Instruktur (ID)', 'number' ),
			'eventwp_event_gallery'      => array( 'Galeri (URL dipisah koma)', 'textarea' ),
		);
		?>
		<style>
			.eventwp-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 20px; padding:6px 0; }
			.eventwp-meta-grid .field { display:flex; flex-direction:column; gap:4px; }
			.eventwp-meta-grid label { font-weight:600; }
			.eventwp-meta-grid input, .eventwp-meta-grid select, .eventwp-meta-grid textarea { width:100%; }
		</style>
		<div class="eventwp-meta-grid">
			<?php foreach ( $fields as $key => $def ) : ?>
				<?php
				$val  = get_post_meta( $post->ID, $key, true );
				[$label, $type] = $def;
				?>
				<div class="field">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					<?php if ( 'select' === $type ) : ?>
						<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>">
							<?php if ( 'eventwp_event_status' === $key ) : ?>
								<?php foreach ( array( 'upcoming', 'ongoing', 'completed', 'draft' ) as $opt ) : ?>
									<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $val, $opt ); ?>><?php echo esc_html( ucfirst( $opt ) ); ?></option>
								<?php endforeach; ?>
							<?php else : ?>
								<?php foreach ( array( 'public', 'hidden' ) as $opt ) : ?>
									<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $val, $opt ); ?>><?php echo esc_html( ucfirst( $opt ) ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					<?php elseif ( 'textarea' === $type ) : ?>
						<textarea name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" rows="3"><?php echo esc_textarea( (string) $val ); ?></textarea>
					<?php elseif ( 'datetime-local' === $type ) : ?>
						<input type="datetime-local" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $this->to_local( $val ) ); ?>" />
					<?php else : ?>
						<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( (string) $val ); ?>" step="any" />
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<p class="description"><?php esc_html_e( 'Event juga otomatis disinkronkan ke tabel eventwp_events di database.', 'eventwp' ); ?></p>
		<?php
	}

	/**
	 * Save meta box.
	 *
	 * @param int $post_id Post id.
	 * @return void
	 */
	public function save( $post_id ) {
		if ( ! isset( $_POST['eventwp_event_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['eventwp_event_meta_nonce'] ), 'eventwp_event_meta' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$keys = array(
			'eventwp_event_status',
			'eventwp_event_date_start',
			'eventwp_event_date_end',
			'eventwp_event_location',
			'eventwp_event_price',
			'eventwp_event_capacity',
			'eventwp_event_visibility',
			'eventwp_event_primary_cat',
			'eventwp_event_instructor_id',
			'eventwp_event_gallery',
		);

		foreach ( $keys as $_key ) {
			if ( isset( $_POST[ $_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$val = wp_unslash( $_POST[ $_key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput
				if ( in_array( $_key, array( 'eventwp_event_status', 'eventwp_event_visibility' ), true ) ) {
					$val = sanitize_text_field( $val );
				} elseif ( in_array( $_key, array( 'eventwp_event_price', 'eventwp_event_capacity', 'eventwp_event_primary_cat', 'eventwp_event_instructor_id' ), true ) ) {
					$val = (float) $val;
				} elseif ( 'eventwp_event_date_start' === $_key || 'eventwp_event_date_end' === $_key ) {
					$val = $this->from_local( $val );
				} elseif ( 'eventwp_event_gallery' === $_key ) {
					$val = sanitize_textarea_field( $val );
				} else {
					$val = sanitize_text_field( $val );
				}
				update_post_meta( $post_id, $_key, $val );
			}
		}

		// Sync to DB (only on publish).
		if ( 'publish' === get_post_status( $post_id ) ) {
			EventWP::instance()->api->sync_event_to_db( $post_id );
		}
	}

	/**
	 * Expose meta in REST.
	 *
	 * @return void
	 */
	public function register_rest_fields() {
		register_rest_field(
			'eventwp_event',
			'eventwp_meta',
			array(
				'get_callback'    => array( $this, 'rest_meta' ),
				'update_callback' => function ( $value, $post ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $k => $v ) {
							update_post_meta( $post->ID, sanitize_key( $k ), $v );
						}
						EventWP::instance()->api->sync_event_to_db( $post->ID );
					}
					return true;
				},
				'schema'          => array(
					'type'       => 'object',
					'properties' => array(
						'status' => array( 'type' => 'string' ),
						'price'  => array( 'type' => 'number' ),
					),
				),
			)
		);
	}

	/**
	 * REST meta getter.
	 *
	 * @param array $object Post object array.
	 * @return array
	 */
	public function rest_meta( $object ) {
		$post_id = isset( $object['id'] ) ? (int) $object['id'] : 0;
		$keys    = array(
			'eventwp_event_status',
			'eventwp_event_date_start',
			'eventwp_event_date_end',
			'eventwp_event_location',
			'eventwp_event_price',
			'eventwp_event_capacity',
			'eventwp_event_visibility',
			'eventwp_event_primary_cat',
			'eventwp_event_instructor_id',
			'eventwp_event_gallery',
		);
		$out = array();
		foreach ( $keys as $k ) {
			$out[ str_replace( 'eventwp_event_', '', $k ) ] = get_post_meta( $post_id, $k, true );
		}
		return $out;
	}

	/**
	 * Convert stored datetime to datetime-local input value.
	 *
	 * @param string $value Stored.
	 * @return string
	 */
	private function to_local( $value ) {
		$ts = strtotime( (string) $value );
		return $ts ? gmdate( 'Y-m-d\TH:i', $ts ) : '';
	}

	/**
	 * Convert datetime-local input to stored (UTC).
	 *
	 * @param string $value Input.
	 * @return string
	 */
	private function from_local( $value ) {
		$ts = strtotime( (string) $value );
		return $ts ? gmdate( 'Y-m-d H:i:s', $ts ) : '';
	}
}
