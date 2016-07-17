<?php
/**
 * Contains the PostSaver class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Saver;

/**
 * Class PostSaver
 *
 * @package IronBound\DB\Value
 */
class PostSaver extends Saver {

	/**
	 * @inheritDoc
	 */
	public function get_pk( $value ) {
		return $value->ID;
	}

	/**
	 * @inheritDoc
	 */
	public function get_model( $pk ) {
		return get_post( $pk );
	}

	/**
	 * @inheritDoc
	 */
	public function make_model( $attributes ) {
		return new \WP_Post( (object) $attributes );
	}

	/**
	 * @inheritDoc
	 */
	public function save( $value, array $options = array() ) {

		if ( ! $value instanceof \WP_Post ) {
			throw new \InvalidArgumentException( sprintf(
				'ForeignPost can only save WP_Post objects, %s given.', is_object( $value ) ? get_class( $value ) : gettype( $value )
			) );
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