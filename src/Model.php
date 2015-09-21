<?php
/**
 * Abstract model class for models built upon our DB table.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB;

use IronBound\Cache\Cacheable;
use IronBound\Cache\Cache;
use IronBound\DB\Table\Table;

/**
 * Class Model
 *
 * @package IronBound\DB;
 */
abstract class Model implements Cacheable, \Serializable {

	/**
	 * Retrieve this object.
	 *
	 * @since 1.0
	 *
	 * @param int|string $pk Primary key of this record.
	 *
	 * @returns self|null
	 */
	public static function get( $pk ) {

		$data = self::get_data_from_pk( $pk );

		if ( $data ) {

			$class  = get_called_class();
			$object = new $class( (object) $data );

			if ( ! self::is_data_cached( $pk ) ) {
				Cache::update( $object );
			}

			return $object;
		} else {
			return null;
		}
	}

	/**
	 * Get data for a primary key.
	 *
	 * @since 1.0
	 *
	 * @param int|string $pk Primary key for this record.
	 *
	 * @return \stdClass|null
	 */
	protected static function get_data_from_pk( $pk ) {

		$data = Cache::get( $pk, static::get_cache_group() );

		if ( ! $data ) {

			$table = static::get_table();
			$db    = Manager::make_simple_query_object( $table->get_slug() );

			$data = $db->get( $pk );
		}

		return $data ? (object) $data : null;
	}

	/**
	 * Check if data is cached.
	 *
	 * @since 1.0
	 *
	 * @param int|string $pk Primary key for this record.
	 *
	 * @return bool
	 */
	protected static function is_data_cached( $pk ) {

		$data = Cache::get( $pk, static::get_cache_group() );

		return ! empty( $data );
	}

	/**
	 * Init an object.
	 *
	 * @since 1.0
	 *
	 * @param \stdClass $data
	 */
	protected abstract function init( \stdClass $data );

	/**
	 * Get the table object for this model.
	 *
	 * @since 1.0
	 *
	 * @returns Table
	 */
	protected static function get_table() {
		// override this in child classes.
		throw new \UnexpectedValueException();
	}

	/**
	 * Update a certain value.
	 *
	 * @since 1.0
	 *
	 * @param string $key   DB column to update.
	 * @param mixed  $value New value.
	 *
	 * @return bool
	 */
	protected function update( $key, $value ) {

		$table = static::get_table();
		$db    = Manager::make_simple_query_object( $table->get_slug() );

		$data = array(
			$key => $value
		);

		$res = $db->update( $this->get_pk(), $data );

		if ( $res ) {
			Cache::update( $this );
		}

		return $res;
	}

	/**
	 * Delete this object.
	 *
	 * @since 1.0
	 *
	 * @throws Exception
	 */
	public function delete() {

		$table = static::get_table();
		$db    = Manager::make_simple_query_object( $table->get_slug() );

		$db->delete( $this->get_pk() );

		Cache::delete( $this );
	}

	/**
	 * Get the data we'd like to cache.
	 *
	 * This is a bit magical. It iterates through all of the table columns,
	 * and checks if a getter for that method exists. If so, it pulls in that
	 * value. Otherwise, it will pull in the default value. If you'd like to
	 * customize this you should override this function in your child model
	 * class.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_data_to_cache() {

		$data = array();

		foreach ( static::get_table()->get_column_defaults() as $col => $default ) {

			if ( method_exists( $this, 'get_' . $col ) ) {
				$method = "get_$col";

				$val = $this->$method();

				if ( is_object( $val ) ) {

					if ( $val instanceof Model ) {
						$val = $val->get_pk();
					} else if ( $val instanceof \DateTime ) {
						$val = $val->format( 'Y-m-d H:i:s' );
					} else if ( isset( $val->ID ) ) {
						$val = $val->ID;
					} else if ( isset( $val->id ) ) {
						$val = $val->id;
					} else if ( isset( $val->term_id ) ) {
						$val = $val->term_id;
					} else if ( isset( $val->comment_ID ) ) {
						$val = $val->comment_ID;
					}
				}

				$data[ $col ] = $val;
			} else {
				$data[ $col ] = $default;
			}
		}

		return $data;
	}

	/**
	 * Get the cache group for this record.
	 *
	 * By default this returns a string in the following format
	 * "df-{$table_slug}".
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function get_cache_group() {
		return static::get_table()->get_slug();
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize( array(
			'pk' => $this->get_pk()
		) );
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 *
	 * @return void
	 */
	public function unserialize( $serialized ) {
		$data = unserialize( $serialized );

		$this->init( self::get_data_from_pk( $data['pk'] ) );
	}
}
