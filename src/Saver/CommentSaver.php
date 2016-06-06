<?php
/**
 * Contains the CommentSaver class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Saver;

/**
 * Class CommentSaver
 * @package IronBound\DB\Value
 */
class CommentSaver extends Saver {

	/**
	 * @inheritDoc
	 */
	public function get_pk( $value ) {
		return $value->comment_ID;
	}

	/**
	 * @inheritDoc
	 */
	public function get_model( $pk ) {
		return get_comment( $pk );
	}

	/**
	 * @inheritDoc
	 */
	public function make_model( $attributes ) {
		return new \WP_Comment( (object) $attributes );
	}

	/**
	 * @inheritDoc
	 */
	public function save( $value, array $options = array() ) {

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