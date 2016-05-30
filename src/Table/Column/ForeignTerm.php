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

/**
 * Class ForeignTerm
 * @package IronBound\DB\Table\Column
 */
class ForeignTerm extends BaseColumn implements Savable, Foreign, DeleteConstrainable {

	/**
	 * @var TermSaver
	 */
	protected $saver;

	/**
	 * ForeignTerm constructor.
	 *
	 * @param string    $name Column name.
	 * @param TermSaver $saver
	 */
	public function __construct( $name, TermSaver $saver ) {
		parent::__construct( $name );

		$this->saver = $saver;
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table_name( \wpdb $wpdb ) {
		return $wpdb->terms;
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
	public function convert_raw_to_value( $raw, \stdClass $row = null ) {
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
	public function save( $value ) {
		return $this->saver->save( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function get_pk( $value ) {
		return $this->saver->get_pk( $value );
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