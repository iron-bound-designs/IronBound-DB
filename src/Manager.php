<?php
/**
 * DB Query Manager.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB;

use IronBound\DB\Query\Complex_Query;
use IronBound\DB\Query\Simple_Query;
use IronBound\DB\Table\Table;

/**
 * Class Manager
 *
 * @package IronBound\DB
 */
final class Manager {

	/**
	 * @var Table[]
	 */
	private static $tables = array();

	/**
	 * Register a db table.
	 *
	 * @since 1.0
	 *
	 * @param Table  $table Table object.
	 * @param string $complex_query_class
	 */
	public static function register( Table $table, $complex_query_class = '' ) {
		self::$tables[ $table->get_slug() ] = array(
			'table' => $table,
			'query' => $complex_query_class
		);
	}

	/**
	 * Retrieve a db table object.
	 *
	 * @since 1.0
	 *
	 * @param string $slug
	 *
	 * @return Table|null
	 */
	public static function get( $slug ) {

		if ( isset( self::$tables[ $slug ] ) ) {
			return self::$tables[ $slug ]['table'];
		} else {
			return null;
		}
	}

	/**
	 * Make a query object for the selected db table.
	 *
	 * @since 1.0
	 *
	 * @param string $slug Table name.
	 *
	 * @return Simple_Query|null
	 */
	public static function make_simple_query_object( $slug ) {

		$table = self::get( $slug );

		if ( $table ) {
			return new Simple_Query( $GLOBALS['wpdb'], $table );
		} else {
			return null;
		}
	}

	/**
	 * Make a complex query object.
	 *
	 * @since 1.0
	 *
	 * @param string $slug
	 * @param array  $args
	 *
	 * @return Complex_Query|null
	 */
	public static function make_complex_query_object( $slug, array $args = array() ) {

		$table = self::get( $slug );

		if ( empty( $table ) || empty( self::$tables[ $slug ]['query'] ) ) {
			return null;
		}

		$class = self::$tables[ $slug ]['query'];

		$query = new $class( $args );

		return $query;
	}

	/**
	 * Maybe install a table.
	 *
	 * Will skip a table if it is up-to-date.
	 *
	 * @since 1.0
	 *
	 * @param Table $table
	 *
	 * @return bool True if installed or updated, false if skipped.
	 */
	public static function maybe_install_table( Table $table ) {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$installed = (int) get_option( $table->get_table_name( $GLOBALS['wpdb'] ) . '_version', 0 );

		if ( $installed < $table->get_version() ) {
			dbDelta( $table->get_creation_sql( $GLOBALS['wpdb'] ) );

			update_option( $table->get_table_name( $GLOBALS['wpdb'] ) . '_version', $table->get_version() );

			return true;
		}

		return false;
	}
}