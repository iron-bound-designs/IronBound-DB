<?php
/**
 * Savable column contract.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column\Contracts;

use IronBound\DB\Table\Column\Column;

/**
 * Interface Savable
 * @package IronBound\DB\Table\Column\Contracts
 *
 * Used by the Model when saving all attributes on the model.
 */
interface Savable extends Column {

	/**
	 * Save a value.
	 * 
	 * Data is expected to be un-slashed.
	 *
	 * It should not save if not necessary.
	 *
	 * @since 2.0
	 *
	 * @param mixed $value The value object.
	 *
	 * @return mixed Updated value object. If created, the primary key should be set.
	 */
	public function save( $value );

	/**
	 * Get the primary key from an object value.
	 * 
	 * @since 2.0
	 * 
	 * @param object $value
	 *
	 * @return mixed
	 */
	public function get_pk( $value );
}