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

use IronBound\DB\Table\Column\Contracts\Savable;

/**
 * Class ForeignTerm
 * @package IronBound\DB\Table\Column
 */
class ForeignTerm extends BaseColumn implements Savable {

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

		return get_term( $ids['term_id'], $term->taxonomy );
	}
}