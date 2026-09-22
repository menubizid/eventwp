<?php
/**
 * EventWP Blocks — ready-to-use Gutenberg blocks.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block registration (dynamic server-rendered blocks).
 */
class EventWP_Blocks {

	/**
	 * Block list.
	 *
	 * @var array<int, array<string,mixed>>
	 */
	protected $blocks = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block_types' ), 10 );
	}

	/**
	 * Register all block types.
	 *
	 * @return void
	 */
	public function register_block_types() {
		register_block_type(
			'eventwp/events',
			array(
				'api_version'     => 2,
				'title'           => __( 'EventWP — Daftar Event', 'eventwp' ),
				'description'     => __( 'Grid event up-to-date dengan filter kategori.', 'eventwp' ),
				'category'        => 'widgets',
				'icon'            => 'tickets-alt',
				'keywords'        => array( 'event', 'sport', 'olga', 'tiket' ),
				'attributes'      => array(
					'limit'    => array( 'type' => 'number', 'default' => 6 ),
					'category' => array( 'type' => 'string', 'default' => '' ),
					'view'     => array( 'type' => 'string', 'default' => 'grid' ),
					'filter'   => array( 'type' => 'boolean', 'default' => true ),
					'title'    => array( 'type' => 'string', 'default' => '' ),
				),
				'supports'        => array(
					'align' => array( 'wide', 'full' ),
					'html'  => false,
				),
				'textdomain'      => 'eventwp',
				'render_callback' => array( $this, 'render_events' ),
				'editor_script'   => 'eventwp-blocks-js',
				'editor_style'    => 'eventwp-blocks-css',
			)
		);

		register_block_type(
			'eventwp/event-details',
			array(
				'api_version'     => 2,
				'title'           => __( 'EventWP — Detail Event', 'eventwp' ),
				'description'     => __( 'Detail event dengan tombol beli tiket.', 'eventwp' ),
				'category'        => 'widgets',
				'icon'            => 'megaphone',
				'keywords'        => array( 'event', 'detail', 'sport' ),
				'attributes'      => array(
					'eventId' => array( 'type' => 'number', 'default' => 0 ),
				),
				'supports'        => array(
					'align' => array( 'wide', 'full' ),
					'html'  => false,
				),
				'textdomain'      => 'eventwp',
				'render_callback' => array( $this, 'render_event_details' ),
				'editor_script'   => 'eventwp-blocks-js',
				'editor_style'    => 'eventwp-blocks-css',
			)
		);

		register_block_type(
			'eventwp/app',
			array(
				'api_version'     => 2,
				'title'           => __( 'EventWP — App Sport Event', 'eventwp' ),
				'description'     => __( 'Aplikasi web mobile full (login, checkout, tiket QR).', 'eventwp' ),
				'category'        => 'widgets',
				'icon'            => 'smartphone',
				'keywords'        => array( 'app', 'event', 'mobile', 'sport' ),
				'attributes'      => array(
					'height' => array( 'type' => 'string', 'default' => '85vh' ),
				),
				'supports'        => array(
					'align' => array( 'wide', 'full' ),
					'html'  => false,
				),
				'textdomain'      => 'eventwp',
				'render_callback' => array( $this, 'render_app' ),
				'editor_script'   => 'eventwp-blocks-js',
			)
		);
	}

	/**
	 * Render events grid block.
	 *
	 * @param array $attrs Attributes.
	 * @return string
	 */
	public function render_events( $attrs ) {
		$attrs  = wp_parse_args(
			$attrs,
			array(
				'limit'    => 6,
				'category' => '',
				'view'     => 'grid',
				'filter'   => true,
				'title'    => '',
			)
		);
		$events = EventWP::instance()->api->list_events_public();
		if ( $attrs['category'] ) {
			$events = array_values(
				array_filter(
					$events,
					function ( $e ) use ( $attrs ) {
						return (string) $e['category_slug'] === (string) $attrs['category'] || (string) $e['category_id'] === (string) $attrs['category'];
					}
				)
			);
		}
		$events = array_slice( $events, 0, (int) $attrs['limit'] );

		ob_start();
		EventWP::instance()->helpers->enqueue_app_assets( true );
		?>
		<div class="eventwp-block" data-eventwp-block="events">
			<?php if ( $attrs['title'] ) : ?>
				<h2 class="eventwp-block__title"><?php echo esc_html( $attrs['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( $attrs['filter'] ) : ?>
				<div class="eventwp-block-filter" role="tablist" aria-label="<?php esc_attr_e( 'Filter kategori', 'eventwp' ); ?>">
					<button type="button" class="eventwp-chip is-active" data-filter="all"><?php esc_html_e( 'Semua', 'eventwp' ); ?></button>
					<?php
					$cats = EventWP::instance()->api->get_categories();
					foreach ( $cats as $c ) :
						?>
						<button type="button" class="eventwp-chip" data-filter="<?php echo esc_attr( $c['slug'] ); ?>"><?php echo esc_html( $c['name'] ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="eventwp-grid">
				<?php foreach ( $events as $e ) : ?>
					<article class="eventwp-card" data-category="<?php echo esc_attr( $e['category_slug'] ); ?>">
						<a class="eventwp-card__media" href="<?php echo esc_url( $e['permalink'] ); ?>">
							<?php if ( $e['banner'] ) : ?>
								<img src="<?php echo esc_url( $e['banner'] ); ?>" alt="<?php echo esc_attr( $e['title'] ); ?>" loading="lazy" width="640" height="400" />
							<?php else : ?>
								<div class="eventwp-card__placeholder" style="--cat-color:<?php echo esc_attr( $e['category_color'] ); ?>"><i class="fa-solid <?php echo esc_attr( $e['category_icon'] ); ?>" aria-hidden="true"></i></div>
							<?php endif; ?>
							<span class="eventwp-card__chip" style="--cat-color:<?php echo esc_attr( $e['category_color'] ); ?>"><i class="fa-solid <?php echo esc_attr( $e['category_icon'] ); ?>" aria-hidden="true"></i> <?php echo esc_html( $e['category_name'] ); ?></span>
						</a>
						<div class="eventwp-card__body">
							<h3 class="eventwp-card__title"><a href="<?php echo esc_url( $e['permalink'] ); ?>"><?php echo esc_html( $e['title'] ); ?></a></h3>
							<p class="eventwp-card__meta"><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo esc_html( $e['date_start_fmt'] ); ?></p>
							<p class="eventwp-card__meta"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo esc_html( $e['location'] ); ?></p>
							<div class="eventwp-card__foot">
								<span class="eventwp-card__price"><?php echo esc_html( $e['price_fmt'] ); ?></span>
								<a class="eventwp-btn eventwp-btn--primary" href="<?php echo esc_url( $e['permalink'] ); ?>"><?php esc_html_e( 'Beli Tiket', 'eventwp' ); ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
				<?php if ( empty( $events ) ) : ?>
					<p class="eventwp-empty"><?php esc_html_e( 'Belum ada event tersedia.', 'eventwp' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render event details block.
	 *
	 * @param array $attrs Attributes.
	 * @return string
	 */
	public function render_event_details( $attrs ) {
		$event_id = isset( $attrs['eventId'] ) ? (int) $attrs['eventId'] : 0;
		$event    = $event_id ? EventWP::instance()->api->get_event( $event_id ) : array();
		if ( ! $event ) {
			return '<p class="eventwp-empty">' . esc_html__( 'Pilih event pada pengaturan blok, atau buka dari halaman event.', 'eventwp' ) . '</p>';
		}
		ob_start();
		EventWP::instance()->helpers->enqueue_app_assets( true );
		?>
		<div class="eventwp-detail" data-eventwp-detail data-event-id="<?php echo esc_attr( $event['id'] ); ?>">
			<div class="eventwp-detail__hero">
				<?php if ( $event['banner'] ) : ?>
					<img src="<?php echo esc_url( $event['banner'] ); ?>" alt="<?php echo esc_attr( $event['title'] ); ?>" />
				<?php else : ?>
					<div class="eventwp-card__placeholder" style="--cat-color:<?php echo esc_attr( $event['category_color'] ); ?>"><i class="fa-solid <?php echo esc_attr( $event['category_icon'] ); ?>" aria-hidden="true"></i></div>
				<?php endif; ?>
			</div>
			<h2 class="eventwp-detail__title"><?php echo esc_html( $event['title'] ); ?></h2>
			<div class="eventwp-detail__meta">
				<span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?php echo esc_html( $event['date_start_fmt'] ); ?></span>
				<span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo esc_html( $event['location'] ); ?></span>
				<span><i class="fa-solid fa-user-tie" aria-hidden="true"></i> <?php echo esc_html( $event['instructor_name'] ); ?></span>
			</div>
			<p class="eventwp-detail__desc"><?php echo esc_html( $event['description'] ); ?></p>
			<div class="eventwp-detail__foot">
				<span class="eventwp-card__price"><?php echo esc_html( $event['price_fmt'] ); ?></span>
				<a class="eventwp-btn eventwp-btn--primary" href="<?php echo esc_url( $event['permalink'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Pesan Tiket Sekarang', 'eventwp' ); ?></a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the full app block.
	 *
	 * @param array $attrs Attributes.
	 * @return string
	 */
	public function render_app( $attrs ) {
		$attrs  = wp_parse_args( $attrs, array( 'height' => '85vh' ) );
		$output = EventWP::instance()->shortcodes->render_app( array() );
		return $output;
	}
}
