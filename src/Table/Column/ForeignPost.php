<?php
/**
 * Contains the class for the ForeignPost column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Saver\Saver;
use IronBound\DB\Table\Column\Contracts\Savable;
use IronBound\DB\Table\ForeignKey\DeleteConstrainable;
use IronBound\DB\Table\Table;
use IronBound\DB\WP\Posts;

/**
 * Class ForeignPost
 * @package IronBound\DB\Table\Column
 */
class ForeignPost extends BaseColumn implements Foreign, DeleteConstrainable {

	/**
	 * ForeignPost constructor.
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
		return new Posts();
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table_column_name() {
		return 'ID';
	}

	/**
	 * @inheritDoc
	 */
	public function get_definition() {
		return "{$this->name} BIGINT(20) unsigned NOT NULL";
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
		return get_post( $raw );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( $value instanceof \WP_Post ) {
			return $value->ID;
		}

		return absint( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function register_delete_callback( $callback ) {
		add_action( 'before_delete_post', function ( $post_id ) use ( $callback ) {
			$callback( $post_id, get_post( $post_id ) );
		} );
	}
}