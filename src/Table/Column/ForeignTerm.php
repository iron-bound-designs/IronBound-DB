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

use IronBound\DB\Saver\TermSaver;
use IronBound\DB\Table\Column\Contracts\Savable;
use IronBound\DB\Table\ForeignKey\DeleteConstrainable;
use IronBound\DB\Table\Table;
use IronBound\DB\WP\Terms;

/**
 * Class ForeignTerm
 * @package IronBound\DB\Table\Column
 */
class ForeignTerm extends BaseColumn implements Foreign, DeleteConstrainable {

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
	public function get_foreign_table() {
		return new Terms();
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table_column_name() {
		return 'term_id';
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
	public function convert_raw_to_value( $raw ) {
		$term = get_term( $raw );

		if ( is_wp_error( $term ) ) {
			return null;
		}

		return $term;
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

	/**
	 * @inheritDoc
	 */
	public function register_delete_callback( $callback ) {
		add_action( 'pre_delete_term', function ( $term_id, $taxonomy ) use ( $callback ) {
			$callback( $term_id, get_term( $term_id, $taxonomy ) );
		}, 10, 2 );
	}
}