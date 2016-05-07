<?php
/**
 * Contains the definition for the DeleteConstrainable interface
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\ForeignKey;
use IronBound\DB\Table\Column\Foreign;

/**
 * Interface DeleteConstrainable
 * @package IronBound\DB\Table\ForeignKey
 */
interface DeleteConstrainable extends Foreign {

	/**
	 * Register a delete callback.
	 *
	 * The callback will be called with the primary key of the model to be deleted as the first parameter
	 * and the actual model object as the second parameter.
	 *
	 * @since 2.0
	 *
	 * @param callable $callback
	 */
	public function register_delete_callback( $callback );
}