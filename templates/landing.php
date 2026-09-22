<?php
/**
 * Landing page template for Active Nation (Sport Event).
 *
 * Rendered via [eventwp_landing] shortcode.
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

$ewp = EventWP::instance();
$api = $ewp->api;

/* Data */
$ewp_brand = 'Active Nation';
$site_row  = $ewp->store->get_row( 'settings', array( 'setting_key' => 'brand_name' ) );
if ( $site_row && $site_row['setting_value'] ) {
	$ewp_brand = $site_row['setting_value'];
}

$ewp_events = $api->list_events_public( '', '' );
$ewp_cats   = $api->get_categories();

$ewp_app_url = get_permalink( (int) get_option( 'eventwp_app_page' ) );
if ( ! $ewp_app_url ) {
	$ewp_app_url = '#eventwp-app';
}

/* Icon set */
$ewp_icons = array(
	'zumba'    => '<path d="M12 3v5.4M12 3l-3 5.4M12 3l3 5.4M9 9.5l-2.8 5m-3.2 1 1.8-3.1M6.2 14.5 8 15.5m7-6-2.8 5m3.2 1 1.8-3.1M8 18h8l-1.4 2.5H9.4L8 18Z"/><circle cx="12" cy="20.5" r="1.4"/>',
	'basket'  => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18M4.6 6.5h14.8M4.6 17.5h14.8M3 9.3h18M3 14.7h18"/>',
	'padel'   => '<circle cx="13.5" cy="13.5" r="6.5"/><path d="M6.5 3 12 8.5M11 21l4.5-4.5"/>',
	'yoga'    => '<path d="M12 3c3 4.6 4.2 8 4.6 11.6 0 .2 0 0 0 0M8 21c2.7-5.6 3.6-9 4-13M12 3c-2.6 4-3.7 7.6-4 11M12 3v6.2"/>',
	'run'     => '<circle cx="14" cy="6" r="2.2"/><path d="M5 21l5.2-4.4M14 8l3.4-2.2M15.5 6.4 13 11M13 11l-3.5 3.2M9.5 14.2 7 21M16.4 17l1.6 4"/>',
	'check'   => '<path d="M20 6 9 17l-5-5"/>',
	'ticket'  => '<path d="M3 8a2 2 0 0 0 2-2h12a2 2 0 0 0 2 2 2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2 2 2 0 0 0-2 2H5a2 2 0 0 0-2-2 2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4v-2a2 2 0 0 1 2-2Z"/><path d="M13 6v2m0 4v2m0 4v2"/>',
	'qr'      => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><path d="M14 14h3v3h-3zM17 17h4v4h-4z"/>',
	'phone'   => '<rect x="6" y="2.5" width="12" height="19" rx="2.5"/><path d="M10 5h4M12 18.5h.01"/>',
	'heart'   => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
	'bolt'    => '<path d="M13 2 4.5 13.5H11l-1 8.5L18.5 10H12l1-8Z"/>',
	'shield'  => '<path d="M12 2.5 4.5 5.5v6c0 4.6 3.2 8 7.5 9.6 4.3-1.6 7.5-5 7.5-9.6v-6L12 2.5Z"/>',
	'map'     => '<path d="M12 21s-7-5.1-7-11a7 7 0 0 1 14 0c0 5.9-7 11-7 11Z"/><circle cx="12" cy="10" r="2.6"/>',
	'grid'    => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
	'users'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 5a3.5 3.5 0 0 1 0 6.8M21.5 20a6.5 6.5 0 0 0-5-6.3"/>',
	'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
	'arrow'   => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
	'play'    => '<path d="M8 5.5v13l11-6.5-11-6.5Z"/>',
	'star'    => '<path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.9-5.2-2.8-5.2 2.8 1-5.9L3.5 9.7l5.9-.9L12 3.5Z"/>',
	'wa'      => '<path d="M12 3a9 9 0 0 0-7.8 13.4L3 21l4.8-1.3A9 9 0 1 0 12 3Z"/><path d="M8.5 8.5c.5 2.5 2.5 4.5 5 5"/>',
	'plus'    => '<path d="M12 5v14M5 12h14"/>',
	'up'      => '<path d="M12 19V5m-6 6 6-6 6 6"/>',
	'fb'      => '<path d="M14 8h2.5V4.5H14A4.5 4.5 0 0 0 9.5 9v2.5H7V15h2.5v6.5H13V15h2.5l.5-3.5h-3V9a1 1 0 0 1 1-1Z"/>',
	'ig'      => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/>',
	'tt'      => '<path d="M16.5 6.5A4.5 4.5 0 0 1 21 11M16.5 6.5a4.5 4.5 0 0 0 2.5 4M4 6h10a4 4 0 0 1 0 8H8m6 4 2.5-3M4 10h14"/>',
);
$ewp_icon = function ( $key, $class = '' ) use ( $ewp_icons ) {
	$path = isset( $ewp_icons[ $key ] ) ? $ewp_icons[ $key ] : $ewp_icons['bolt'];
	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
};

/* Sport color map */
$ewp_sport_colors = array(
	'zumba-step' => '#ff3d81',
	'3x3-basket' => '#ff8a2a',
	'padel'      => '#2f7bff',
	'yoga'       => '#00e0c6',
	'run'        => '#8b5cf6',
);

/* Testimonials */
$ewp_testimonials = array(
	array( 'name' => 'Ayu Lestari', 'role' => 'Runner — Jakarta', 'color' => '#2f7bff', 'stars' => 5, 'text' => 'Tiket malam hari langsung masuk HP, tinggal scan QR di gate. Prosesnya mulus banget dari daftar sampai check-in. Pengalaman event terbaik yang pernah ada!' ),
	array( 'name' => 'Rizky Pratama', 'role' => 'Atlet 3x3 — Bandung', 'color' => '#ff8a2a', 'stars' => 5, 'text' => 'Beli tiket tim untuk 5 orang dalam sekali checkout, pakai voucher NATION10. Adminnya juga fast response buat validasi pembayaran. Recommended!' ),
	array( 'name' => 'Sinta Dewi', 'role' => 'Zumba Lover — Surabaya', 'color' => '#ff3d81', 'stars' => 5, 'text' => 'Form kesehatan singkat bikin tenang, dan reminder check-in via WhatsApp benar-benar membantu. Sampai lokasi tinggal scan, langsung fun!' ),
);
?>
<div class="ewp-landing" id="eventwp-landing">
	<a class="ewp-skip-link" href="#main-eventwp"><?php esc_html_e( 'Lewati ke konten', 'eventwp' ); ?></a>

	<!-- NAVBAR -->
	<header class="ewp-nav" data-ewp-nav>
		<div class="ewp-wrap ewp-nav__inner">
			<a class="ewp-logo" href="#main-eventwp" aria-label="<?php echo esc_attr( $ewp_brand ); ?>">
				<span class="ewp-logo__mark"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'bolt' ) ); ?></span>
				<span><?php echo esc_html( $ewp_brand ); ?></span>
			</a>
			<nav class="ewp-nav__links" aria-label="<?php esc_attr_e( 'Navigasi utama', 'eventwp' ); ?>">
				<a class="ewp-nav__link" href="#sports"><?php esc_html_e( 'Sports', 'eventwp' ); ?></a>
				<a class="ewp-nav__link" href="#events"><?php esc_html_e( 'Events', 'eventwp' ); ?></a>
				<a class="ewp-nav__link" href="#benefits"><?php esc_html_e( 'Benefits', 'eventwp' ); ?></a>
				<a class="ewp-nav__link" href="#how"><?php esc_html_e( 'Cara Kerja', 'eventwp' ); ?></a>
				<a class="ewp-nav__link" href="#faq"><?php esc_html_e( 'FAQ', 'eventwp' ); ?></a>
				<a class="ewp-nav__cta" href="#ticket"><?php esc_html_e( 'Beli Tiket', 'eventwp' ); ?> <?php echo $ewp->helpers->kses_svg( $ewp_icon( 'arrow' ) ); ?></a>
			</nav>
			<button class="ewp-burger" data-ewp-burger aria-label="<?php esc_attr_e( 'Buka menu', 'eventwp' ); ?>" aria-expanded="false" aria-controls="ewpMobileNav">
				<span></span><span></span><span></span>
			</button>
		</div>
	</header>

	<main id="main-eventwp">
		<!-- HERO -->
		<section class="ewp-hero" id="hero">
			<div class="ewp-hero__bg" aria-hidden="true">
				<div class="ewp-orb ewp-orb--1"></div>
				<div class="ewp-orb ewp-orb--2"></div>
				<div class="ewp-orb ewp-orb--3"></div>
				<div class="ewp-grid-overlay"></div>
			</div>
			<div class="ewp-wrap ewp-hero__inner">
				<div class="ewp-hero__copy">
					<span class="ewp-hero__badge" data-reveal><span class="dot"></span> <?php esc_html_e( 'Tiket event olahraga #1 di kotamu · Musim 2026', 'eventwp' ); ?></span>
					<h1 class="ewp-hero__title" data-reveal data-reveal-delay="1">
						<?php esc_html_e( 'Rasakan Energie', 'eventwp' ); ?><br />
						<span class="ewp-grad-text"><?php esc_html_e( 'Sport Event', 'eventwp' ); ?></span><br />
						<?php esc_html_e( 'Tanpa Ribet.', 'eventwp' ); ?>
					</h1>
					<p class="ewp-hero__sub" data-reveal data-reveal-delay="2">
						<?php esc_html_e( 'Zumba Step, 3x3 Basket, Padel, Yoga, dan Night Run — satu platform untuk cari event, pesan tiket, bayar via QRIS atau transfer, dan check-in cukup dengan satu scan QR.', 'eventwp' ); ?>
					</p>
					<div class="ewp-hero__actions" data-reveal data-reveal-delay="3">
						<a class="ewp-btn ewp-btn--primary ewp-btn--lg" href="#ticket">
							<?php echo $ewp->helpers->kses_svg( $ewp_icon( 'ticket' ) ); ?>
							<?php esc_html_e( 'Amankan Tiketmu', 'eventwp' ); ?>
						</a>
						<a class="ewp-btn ewp-btn--ghost ewp-btn--lg" href="#how">
							<span class="ewp-btn__play"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'play' ) ); ?></span>
							<?php esc_html_e( 'Lihat Cara Kerjanya', 'eventwp' ); ?>
						</a>
					</div>
					<div class="ewp-hero__trust" data-reveal data-reveal-delay="4">
						<div class="ewp-stat"><span class="ewp-stat__num"><span class="accent">+12K</span></span><span class="ewp-stat__label"><?php esc_html_e( 'Peserta aktif', 'eventwp' ); ?></span></div>
						<div class="ewp-stat"><span class="ewp-stat__num"><?php echo esc_html( number_format_i18n( max( 1, count( $ewp_events ) ) + 4 ) ); ?></span><span class="ewp-stat__label"><?php esc_html_e( 'Event per bulan', 'eventwp' ); ?></span></div>
						<div class="ewp-stat"><span class="ewp-stat__num">4.9<span class="accent">★</span></span><span class="ewp-stat__label"><?php esc_html_e( 'Rating peserta', 'eventwp' ); ?></span></div>
						<div class="ewp-stat"><span class="ewp-stat__num"><span class="accent">&lt;5 dtk</span></span><span class="ewp-stat__label"><?php esc_html_e( 'Proses check-in', 'eventwp' ); ?></span></div>
					</div>
				</div>

				<div class="ewp-hero__visual" aria-hidden="true">
					<div class="ewp-hero__phone">
						<div class="ewp-hero__phone-screen">
							<img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='600' height='1240'%3E%3Cdefs%3E%3ClinearGradient id='a' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0' stop-color='%232f7bff'/%3E%3Cstop offset='0.55' stop-color='%2300e0c6'/%3E%3Cstop offset='1' stop-color='%237c5cff'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='600' height='760' fill='%230d1322'/%3E%3Crect width='600' height='1240' fill='url(%23a)' opacity='0.16'/%3E%3Ctext x='40' y='96' font-family='Arial' font-size='22' fill='%238b93b0'%3EGood morning%3C/text%3E%3Ctext x='40' y='136' font-family='Arial' font-size='34' font-weight='700' fill='%23ffffff'%3EUpcoming Events%3C/text%3E%3C/text%3E%3Cg%3E%3Crect x='40' y='176' width='520' height='150' rx='20' fill='%23171f36'/%3E%3Crect x='40' y='176' width='520' height='150' rx='20' fill='url(%23a)' opacity='0.25'/%3E%3Ccircle cx='96' cy='226' r='26' fill='%23ffffff' opacity='0.15'/%3E%3Crect x='148' y='204' width='240' height='16' rx='8' fill='%23ffffff' opacity='0.85'/%3E%3Crect x='148' y='232' width='140' height='12' rx='6' fill='%23ffffff' opacity='0.4'/%3E%3Crect x='456' y='214' width='80' height='34' rx='17' fill='%2300e0c6'/%3E%3Ctext x='472' y='237' font-family='Arial' font-size='15' font-weight='700' fill='%23000'%3EBeli%3C/text%3E%3Crect x='40' y='346' width='520' height='150' rx='20' fill='%23171f36'/%3E%3Crect x='40' y='346' width='520' height='150' rx='20' fill='%23ff3d81' opacity='0.22'/%3E%3Ccircle cx='96' cy='396' r='26' fill='%23ffffff' opacity='0.15'/%3E%3Crect x='148' y='374' width='220' height='16' rx='8' fill='%23ffffff' opacity='0.85'/%3E%3Crect x='148' y='402' width='140' height='12' rx='6' fill='%23ffffff' opacity='0.4'/%3E%3Crect x='456' y='384' width='80' height='34' rx='17' fill='%23ff8a2a'/%3E%3Ctext x='472' y='407' font-family='Arial' font-size='15' font-weight='700' fill='%23fff'%3EEvent%3C/text%3E%3Crect x='40' y='516' width='520' height='150' rx='20' fill='%23171f36'/%3E%3Crect x='40' y='516' width='520' height='150' rx='20' fill='%2300e0c6' opacity='0.18'/%3E%3Ccircle cx='96' cy='566' r='26' fill='%23ffffff' opacity='0.15'/%3E%3Crect x='148' y='544' width='200' height='16' rx='8' fill='%23ffffff' opacity='0.85'/%3E%3Crect x='148' y='572' width='140' height='12' rx='6' fill='%23ffffff' opacity='0.4'/%3E%3Crect x='456' y='554' width='80' height='34' rx='17' fill='%2300e0c6'/%3E%3Ctext x='472' y='577' font-family='Arial' font-size='15' font-weight='700' fill='%23000'%3EBeli%3C/text%3E%3C/g%3E%3Crect x='40' y='700' width='520' height='460' rx='24' fill='%23171f36'/%3E%3Ctext x='72' y='760' font-family='Arial' font-size='24' font-weight='700' fill='%23ffffff'%3ETiket Aktif%3C/text%3E%3Crect x='150' y='820' width='300' height='300' rx='20' fill='%23ffffff'/%3E%3Crect x='190' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='207' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='224' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='275' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='292' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='309' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='343' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='360' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='377' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='860' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='877' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='877' width='13' height='13' fill='%230b1224'/%3E%3Crect x='292' y='877' width='13' height='13' fill='%230b1224'/%3E%3Crect x='309' y='877' width='13' height='13' fill='%230b1224'/%3E%3Crect x='326' y='877' width='13' height='13' fill='%230b1224'/%3E%3Crect x='343' y='877' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='877' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='224' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='258' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='292' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='309' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='343' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='377' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='894' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='207' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='224' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='275' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='326' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='343' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='360' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='377' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='911' width='13' height='13' fill='%230b1224'/%3E%3Crect x='224' y='928' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='928' width='13' height='13' fill='%230b1224'/%3E%3Crect x='275' y='928' width='13' height='13' fill='%230b1224'/%3E%3Crect x='292' y='928' width='13' height='13' fill='%230b1224'/%3E%3Crect x='309' y='928' width='13' height='13' fill='%230b1224'/%3E%3Crect x='207' y='945' width='13' height='13' fill='%230b1224'/%3E%3Crect x='326' y='945' width='13' height='13' fill='%230b1224'/%3E%3Crect x='343' y='945' width='13' height='13' fill='%230b1224'/%3E%3Crect x='360' y='945' width='13' height='13' fill='%230b1224'/%3E%3Crect x='377' y='945' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='945' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='962' width='13' height='13' fill='%230b1224'/%3E%3Crect x='207' y='962' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='962' width='13' height='13' fill='%230b1224'/%3E%3Crect x='258' y='962' width='13' height='13' fill='%230b1224'/%3E%3Crect x='275' y='962' width='13' height='13' fill='%230b1224'/%3E%3Crect x='292' y='962' width='13' height='13' fill='%230b1224'/%3E%3Crect x='360' y='962' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='962' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='979' width='13' height='13' fill='%230b1224'/%3E%3Crect x='309' y='979' width='13' height='13' fill='%230b1224'/%3E%3Crect x='326' y='979' width='13' height='13' fill='%230b1224'/%3E%3Crect x='343' y='979' width='13' height='13' fill='%230b1224'/%3E%3Crect x='360' y='979' width='13' height='13' fill='%230b1224'/%3E%3Crect x='377' y='979' width='13' height='13' fill='%230b1224'/%3E%3Crect x='207' y='996' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='996' width='13' height='13' fill='%230b1224'/%3E%3Crect x='292' y='996' width='13' height='13' fill='%230b1224'/%3E%3Crect x='309' y='996' width='13' height='13' fill='%230b1224'/%3E%3Crect x='343' y='996' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='996' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='207' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='224' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='258' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='309' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='326' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='343' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='1013' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='1030' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='1030' width='13' height='13' fill='%230b1224'/%3E%3Crect x='258' y='1030' width='13' height='13' fill='%230b1224'/%3E%3Crect x='275' y='1030' width='13' height='13' fill='%230b1224'/%3E%3Crect x='360' y='1030' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='1047' width='13' height='13' fill='%230b1224'/%3E%3Crect x='224' y='1047' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='1047' width='13' height='13' fill='%230b1224'/%3E%3Crect x='275' y='1047' width='13' height='13' fill='%230b1224'/%3E%3Crect x='309' y='1047' width='13' height='13' fill='%230b1224'/%3E%3Crect x='326' y='1047' width='13' height='13' fill='%230b1224'/%3E%3Crect x='394' y='1047' width='13' height='13' fill='%230b1224'/%3E%3Crect x='190' y='1064' width='13' height='13' fill='%230b1224'/%3E%3Crect x='207' y='1064' width='13' height='13' fill='%230b1224'/%3E%3Crect x='224' y='1064' width='13' height='13' fill='%230b1224'/%3E%3Crect x='241' y='1064' width='13' height='13' fill='%230b1224'/%3E%3Crect x='275' y='1064' width='13' height='13' fill='%230b1224'/%3E%3Crect x='326' y='1064' width='13' height='13' fill='%230b1224'/%3E%3Crect x='377' y='1064' width='13' height='13' fill='%230b1224'/%3E%3Ctext x='672' y='700' font-family='Arial' font-size='0' fill='%23000'%3EQR%3C/text%3E%3C/svg%3E" alt="" loading="lazy" width="600" height="1240" />
						</div>
					</div>
					<div class="ewp-float-card ewp-float-card--ticket">
						<span class="ewp-float-card__icon" style="background:linear-gradient(135deg,#2f7bff,#7c5cff)"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'ticket' ) ); ?></span>
						<div><b><?php esc_html_e( 'E-Ticket Instan', 'eventwp' ); ?></b><small><?php esc_html_e( 'Langsung masuk aplikasi', 'eventwp' ); ?></small></div>
					</div>
					<div class="ewp-float-card ewp-float-card--qr">
						<span class="ewp-float-card__icon" style="background:linear-gradient(135deg,#00e0c6,#2f7bff)"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'qr' ) ); ?></span>
						<div><b><?php esc_html_e( 'Check-in QR', 'eventwp' ); ?></b><small><?php esc_html_e( 'Satu scan, langsung masuk', 'eventwp' ); ?></small></div>
					</div>
					<div class="ewp-float-card ewp-float-card--checkin">
						<span class="ewp-float-card__icon" style="background:linear-gradient(135deg,#ff3d81,#ff8a2a)"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'check' ) ); ?></span>
						<div><b><?php esc_html_e( 'Kehadiran Tercatat', 'eventwp' ); ?></b><small><?php esc_html_e( 'Real-time untuk coach', 'eventwp' ); ?></small></div>
					</div>
				</div>
			</div>
		</section>

		<!-- MARQUEE -->
		<div class="ewp-marquee" aria-hidden="true">
			<div class="ewp-marquee__track">
				<?php for ( $x = 0; $x < 2; $x++ ) : ?>
					<span><i>⚡</i> Zumba Step</span>
					<span><i>🏀</i> 3x3 Basket</span>
					<span><i>🎾</i> Padel</span>
					<span><i>🧘</i> Yoga</span>
					<span><i>🏃</i> Night Run</span>
					<span><i>🎟️</i> E-Ticket</span>
					<span><i>📱</i> Check-in QR</span>
					<span><i>💳</i> QRIS &amp; Transfer</span>
				<?php endfor; ?>
			</div>
		</div>

		<!-- SPORTS -->
		<section class="ewp-section ewp-section--dark" id="sports">
			<div class="ewp-wrap">
				<div class="ewp-section-head">
					<span class="ewp-eyebrow" data-reveal><?php esc_html_e( 'Pilih Arena Mainmu', 'eventwp' ); ?></span>
					<h2 class="ewp-h2" data-reveal data-reveal-delay="1"><?php esc_html_e( '5 Cabang Olahraga,', 'eventwp' ); ?> <span class="ewp-grad-text"><?php esc_html_e( 'Satu Komunitas', 'eventwp' ); ?></span></h2>
					<p class="ewp-lead" data-reveal data-reveal-delay="2"><?php esc_html_e( 'Dari lantai dansa sampai garis finish — pilih sport favoritmu dan temukan event paling dekat dengan domisilimu.', 'eventwp' ); ?></p>
				</div>
				<div class="ewp-sports-grid" data-ewp-sports>
					<?php
					$ewp_display_cats = $ewp_cats;
					if ( empty( $ewp_display_cats ) ) {
						$ewp_display_cats = array(
							array( 'name' => 'Zumba Step', 'slug' => 'zumba-step' ),
							array( 'name' => '3x3 Basket', 'slug' => '3x3-basket' ),
							array( 'name' => 'Padel', 'slug' => 'padel' ),
							array( 'name' => 'Yoga', 'slug' => 'yoga' ),
							array( 'name' => 'Run', 'slug' => 'run' ),
						);
					}
					$ewp_i = 0;
					foreach ( array_slice( $ewp_display_cats, 0, 5 ) as $ewp_cat ) :
						$ewp_slug  = isset( $ewp_cat['slug'] ) ? $ewp_cat['slug'] : 'zumba-step';
						$ewp_color = isset( $ewp_cat['color'] ) ? $ewp_cat['color'] : ( isset( $ewp_sport_colors[ $ewp_slug ] ) ? $ewp_sport_colors[ $ewp_slug ] : '#2f7bff' );
						$ewp_icon_key_map = array(
							'zumba-step' => 'zumba',
							'zumba'      => 'zumba',
							'3x3-basket' => 'basket',
							'basket'     => 'basket',
							'padel'      => 'padel',
							'yoga'       => 'yoga',
							'run'        => 'run',
						);
						$ewp_icon_key = isset( $ewp_icon_key_map[ $ewp_slug ] ) ? $ewp_icon_key_map[ $ewp_slug ] : 'bolt';
						?>
						<a class="ewp-sport" href="#events" style="--sport-color:<?php echo esc_attr( $ewp_color ); ?>" data-reveal data-reveal-delay="<?php echo esc_attr( ( $ewp_i % 5 ) + 1 ); ?>">
							<span class="ewp-sport__icon"><?php echo $ewp->helpers->kses_svg( $ewp_icon( $ewp_icon_key ) ); ?></span>
							<h3><?php echo esc_html( $ewp_cat['name'] ); ?></h3>
							<p><?php echo esc_html( isset( $ewp_cat['count'] ) ? number_format_i18n( $ewp_cat['count'] ) . ' event' : 'Event reguler' ); ?></p>
						</a>
						<?php
						$ewp_i++;
					endforeach;
					?>
				</div>
			</div>
		</section>

		<!-- EVENTS -->
		<section class="ewp-section ewp-section--soft" id="events">
			<div class="ewp-wrap">
				<div class="ewp-section-head">
					<span class="ewp-eyebrow" data-reveal><?php esc_html_e( 'Upcoming Events', 'eventwp' ); ?></span>
					<h2 class="ewp-h2" data-reveal data-reveal-delay="1"><?php esc_html_e( 'Event Pilihan', 'eventwp' ); ?> <span class="ewp-grad-text"><?php esc_html_e( 'Minggu Ini', 'eventwp' ); ?></span></h2>
					<p class="ewp-lead" data-reveal data-reveal-delay="2"><?php esc_html_e( 'Kuota terbatas setiap event. Pilih jadwalmu dan amankan slot sebelum penuh.', 'eventwp' ); ?></p>
				</div>
				<div class="ewp-events-grid">
					<?php
					$ewp_show_events = array_slice( $ewp_events, 0, 6 );
					if ( empty( $ewp_show_events ) ) :
						?>
						<div class="ewp-events-grid__empty" data-reveal><?php esc_html_e( 'Event akan segera diumumkan. Nantikan jadwal terbaru!', 'eventwp' ); ?></div>
					<?php endif; ?>
					<?php
					$ewp_ei = 0;
					foreach ( $ewp_show_events as $ewp_e ) :
						$ewp_ts   = strtotime( (string) $ewp_e['date_start'] );
						$ewp_day  = $ewp_ts ? date_i18n( 'd', $ewp_ts ) : '—';
						$ewp_mon  = $ewp_ts ? date_i18n( 'M', $ewp_ts ) : '';
						$ewp_color = isset( $ewp_e['category_color'] ) ? $ewp_e['category_color'] : '#2f7bff';
						?>
						<article class="ewp-event-card" data-reveal data-reveal-delay="<?php echo esc_attr( ( $ewp_ei % 3 ) + 1 ); ?>">
							<a class="ewp-event-card__media" href="#ticket" aria-label="<?php echo esc_attr( $ewp_e['title'] ); ?>">
								<img src="<?php echo esc_url( $ewp_e['banner'] ); ?>" alt="<?php echo esc_attr( $ewp_e['title'] ); ?>" loading="lazy" width="640" height="420" />
								<span class="ewp-event-card__chip" style="color:<?php echo esc_attr( $ewp_color ); ?>"><span class="dot" style="background:<?php echo esc_attr( $ewp_color ); ?>;width:7px;height:7px;border-radius:50%;display:inline-block"></span><?php echo esc_html( $ewp_e['category_name'] ); ?></span>
								<span class="ewp-event-card__date"><b><?php echo esc_html( $ewp_day ); ?></b><small><?php echo esc_html( $ewp_mon ); ?></small></span>
							</a>
							<div class="ewp-event-card__body">
								<h3 class="ewp-event-card__title"><?php echo esc_html( $ewp_e['title'] ); ?></h3>
								<p class="ewp-event-card__meta"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'clock' ) ); ?> <?php echo esc_html( $ewp_e['date_start_fmt'] ); ?></p>
								<p class="ewp-event-card__meta"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'map' ) ); ?> <?php echo esc_html( $ewp_e['location'] ); ?></p>
								<div class="ewp-event-card__foot">
									<span class="ewp-price"><?php echo esc_html( $ewp_e['price_fmt'] ); ?></span>
									<a class="ewp-event-card__link" href="#ticket"><?php esc_html_e( 'Beli', 'eventwp' ); ?> <?php echo $ewp->helpers->kses_svg( $ewp_icon( 'arrow' ) ); ?></a>
								</div>
							</div>
						</article>
						<?php
						$ewp_ei++;
					endforeach;
					?>
				</div>
				<div class="ewp-events-cta" data-reveal>
					<a class="ewp-btn ewp-btn--ghost" href="<?php echo esc_url( $ewp_app_url ); ?>"><?php esc_html_e( 'Lihat Semua Event', 'eventwp' ); ?> <?php echo $ewp->helpers->kses_svg( $ewp_icon( 'arrow' ) ); ?></a>
				</div>
			</div>
		</section>

		<!-- BENEFITS -->
		<section class="ewp-section ewp-section--dark" id="benefits">
			<div class="ewp-wrap">
				<div class="ewp-section-head">
					<span class="ewp-eyebrow" data-reveal><?php esc_html_e( 'Kenapa Active Nation', 'eventwp' ); ?></span>
					<h2 class="ewp-h2" data-reveal data-reveal-delay="1"><?php esc_html_e( 'Dibuat untuk', 'eventwp' ); ?> <span class="ewp-grad-text"><?php esc_html_e( 'Pengalaman Maksimal', 'eventwp' ); ?></span></h2>
					<p class="ewp-lead" data-reveal data-reveal-delay="2"><?php esc_html_e( 'Bukan sekadar jual tiket — kami merancang seluruh perjalananmu, dari layar ponsel sampai garis finish.', 'eventwp' ); ?></p>
				</div>
				<div class="ewp-benefits-grid">
					<div class="ewp-benefit" style="--benefit-color:#2f7bff" data-reveal data-reveal-delay="1">
						<span class="ewp-benefit__glow"></span>
						<span class="ewp-benefit__icon"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'phone' ) ); ?></span>
						<h3><?php esc_html_e( 'App Mobile-First', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Antarmuka layaknya aplikasi native: bottom navigation, modal bottom-sheet, dan transisi halus di desktop maupun HP.', 'eventwp' ); ?></p>
					</div>
					<div class="ewp-benefit" style="--benefit-color:#ff3d81" data-reveal data-reveal-delay="2">
						<span class="ewp-benefit__glow"></span>
						<span class="ewp-benefit__icon"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'ticket' ) ); ?></span>
						<h3><?php esc_html_e( 'E-Ticket & QR Check-in', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Tiket aktif langsung muncul dengan QR code. Sampai di lokasi, cukup scan — kehadiran tercatat real-time untuk coach.', 'eventwp' ); ?></p>
					</div>
					<div class="ewp-benefit" style="--benefit-color:#00e0c6" data-reveal data-reveal-delay="3">
						<span class="ewp-benefit__glow"></span>
						<span class="ewp-benefit__icon"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'grid' ) ); ?></span>
						<h3><?php esc_html_e( 'Voucher & Pembayaran Fleksibel', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Klaim voucher diskon persentase/nominal, lalu bayar via Transfer Bank atau QRIS dengan unggah bukti pembayaran.', 'eventwp' ); ?></p>
					</div>
					<div class="ewp-benefit" style="--benefit-color:#ff8a2a" data-reveal data-reveal-delay="4">
						<span class="ewp-benefit__glow"></span>
						<span class="ewp-benefit__icon"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'clock' ) ); ?></span>
						<h3><?php esc_html_e( 'Reminder Otomatis', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Pesan WhatsApp otomatis untuk reminder check-in dan pengingat isi form persyaratan — kamu tidak akan ketinggalan event.', 'eventwp' ); ?></p>
					</div>
					<div class="ewp-benefit" style="--benefit-color:#8b5cf6" data-reveal data-reveal-delay="5">
						<span class="ewp-benefit__glow"></span>
						<span class="ewp-benefit__icon"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'heart' ) ); ?></span>
						<h3><?php esc_html_e( 'Komunitas & Ulasan', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Beri rating bintang, bagikan foto ke galeri event bersama, dan baca feedback dari peserta lain sebelum mendaftar.', 'eventwp' ); ?></p>
					</div>
					<div class="ewp-benefit" style="--benefit-color:#2f7bff" data-reveal data-reveal-delay="4">
						<span class="ewp-benefit__glow"></span>
						<span class="ewp-benefit__icon"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'shield' ) ); ?></span>
						<h3><?php esc_html_e( 'Aman & Tervalidasi', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Setiap pembayaran divalidasi admin, data tersimpan di database WordPress kamu, dan seluruh aktivitas terekam di audit log.', 'eventwp' ); ?></p>
					</div>
				</div>
			</div>
		</section>

		<!-- HOW IT WORKS -->
		<section class="ewp-section ewp-section--light" id="how">
			<div class="ewp-wrap">
				<div class="ewp-section-head">
					<span class="ewp-eyebrow" data-reveal><?php esc_html_e( 'Proses Super Simpel', 'eventwp' ); ?></span>
					<h2 class="ewp-h2" data-reveal data-reveal-delay="1"><?php esc_html_e( 'Cara Kerjanya', 'eventwp' ); ?> <span class="ewp-grad-text"><?php esc_html_e( 'Dalam 4 Langkah', 'eventwp' ); ?></span></h2>
				</div>
				<div class="ewp-steps">
					<div class="ewp-step" data-reveal data-reveal-delay="1">
						<span class="ewp-step__num">1</span>
						<h3><?php esc_html_e( 'Daftar & Pilih Kota', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Buat akun dengan nomor HP. Event otomatis difilter berdasarkan kota domisilimu.', 'eventwp' ); ?></p>
					</div>
					<div class="ewp-step" data-reveal data-reveal-delay="2">
						<span class="ewp-step__num">2</span>
						<h3><?php esc_html_e( 'Pilih Event & Bayar', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Pilih kuantitas tiket (maks 5), klaim voucher, lalu bayar via Transfer Bank atau QRIS.', 'eventwp' ); ?></p>
					</div>
					<div class="ewp-step" data-reveal data-reveal-delay="3">
						<span class="ewp-step__num">3</span>
						<h3><?php esc_html_e( 'Terima E-Ticket', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Setelah pembayaran tervalidasi, e-ticket + QR code aktif di aplikasi. Isi form persyaratan jika diperlukan.', 'eventwp' ); ?></p>
					</div>
					<div class="ewp-step" data-reveal data-reveal-delay="4">
						<span class="ewp-step__num">4</span>
						<h3><?php esc_html_e( 'Scan & Have Fun', 'eventwp' ); ?></h3>
						<p><?php esc_html_e( 'Tunjukkan QR di gate, check-in dalam hitungan detik, lalu nikmati event dan bagikan momenmu.', 'eventwp' ); ?></p>
					</div>
				</div>
			</div>
		</section>

		<!-- TESTIMONIALS -->
		<section class="ewp-section ewp-section--soft" id="testimonials">
			<div class="ewp-wrap">
				<div class="ewp-section-head">
					<span class="ewp-eyebrow" data-reveal><?php esc_html_e( 'Kata Mereka', 'eventwp' ); ?></span>
					<h2 class="ewp-h2" data-reveal data-reveal-delay="1"><?php esc_html_e( 'Dipercaya Ribuan', 'eventwp' ); ?> <span class="ewp-grad-text"><?php esc_html_e( 'Peserta Aktif', 'eventwp' ); ?></span></h2>
				</div>
				<div class="ewp-testi-grid">
					<?php
					$ewp_ti = 0;
					foreach ( $ewp_testimonials as $ewp_t ) :
						?>
						<blockquote class="ewp-testi" data-reveal data-reveal-delay="<?php echo esc_attr( ( $ewp_ti % 3 ) + 1 ); ?>">
							<div class="ewp-testi__stars">
								<?php for ( $s = 0; $s < $ewp_t['stars']; $s++ ) : ?>
									<?php echo $ewp->helpers->kses_svg( $ewp_icon( 'star' ) ); ?>
								<?php endfor; ?>
							</div>
							<p class="ewp-testi__text">“<?php echo esc_html( $ewp_t['text'] ); ?>”</p>
							<footer class="ewp-testi__author">
								<span class="ewp-avatar" style="background:<?php echo esc_attr( $ewp_t['color'] ); ?>"><?php echo esc_html( $api->initials( $ewp_t['name'] ) ); ?></span>
								<div><b><?php echo esc_html( $ewp_t['name'] ); ?></b><small><?php echo esc_html( $ewp_t['role'] ); ?></small></div>
							</footer>
						</blockquote>
						<?php
						$ewp_ti++;
					endforeach;
					?>
				</div>
			</div>
		</section>

		<!-- FAQ -->
		<section class="ewp-section ewp-section--dark" id="faq">
			<div class="ewp-wrap">
				<div class="ewp-section-head">
					<span class="ewp-eyebrow" data-reveal><?php esc_html_e( 'Punya Pertanyaan?', 'eventwp' ); ?></span>
					<h2 class="ewp-h2" data-reveal data-reveal-delay="1"><?php esc_html_e( 'FAQ', 'eventwp' ); ?></h2>
				</div>
				<div class="ewp-faq">
					<?php
					$ewp_faqs = array(
						array( 'Bagaimana cara membeli tiket?', 'Login/daftar dengan nomor HP, pilih event di kotamu, tentukan jumlah tiket (maks 5), klaim voucher bila ada, pilih metode pembayaran (Transfer Bank/QRIS), unggah bukti, dan e-ticket kamu aktif setelah validasi.' ),
						array( 'Apakah tiket bisa dipindahtangankan?', 'Saat ini tiket bersifat personal karena terkait akun dan QR check-in kamu. Untuk perubahan nama, hubungi support kami sebelum hari-H.' ),
						array( 'Bagaimana sistem refund-nya?', 'Refund tersedia untuk pesanan yang belum terverifikasi. Setelah e-ticket diterbitkan, silakan hubungi admin untuk kebijakan kasus-per-kasus.' ),
						array( 'Apa itu form persyaratan event?', 'Beberapa event (misal riwayat cedera, ukuran baju, tinggi/berat) mewajibkan pengisian form kesehatan/data diri. Pengingat akan dikirim otomatis via WhatsApp.' ),
						array( 'Apakah saya bisa pindah jadwal?', 'Ya, selama kuota event tujuan masih tersedia dan permintaan diajukan minimal 48 jam sebelum event dimulai.' ),
					);
					$ewp_fi = 0;
					foreach ( $ewp_faqs as $ewp_faq ) :
						?>
						<div class="ewp-faq__item" data-reveal data-reveal-delay="<?php echo esc_attr( ( $ewp_fi % 4 ) + 1 ); ?>">
							<button class="ewp-faq__q" data-ewp-faq aria-expanded="false">
								<span><?php echo esc_html( $ewp_faq[0] ); ?></span>
								<span class="icon"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'plus' ) ); ?></span>
							</button>
							<div class="ewp-faq__a"><div class="ewp-faq__a-inner"><?php echo esc_html( $ewp_faq[1] ); ?></div></div>
						</div>
						<?php
						$ewp_fi++;
					endforeach;
					?>
				</div>
			</div>
		</section>

		<!-- CTA -->
		<section class="ewp-section ewp-section--dark" id="ticket">
			<div class="ewp-wrap">
				<div class="ewp-cta-panel" data-reveal data-reveal-delay="1">
					<h2><?php esc_html_e( 'Siap Bergabung di', 'eventwp' ); ?> <?php echo esc_html( $ewp_brand ); ?>?</h2>
					<p><?php esc_html_e( 'Amankan tiketmu sekarang — kuota terbatas dan slot favorit cepat penuh. Cari event di kotamu dan jadilah bagian dari komunitas sport paling aktif.', 'eventwp' ); ?></p>
					<a class="ewp-btn ewp-btn--white ewp-btn--lg" href="<?php echo esc_url( $ewp_app_url ); ?>" target="_blank" rel="noopener">
						<?php echo $ewp->helpers->kses_svg( $ewp_icon( 'bolt' ) ); ?>
						<?php esc_html_e( 'Beli Tiket Sekarang', 'eventwp' ); ?>
					</a>
				</div>
			</div>
		</section>
	</main>

	<!-- FOOTER -->
	<footer class="ewp-footer">
		<div class="ewp-wrap">
			<div class="ewp-footer__grid">
				<div class="ewp-footer__brand">
					<a class="ewp-logo" href="#main-eventwp">
						<span class="ewp-logo__mark"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'bolt' ) ); ?></span>
						<span><?php echo esc_html( $ewp_brand ); ?></span>
					</a>
					<p><?php esc_html_e( 'Ekosistem event olahraga digital yang menghubungkan peserta, coach, dan penyelenggara dalam satu platform yang aman dan menyenangkan.', 'eventwp' ); ?></p>
					<div class="ewp-footer__social">
						<a href="#" aria-label="Instagram"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'ig' ) ); ?></a>
						<a href="#" aria-label="Facebook"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'fb' ) ); ?></a>
						<a href="#" aria-label="TikTok"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'tt' ) ); ?></a>
						<a href="#" aria-label="WhatsApp"><?php echo $ewp->helpers->kses_svg( $ewp_icon( 'wa' ) ); ?></a>
					</div>
				</div>
				<div>
					<h4><?php esc_html_e( 'Sports', 'eventwp' ); ?></h4>
					<ul>
						<li><a href="#sports"><?php esc_html_e( 'Zumba Step', 'eventwp' ); ?></a></li>
						<li><a href="#sports"><?php esc_html_e( '3x3 Basket', 'eventwp' ); ?></a></li>
						<li><a href="#sports"><?php esc_html_e( 'Padel', 'eventwp' ); ?></a></li>
						<li><a href="#sports"><?php esc_html_e( 'Yoga', 'eventwp' ); ?></a></li>
						<li><a href="#sports"><?php esc_html_e( 'Night Run', 'eventwp' ); ?></a></li>
					</ul>
				</div>
				<div>
					<h4><?php esc_html_e( 'Navigasi', 'eventwp' ); ?></h4>
					<ul>
						<li><a href="#events"><?php esc_html_e( 'Events', 'eventwp' ); ?></a></li>
						<li><a href="#benefits"><?php esc_html_e( 'Benefits', 'eventwp' ); ?></a></li>
						<li><a href="#how"><?php esc_html_e( 'Cara Kerja', 'eventwp' ); ?></a></li>
						<li><a href="#faq"><?php esc_html_e( 'FAQ', 'eventwp' ); ?></a></li>
					</ul>
				</div>
				<div>
					<h4><?php esc_html_e( 'Bantuan', 'eventwp' ); ?></h4>
					<ul>
						<li><a href="#faq"><?php esc_html_e( 'Pusat Bantuan', 'eventwp' ); ?></a></li>
						<li><a href="#faq"><?php esc_html_e( 'Kebijakan Refund', 'eventwp' ); ?></a></li>
						<li><a href="#faq"><?php esc_html_e( 'Syarat & Ketentuan', 'eventwp' ); ?></a></li>
						<li><a href="#faq"><?php esc_html_e( 'Kontak Kami', 'eventwp' ); ?></a></li>
					</ul>
				</div>
			</div>
			<div class="ewp-footer__bottom">
				<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $ewp_brand ); ?>. <?php esc_html_e( 'Seluruh hak cipta dilindungi.', 'eventwp' ); ?></span>
				<span><?php esc_html_e( 'Ditenagai oleh EventWP · Made with ⚡ for sport lovers', 'eventwp' ); ?></span>
			</div>
		</div>
	</footer>

	<button class="ewp-backtop" data-ewp-backtop aria-label="<?php esc_attr_e( 'Kembali ke atas', 'eventwp' ); ?>">
		<?php echo $ewp->helpers->kses_svg( $ewp_icon( 'up' ) ); ?>
	</button>
</div>
