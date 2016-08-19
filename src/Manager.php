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
use IronBound\DB\Table\Plugins\Plugin;
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
	 * @var Plugin[]
	 */
	private static $plugins = array();

	/**
	 * Register a db table.
	 *
	 * @since 1.0
	 *
	 * @param Table  $table Table object.
	 * @param string $complex_query_class
	 * @param string $model_class
	 *
	 * @throws \InvalidArgumentException If $complex_query_class is not a subclass of Complex_Query.
	 */
	public static function register( Table $table, $complex_query_class = '', $model_class = '' ) {

		if ( $complex_query_class && ! is_subclass_of( $complex_query_class, 'IronBound\DB\Query\Complex_Query' ) ) {
			throw new \InvalidArgumentException( '$complex_query_class must subclass Complex_Query' );
		}

		if ( $model_class && ! is_subclass_of( $model_class, 'IronBound\DB\Model' ) ) {
			throw new \InvalidArgumentException( '$model_class must subclass Model.' );
		}

		static::$tables[ $table->get_slug() ] = array(
			'table' => $table,
			'query' => $complex_query_class,
			'model' => $model_class
		);

		/** @var \wpdb $wpdb */
		global $wpdb;

		$name = $table->get_table_name( $wpdb );
		$name = preg_replace( "/^{$wpdb->prefix}/", "", $name );

		$wpdb->{$name}  = $table->get_table_name( $wpdb );
		$wpdb->tables[] = $name;

		static::fire_plugin_event( $table, 'registered' );
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

		if ( isset( static::$tables[ $slug ] ) ) {
			return static::$tables[ $slug ]['table'];
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
	 * @param \wpdb  $wpdb
	 *
	 * @return Simple_Query|null
	 */
	public static function make_simple_query_object( $slug, \wpdb $wpdb = null ) {

		$table = static::get( $slug );
		$wpdb  = $wpdb ?: $GLOBALS['wpdb'];

		if ( $table ) {
			return new Simple_Query( $wpdb, $table );
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

		$table = static::get( $slug );

		if ( empty( $table ) || empty( static::$tables[ $slug ]['query'] ) ) {
			return null;
		}

		$class = static::$tables[ $slug ]['query'];

		$query = new $class( $args );

		return $query;
	}

	/**
	 * Get the model for a table.
	 *
	 * @since 2.0
	 *
	 * @param string|Table $slug
	 *
	 * @return string|null
	 */
	public static function get_model( $slug ) {

		$slug = $slug instanceof Table ? $slug->get_slug() : $slug;

		if ( isset( static::$tables[ $slug ] ) ) {
			return static::$tables[ $slug ]['model'];
		} else {
			return null;
		}
	}

	/**
	 * Maybe install a table.
	 *
	 * Will skip a table if it is up-to-date.
	 *
	 * @since 1.0
	 *
	 * @param Table $table
	 * @param \wpdb $wpdb
	 *
	 * @return bool True if installed or updated, false if skipped.
	 */
	public static function maybe_install_table( Table $table, \wpdb $wpdb = null ) {

		$wpdb = $wpdb ?: $GLOBALS['wpdb'];

		$installed = (int) get_option( $table->get_table_name( $wpdb ) . '_version', 0 );

		if ( $installed >= $table->get_version() ) {
			return false;
		}

		if ( $installed === 0 ) {
			if ( ! static::is_table_installed( $table, $wpdb ) ) {
				$wpdb->query( $table->get_creation_sql( $wpdb ) );
				static::fire_plugin_event( $table, 'installed' );
			}
		} else {

			$update = $installed + 1;

			while ( $update <= $table->get_version() ) {

				if ( method_exists( $table, "v{$update}_schema_update" ) ) {
					$method = "v{$update}_schema_update";

					$table->{$method}( $wpdb, $installed );

					static::fire_plugin_event( $table, 'updated_schema', array( $update, $installed ) );
				}

				$update += 1;
			}

			static::fire_plugin_event( $table, 'updated' );
		}

		update_option( $table->get_table_name( $wpdb ) . '_version', $table->get_version() );

		return true;
	}

	/**
	 * Check if a table is installed.
	 *
	 * @since 1.0
	 *
	 * @param Table $table
	 * @param \wpdb $wpdb
	 *
	 * @return bool
	 */
	public static function is_table_installed( Table $table, \wpdb $wpdb = null ) {

		$wpdb = $wpdb ?: $GLOBALS['wpdb'];

		$name = $table->get_table_name( $wpdb );

		$results = $wpdb->get_results( "SHOW TABLES LIKE '$name'" );

		return count( $results ) > 0;
	}

	/**
	 * Register a plugin with the table manager.
	 *
	 * @since 2.0
	 *
	 * @param Plugin $plugin
	 */
	public static function register_plugin( Plugin $plugin ) {
		static::$plugins[] = $plugin;
	}

	/**
	 * Fire a plugin event on a given table.
	 *
	 * @since 2.0
	 *
	 * @param Table  $table
	 * @param string $event Name of the method on the plugin object. Either 'registered', 'installed', or 'updated'.
	 * @param array  $args  Additional arguments to pass to the plugin handler.
	 */
	protected static function fire_plugin_event( Table $table, $event, $args = array() ) {

		foreach ( static::$plugins as $plugin ) {
			if ( $plugin->accepts( $table ) ) {
				if ( method_exists( $plugin, $event ) ) {
					$args = array( $table ) + $args;
					call_user_func_array( array( $plugin, $event ), $args );
				}
			}
		}
	}
}