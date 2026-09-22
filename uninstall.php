<?php
/**
 * EventWP — uninstall cleanup.
 * Removes database tables and options (unless EVENTWP_KEEP_DATA is set).
 *
 * @package EventWP
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( defined( 'EVENTWP_KEEP_DATA' ) && EVENTWP_KEEP_DATA ) {
	return;
}

global $wpdb;

$map = array(
	'users'            => 'eventwp_users',
	'events'           => 'eventwp_events',
	'event_categories' => 'eventwp_event_categories',
	'tickets'          => 'eventwp_tickets',
	'orders'           => 'eventwp_orders',
	'form_templates'   => 'eventwp_form_templates',
	'form_responses'   => 'eventwp_form_responses',
	'payments'         => 'eventwp_payments',
	'vouchers'         => 'eventwp_vouchers',
	'reviews'          => 'eventwp_reviews',
	'galleries'        => 'eventwp_galleries',
	'settings'         => 'eventwp_settings',
	'audit_logs'       => 'eventwp_audit_logs',
);

foreach ( $map as $name ) {
	$table = $wpdb->prefix . $name;
	$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
}

delete_option( 'eventwp_db_version' );
delete_option( 'eventwp_app_page' );
