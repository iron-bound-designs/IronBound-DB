<?php
/**
 * Contains the TermSaver class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Saver;

/**
 * Class TermSaver
 *
 * @package IronBound\DB\Value
 */
class TermSaver extends Saver {

	/**
	 * @inheritDoc
	 */
	public function get_pk( $value ) {
		return $value->term_id;
	}

	/**
	 * @inheritDoc
	 */
	public function get_model( $pk ) {

		if ( ! $pk ) {
			return null;
		}

		$term = get_term( $pk );

		if ( is_wp_error( $term ) ) {
			throw new \UnexpectedValueException( $term->get_error_message() );
		}

		return $term;
	}

	/**
	 * @inheritDoc
	 */
	public function make_model( $attributes ) {
		return new \WP_Term( (object) $attributes );
	}

	/**
	 * @inheritDoc
	 */
	public function save( $value, array $options = array() ) {

		if ( ! $value instanceof \WP_Term && ! property_exists( $value, 'term_id' ) ) {
			throw new \InvalidArgumentException( 'ForeignTerm can only save WP_Term objects.' );
		}

		if ( ! $value->term_id ) {
			return $this->do_save( $value );
		}

		$current = get_term( $value->term_id, $value->taxonomy );

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
	 * Do the saving for a term.
	 *
	 * @since 2.0
	 *
	 * @param \WP_Term $term
	 *
	 * @return \WP_Term
	 */
	protected function do_save( $term ) {

		if ( ! $term->term_id ) {
			$ids = wp_insert_term( wp_slash( $term->name ), $term->taxonomy, array(
				'description' => wp_slash( $term->description ),
				'parent'      => $term->parent,
				'slug'        => $term->slug
			) );
		} else {
			$ids = wp_update_term( $term->term_id, $term->taxonomy, array(
				'name'        => wp_slash( $term->name ),
				'description' => wp_slash( $term->description ),
				'parent'      => $term->parent,
				'slug'        => $term->slug
			) );
		}

		if ( is_wp_error( $ids ) ) {
			throw new \InvalidArgumentException( 'Error encountered while saving WP_Term: ' . $ids->get_error_message() );
		}

		$term = get_term( $ids['term_id'], $term->taxonomy );

		if ( is_wp_error( $term ) ) {
			throw new \UnexpectedValueException( $term->get_error_message() );
		}

		return $term;
	}
}