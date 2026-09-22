<?php
/**
 * EventWP Taxonomy — eventwp_category hierarchical taxonomy for events.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomy registration.
 */
class EventWP_Taxonomy {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ), 6 );
		add_action( 'eventwp_category_add_form_fields', array( $this, 'add_fields' ) );
		add_action( 'eventwp_category_edit_form_fields', array( $this, 'edit_fields' ) );
		add_action( 'created_eventwp_category', array( $this, 'save_fields' ) );
		add_action( 'edited_eventwp_category', array( $this, 'save_fields' ) );
	}

	/**
	 * Register taxonomy.
	 *
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'          => _x( 'Kategori Event', 'taxonomy general name', 'eventwp' ),
			'singular_name' => _x( 'Kategori', 'taxonomy singular name', 'eventwp' ),
			'search_items'  => __( 'Cari Kategori', 'eventwp' ),
			'all_items'     => __( 'Semua Kategori', 'eventwp' ),
			'edit_item'     => __( 'Edit Kategori', 'eventwp' ),
			'update_item'   => __( 'Perbarui Kategori', 'eventwp' ),
			'add_new_item'  => __( 'Tambah Kategori Baru', 'eventwp' ),
			'new_item_name' => __( 'Nama Kategori Baru', 'eventwp' ),
			'menu_name'     => __( 'Kategori', 'eventwp' ),
		);

		register_taxonomy(
			'eventwp_category',
			array( 'eventwp_event' ),
			array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => 'eventwp',
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => 'event-category',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Add-form extra fields.
	 *
	 * @return void
	 */
	public function add_fields() {
		$templates = $this->template_options();
		?>
		<div class="form-field term-color-wrap">
			<label for="eventwp_cat_color"><?php esc_html_e( 'Warna Kategori', 'eventwp' ); ?></label>
			<input type="color" name="eventwp_cat_color" id="eventwp_cat_color" value="#0a84ff" />
			<p><?php esc_html_e( 'Digunakan sebagai aksen visual event.', 'eventwp' ); ?></p>
		</div>
		<div class="form-field term-icon-wrap">
			<label for="eventwp_cat_icon"><?php esc_html_e( 'Ikon (FontAwesome class)', 'eventwp' ); ?></label>
			<input type="text" name="eventwp_cat_icon" id="eventwp_cat_icon" value="fa-bolt" />
			<p><?php esc_html_e( 'Contoh: fa-basketball, fa-spa, fa-person-running.', 'eventwp' ); ?></p>
		</div>
		<div class="form-field term-template-wrap">
			<label for="eventwp_cat_template"><?php esc_html_e( 'Template Form Event (opsional)', 'eventwp' ); ?></label>
			<select name="eventwp_cat_template" id="eventwp_cat_template">
				<option value="0"><?php esc_html_e( '— Tanpa form —', 'eventwp' ); ?></option>
				<?php foreach ( $templates as $tid => $tname ) : ?>
					<option value="<?php echo esc_attr( $tid ); ?>"><?php echo esc_html( $tname ); ?></option>
				<?php endforeach; ?>
			</select>
			<p><?php esc_html_e( 'Pelanggan wajib mengisi form ini setelah pembelian (misal: riwayat cedera, ukuran baju).', 'eventwp' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Edit-form extra fields.
	 *
	 * @param WP_Term $term Term.
	 * @return void
	 */
	public function edit_fields( $term ) {
		$color     = get_term_meta( $term->term_id, 'eventwp_cat_color', true );
		$icon      = get_term_meta( $term->term_id, 'eventwp_cat_icon', true );
		$template  = get_term_meta( $term->term_id, 'eventwp_cat_template', true );
		$templates = $this->template_options();
		?>
		<tr class="form-field term-color-wrap">
			<th scope="row"><label for="eventwp_cat_color"><?php esc_html_e( 'Warna Kategori', 'eventwp' ); ?></label></th>
			<td><input type="color" name="eventwp_cat_color" id="eventwp_cat_color" value="<?php echo esc_attr( $color ? $color : '#0a84ff' ); ?>" /></td>
		</tr>
		<tr class="form-field term-icon-wrap">
			<th scope="row"><label for="eventwp_cat_icon"><?php esc_html_e( 'Ikon (FontAwesome class)', 'eventwp' ); ?></label></th>
			<td><input type="text" name="eventwp_cat_icon" id="eventwp_cat_icon" value="<?php echo esc_attr( $icon ? $icon : 'fa-bolt' ); ?>" /></td>
		</tr>
		<tr class="form-field term-template-wrap">
			<th scope="row"><label for="eventwp_cat_template"><?php esc_html_e( 'Template Form Event', 'eventwp' ); ?></label></th>
			<td>
				<select name="eventwp_cat_template" id="eventwp_cat_template">
					<option value="0"><?php esc_html_e( '— Tanpa form —', 'eventwp' ); ?></option>
					<?php foreach ( $templates as $tid => $tname ) : ?>
						<option value="<?php echo esc_attr( $tid ); ?>" <?php selected( (string) $template, (string) $tid ); ?>><?php echo esc_html( $tname ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Persist fields.
	 *
	 * @param int $term_id Term id.
	 * @return void
	 */
	public function save_fields( $term_id ) {
		if ( isset( $_POST['eventwp_cat_color'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_term_meta( $term_id, 'eventwp_cat_color', sanitize_hex_color( wp_unslash( $_POST['eventwp_cat_color'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( isset( $_POST['eventwp_cat_icon'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_term_meta( $term_id, 'eventwp_cat_icon', sanitize_text_field( wp_unslash( $_POST['eventwp_cat_icon'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( isset( $_POST['eventwp_cat_template'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_term_meta( $term_id, 'eventwp_cat_template', absint( wp_unslash( $_POST['eventwp_cat_template'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}

	/**
	 * Form template options for the select.
	 *
	 * @return array<int,string>
	 */
	private function template_options() {
		$store = EventWP::instance()->store;
		$rows  = $store->get_rows( 'form_templates', 'id', 'DESC', 100 );
		$out   = array();
		foreach ( (array) $rows as $row ) {
			$out[ (int) $row['id'] ] = $row['name'];
		}
		return $out;
	}
}
