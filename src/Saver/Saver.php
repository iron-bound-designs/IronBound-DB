<?php
/**
 * Contains the abstract SavableValue class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Saver;

/**
 * Class SavableValue
 * @package IronBound\DB\Saver
 */
abstract class Saver {

	/**
	 * Save a value.
	 *
	 * Data is expected to be un-slashed.
	 *
	 * It should not save if unnecessary.
	 *
	 * @since 2.0
	 *
	 * @param mixed $value   The value object.
	 * @param array $options Additional options to use when saving.
	 *
	 * @return mixed Updated value object. If created, the primary key should be set.
	 */
	public abstract function save( $value, array $options = array() );

	/**
	 * Get the primary key from a value.
	 *
	 * @since 2.0
	 *
	 * @param mixed $value
	 *
	 * @return string|int
	 */
	public abstract function get_pk( $value );

	/**
	 * Get a model by its primary key.
	 * 
	 * @since 2.0
	 * 
	 * @param string|int $pk
	 *
	 * @return object
	 */
	public abstract function get_model( $pk );

	/**
	 * Make a model from its given attributes
	 *
	 * @since 2.0
	 *
	 * @param array $attributes
	 *
	 * @return object
	 */
	public abstract function make_model( $attributes );

	/**
	 * Check if two things are numerically equivalent.
	 *
	 * @since 2.0
	 *
	 * @param mixed $a
	 * @param mixed $b
	 *
	 * @return bool
	 */
	protected function numerically_equivalent( $a, $b ) {
		return is_numeric( $a ) && is_numeric( $b ) && strcmp( (string) $a, (string) $b ) === 0;
	}

	/**
	 * Check if there are changes.
	 *
	 * @since 2.0
	 *
	 * @param array $old
	 * @param array $new
	 *
	 * @return bool
	 */
	protected function has_changes( $old, $new ) {

		foreach ( $new as $key => $value ) {
			if ( $value !== $old[ $key ] && ! $this->numerically_equivalent( $value, $old[ $key ] ) ) {
				return true;
			}
		}

		return false;
	}
}