<?php
/**
 * HasChildren class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Model;

/**
 * Class HasChildren
 *
 * @package IronBound\DB\Relations
 */
class HasChildren extends HasMany {

	/**
	 * @inheritDoc
	 */
	public function __construct( $foreign_key, Model $parent, $attribute ) { parent::__construct( $foreign_key, get_class( $parent ), $parent, $attribute ); }
}