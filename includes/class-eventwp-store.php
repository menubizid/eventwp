<?php
/**
 * EventWP Store — dynamic DataStore over custom tables (same DB as WordPress).
 *
 * @package EventWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Data Access Layer.
 */
class EventWP_Store {

	/**
	 * Allowed table aliases.
	 *
	 * @return array<string,string>
	 */
	public function allowed_tables() {
		return array(
			'users'            => 'users',
			'events'           => 'events',
			'event_categories' => 'event_categories',
			'tickets'          => 'tickets',
			'orders'           => 'orders',
			'form_templates'   => 'form_templates',
			'form_responses'   => 'form_responses',
			'payments'         => 'payments',
			'vouchers'         => 'vouchers',
			'reviews'          => 'reviews',
			'galleries'        => 'galleries',
			'settings'         => 'settings',
			'audit_logs'       => 'audit_logs',
		);
	}

	/**
	 * Resolve a table alias into a full prefixed table name.
	 *
	 * @param string $table Alias.
	 * @throws Exception If the table is not allowed.
	 * @return string
	 */
	public function table( $table ) {
		$allowed = $this->allowed_tables();
		if ( ! isset( $allowed[ $table ] ) ) {
			throw new Exception( 'Table tidak dikenal.' );
		}
		return EventWP_Installer::table( $table );
	}

	/**
	 * Get a single raw row by column conditions.
	 *
	 * @param string $table      Alias.
	 * @param array  $conditions Column => value (all ANDed).
	 * @return array|null
	 */
	public function get_row( $table, $conditions = array() ) {
		global $wpdb;
		try {
			$tbl = $this->table( $table );
		} catch ( Exception $e ) {
			return null;
		}
		$where  = '1=1';
		$params = array();
		if ( ! empty( $conditions ) ) {
			$clauses = array();
			foreach ( $conditions as $col => $val ) {
				$col       = preg_replace( '/[^a-zA-Z0-9_]/', '', (string) $col );
				$clauses[] = "{$col} = %s";
				$params[]  = $val;
			}
			$where = implode( ' AND ', $clauses );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$sql = $wpdb->prepare( "SELECT * FROM {$tbl} WHERE {$where} LIMIT 1", $params );
		return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get rows, optionally ordered and limited.
	 *
	 * @param string $table     Alias.
	 * @param string $order_by  Order column.
	 * @param string $order     ASC|DESC.
	 * @param int    $limit     Limit.
	 * @param int    $offset    Offset.
	 * @return array|null
	 */
	public function get_rows( $table, $order_by = 'id', $order = 'DESC', $limit = 200, $offset = 0 ) {
		global $wpdb;
		try {
			$tbl = $this->table( $table );
		} catch ( Exception $e ) {
			return null;
		}
		$order_by = preg_replace( '/[^a-zA-Z0-9_]/', '', (string) $order_by );
		$order    = 'DESC' === strtoupper( $order ) ? 'DESC' : 'ASC';
		$limit    = max( 1, min( 1000, (int) $limit ) );
		$offset   = max( 0, (int) $offset );
		$by       = $order_by ? $order_by : 'id';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$sql = $wpdb->prepare( "SELECT * FROM {$tbl} ORDER BY {$by} {$order} LIMIT %d OFFSET %d", $limit, $offset );
		$rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Update a record safely.
	 *
	 * @param string $table  Alias.
	 * @param array  $data   Column => value.
	 * @param array  $where  Column => value conditions (mandatory).
	 * @return bool
	 */
	public function update( $table, $data, $where ) {
		global $wpdb;
		try {
			$tbl = $this->table( $table );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (bool) $wpdb->update( $tbl, $data, $where );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Insert a record.
	 *
	 * @param string $table Alias.
	 * @param array  $data  Column => value.
	 * @return int Inserted id or 0.
	 */
	public function insert( $table, $data ) {
		global $wpdb;
		try {
			$tbl = $this->table( $table );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( false === $wpdb->insert( $tbl, $data ) ) {
				return 0;
			}
			return (int) $wpdb->insert_id;
		} catch ( Exception $e ) {
			return 0;
		}
	}

	/**
	 * Delete a record (safe: requires where).
	 *
	 * @param string $table Alias.
	 * @param array  $where Conditions.
	 * @return bool
	 */
	public function delete( $table, $where ) {
		global $wpdb;
		if ( empty( $where ) ) {
			return false;
		}
		try {
			$tbl = $this->table( $table );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (bool) $wpdb->delete( $tbl, $where );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Raw count.
	 *
	 * @param string $table      Alias.
	 * @param array  $conditions Conditions.
	 * @return int
	 */
	public function count( $table, $conditions = array() ) {
		global $wpdb;
		try {
			$tbl = $this->table( $table );
		} catch ( Exception $e ) {
			return 0;
		}
		$where  = '1=1';
		$params = array();
		if ( ! empty( $conditions ) ) {
			$clauses = array();
			foreach ( $conditions as $col => $val ) {
				$col       = preg_replace( '/[^a-zA-Z0-9_]/', '', (string) $col );
				$clauses[] = "{$col} = %s";
				$params[]  = $val;
			}
			$where = implode( ' AND ', $clauses );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$tbl} WHERE {$where}", $params );
		return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * JSON-decode helper for schema/response payload rows.
	 *
	 * @param string $value JSON string.
	 * @return array
	 */
	public function decode_json( $value ) {
		$decoded = json_decode( (string) $value, true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}
		return $decoded;
	}
}
