<?php
/**
 * Contains the ModelWithMeta class definition.
 *
 * This is provided as an alternative to the meta trait for PHP 5.3.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Extensions\Meta;

use IronBound\DB\Model;

/**
 * Class ModelWithMeta
 * @package IronBound\DB\Model
 *          
 * This class serves only to provide meta support for PHP 5.3 environments. In later PHP versions,
 * it is recommended to use the MetaSupport trait.
 */
abstract class ModelWithMeta extends Model implements WithMeta {

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
	 * @inheritdoc
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