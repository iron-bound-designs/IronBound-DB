<?php
/**
 * HasParent class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Model;

/**
 * Class HasParent
 *
 * @package IronBound\DB\Relations
 */
class HasParent extends HasForeign {

	/**
	 * @inheritDoc
	 */
	public function __construct( $attribute, Model $parent ) { parent::__construct( $attribute, $parent, get_class( $parent ) ); }

}