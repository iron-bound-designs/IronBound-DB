<?php
/**
 * Contains the class definition for Boolean columns.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2017.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Exception\InvalidDataForColumnException;

/**
 * Class Boolean
 *
 * @package IronBound\DB\Table\Column
 */
class Boolean extends BaseColumn {

	/**
	 * @inheritDoc
	 */
	public function __construct( $name, array $options = array() ) { parent::__construct( $name, $options, array( 1 ) ); }

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() { return 'TINYINT'; }

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw ) { return (bool) $raw; }

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( is_null( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			throw new InvalidDataForColumnException( 'Non-scalar value encountered.', $this, $value );
		}

		return (bool) $value;
	}
}