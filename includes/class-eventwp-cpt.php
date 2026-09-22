<?php
/**
 * EventWP CPT — eventwp_event custom post type.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Custom Post Type registration.
 */
class EventWP_CPT {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ), 5 );
		add_filter( 'manage_eventwp_event_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_eventwp_event_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_filter( 'manage_edit-eventwp_event_sortable_columns', array( $this, 'sortable_columns' ) );
		// Keep the mirror table in sync for native CPT edits (published events).
		add_action( 'save_post_eventwp_event', array( $this, 'auto_sync' ), 20, 2 );
	}

	/**
	 * Auto-sync CPT edits (published event) into the mirror table.
	 *
	 * @param int     $post_id Post id.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function auto_sync( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( 'publish' === $post->post_status ) {
			EventWP::instance()->api->sync_event_to_db( $post_id );
		}
	}

	/**
	 * Register the CPT.
	 *
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'                  => _x( 'Sport Events', 'Post type general name', 'eventwp' ),
			'singular_name'         => _x( 'Event', 'Post type singular name', 'eventwp' ),
			'menu_name'             => _x( 'Sport Events', 'Admin menu text', 'eventwp' ),
			'name_admin_bar'        => _x( 'Event', 'Admin bar name', 'eventwp' ),
			'add_new'               => __( 'Tambah Event', 'eventwp' ),
			'add_new_item'          => __( 'Tambah Event Baru', 'eventwp' ),
			'edit_item'             => __( 'Edit Event', 'eventwp' ),
			'new_item'              => __( 'Event Baru', 'eventwp' ),
			'view_item'             => __( 'Lihat Event', 'eventwp' ),
			'search_items'          => __( 'Cari Event', 'eventwp' ),
			'not_found'             => __( 'Tidak ada event ditemukan', 'eventwp' ),
			'not_found_in_trash'    => __( 'Tidak ada event di Trash', 'eventwp' ),
			'all_items'             => __( 'Semua Event', 'eventwp' ),
			'featured_image'        => __( 'Banner Event', 'eventwp' ),
			'set_featured_image'    => __( 'Set banner event', 'eventwp' ),
			'remove_featured_image' => __( 'Hapus banner', 'eventwp' ),
			'use_featured_image'    => __( 'Gunakan sebagai banner', 'eventwp' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'eventwp',
			'show_in_nav_menus'  => true,
			'show_in_rest'       => true,
			'rest_base'          => 'eventwp_events',
			'menu_icon'          => 'dashicons-tickets-alt',
			'menu_position'      => 5,
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'has_archive'        => true,
			'rewrite'            => array(
				'slug'       => 'event',
				'with_front' => false,
			),
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		);

		register_post_type( 'eventwp_event', $args );
	}

	/**
	 * Admin list columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['eventwp_date']   = __( 'Jadwal', 'eventwp' );
				$new['eventwp_price']  = __( 'Harga', 'eventwp' );
				$new['eventwp_status'] = __( 'Status', 'eventwp' );
			}
		}
		return $new;
	}

	/**
	 * Column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'eventwp_date':
				$start = get_post_meta( $post_id, 'eventwp_event_date_start', true );
				echo esc_html( $start ? EventWP::instance()->helpers->format_datetime( $start ) : '—' );
				break;
			case 'eventwp_price':
				$price = get_post_meta( $post_id, 'eventwp_event_price', true );
				echo esc_html( $price ? EventWP::instance()->helpers->format_money( $price ) : 'GRATIS' );
				break;
			case 'eventwp_status':
				$status = get_post_meta( $post_id, 'eventwp_event_status', true );
				echo '<span class="eventwp-badge eventwp-badge--' . esc_attr( sanitize_html_class( $status ? $status : 'upcoming' ) ) . '">' . esc_html( ucfirst( $status ? $status : 'upcoming' ) ) . '</span>';
				break;
		}
	}

	/**
	 * Sortable columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function sortable_columns( $columns ) {
		$columns['eventwp_date'] = 'eventwp_date';
		return $columns;
	}
}
