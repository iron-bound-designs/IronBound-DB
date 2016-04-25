<?php
/**
 * Contains the class for the ForeignTerm column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

/**
 * Class ForeignTerm
 * @package IronBound\DB\Table\Column
 */
class ForeignTerm extends BaseColumn {

	/**
	 * ForeignTerm constructor.
	 *
	 * @param string $name Column name.
	 */
	public function __construct( $name ) {
		parent::__construct( $name );
	}

	/**
	 * @inheritDoc
	 */
	public function get_definition() {
		return "{$this->name} bigint(20) unsigned NOT NULL";
	}

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return 'BIGINT';
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw, \stdClass $row = null ) {
		return get_term( $raw );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( $value instanceof \WP_Term || $value instanceof \stdClass ) {
			return $value->term_id;
		}

		return absint( $value );
	}
}