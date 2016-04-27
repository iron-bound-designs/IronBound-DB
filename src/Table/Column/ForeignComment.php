<?php
/**
 * Contains the class for the ForeignComment column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Table\Column\Contracts\Savable;

/**
 * Class ForeignComment
 * @package IronBound\DB\Table\Column
 */
class ForeignComment extends BaseColumn implements Savable {

	/**
	 * ForeignComment constructor.
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
		return get_comment( $raw );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( $value instanceof \WP_Comment || $value instanceof \stdClass ) {
			return $value->comment_ID;
		}

		return absint( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function save( $value ) {

		if ( ! $value instanceof \WP_Comment && ! property_exists( $value, 'comment_ID' ) ) {
			throw new \InvalidArgumentException( 'ForeignComment can only save WP_Comment objects.' );
		}

		if ( ! $value->comment_ID ) {
			return $this->do_save( $value );
		}

		$current = get_comment( $value->comment_ID );

		if ( ! $current ) {
			return $this->do_save( $value );
		}

		$old = $current->to_array();
		$new = $value->to_array();

		if ( $this->has_changes( $old, $new ) ) {
			return $this->do_save( $value );
		}

		return $value;
	}

	/**
	 * Do the saving for a comment.
	 *
	 * @since 2.0
	 *
	 * @param \WP_Comment $comment
	 *
	 * @return \WP_Comment
	 */
	protected function do_save( $comment ) {

		if ( ! $comment->comment_ID ) {
			$id = wp_insert_comment( wp_slash( $comment->to_array() ) );
		} else {
			if ( wp_update_comment( wp_slash( $comment->to_array() ) ) ) {
				$id = $comment->comment_ID;
			}
		}

		if ( empty( $id ) ) {
			throw new \InvalidArgumentException( 'Error encountered while saving WP_Comment.' );
		}

		return get_comment( $id );
	}
}