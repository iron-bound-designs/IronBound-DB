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

use IronBound\DB\Table\Column\Contracts\Savable;

/**
 * Class ForeignPost
 * @package IronBound\DB\Table\Column
 */
class ForeignPost extends BaseColumn implements Savable {

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
	public function convert_raw_to_value( $raw, \stdClass $row = null ) {
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
	public function save( $value ) {

		if ( ! $value instanceof \WP_Post ) {
			throw new \InvalidArgumentException( 'ForeignPost can only save WP_Post objects.' );
		}

		if ( ! $value->ID ) {
			return $this->do_save( $value );
		}

		$current = get_post( $value->ID );

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
	 * Do the saving for a post.
	 *
	 * @since 2.0
	 *
	 * @param \WP_Post $post
	 *
	 * @return \WP_Post
	 */
	protected function do_save( \WP_Post $post ) {

		if ( ! $post->ID ) {
			$id = wp_insert_post( wp_slash( $post->to_array() ), true );
		} else {
			$id = wp_update_post( $post, true );
		}

		if ( is_wp_error( $id ) ) {
			throw new \InvalidArgumentException( 'Error encountered while saving WP_Post: ' . $id->get_error_message() );
		}

		return get_post( $id );
	}
}