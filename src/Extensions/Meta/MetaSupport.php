<?php
/**
 * Contains the definition for the MetaSupport trait.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Extensions\Meta;

/**
 * Trait MetaSupport
 * @package IronBound\DB\Model
 */
trait MetaSupport {

	/**
	 * @inheritDoc
	 */
	public function add_meta( $key, $value, $unique = false ) {

		add_filter( 'sanitize_key', array( $this, '_meta_type_primary_id_override' ), 10, 2 );
		$result = add_metadata( $this->get_meta_type(), $this->get_pk(), $key, $value, $unique );
		remove_filter( 'sanitize_key', array( $this, '_meta_type_primary_id_override' ) );

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function update_meta( $key, $value, $prev_value = '' ) {

		add_filter( 'sanitize_key', array( $this, '_meta_type_primary_id_override' ), 10, 2 );
		$result = update_metadata( $this->get_meta_type(), $this->get_pk(), $key, $value, $prev_value );
		remove_filter( 'sanitize_key', array( $this, '_meta_type_primary_id_override' ) );

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function get_meta( $key = '', $single = false ) {

		add_filter( 'sanitize_key', array( $this, '_meta_type_primary_id_override' ), 10, 2 );
		$result = get_metadata( $this->get_meta_type(), $this->get_pk(), $key, $single );
		remove_filter( 'sanitize_key', array( $this, '_meta_type_primary_id_override' ) );

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function delete_meta( $key, $value = '', $delete_all = false ) {

		add_filter( 'sanitize_key', array( $this, '_meta_type_primary_id_override' ), 10, 2 );
		$result = delete_metadata( $this->get_meta_type(), $this->get_pk(), $key, $value, $delete_all );
		remove_filter( 'sanitize_key', array( $this, '_meta_type_primary_id_override' ) );

		return $result;
	}

	/**
	 * Override the primary ID column by filtering the results of `sanitize_key()`.
	 *
	 * @since 2.0
	 *
	 * @internal
	 *
	 * @param string $key
	 * @param string $original
	 *
	 * @return string
	 */
	public function _meta_type_primary_id_override( $key, $original ) {

		if ( $original === $this->get_meta_type() . '_id' ) {
			$key = static::get_meta_table()->get_primary_id_column();
		}

		return $key;
	}

	/**
	 * Get the table the metadata is stored in.
	 *
	 * @since 2.0
	 *
	 * @return MetaTable
	 */
	public static function get_meta_table() {
		throw new \UnexpectedValueException();
	}

	/**
	 * Get the unique pk for this record.
	 *
	 * @since 1.0
	 *
	 * @return mixed (generally int, but not necessarily).
	 */
	public abstract function get_pk();

	/**
	 * Get the meta type.
	 *
	 * This is based on the table name, such that appending 'meta' will give the un-prefixed table name.
	 *
	 * For example, wp_plugin_booksmeta => plugin_books.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function get_meta_type() {

		/** @var \wpdb $wpdb */
		global $wpdb;

		$name = static::get_meta_table()->get_table_name( $wpdb );
		$name = preg_replace( "/^{$wpdb->prefix}/", "", $name );
		$name = preg_replace( "/meta$/", "", $name );

		return $name;
	}
}