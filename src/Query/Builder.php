<?php
/**
 * A simple query builder.
 *
 * @author Iron Bound Designs
 * @since  1.0
 */

namespace IronBound\DB\Query;
use IronBound\DB\Query\Tag\Generic;

/**
 * Class Builder
 * @package IronBound\DB\Query
 */
final class Builder {

	/**
	 * @var Generic|Builder
	 */
	private $parts = array();

	/**
	 * Append new sql to the existing query string.
	 *
	 * It is advisable to use the convenience methods.
	 *
	 * @since 1.0
	 *
	 * @param Generic $sql
	 *
	 * @return Builder
	 */
	public function append( Generic $sql ) {
		$this->parts[] = $sql;

		return $this;
	}

	/**
	 * Perform a subquery.
	 *
	 * @since 1.0
	 *
	 * @param Builder $builder
	 *
	 * @return $this
	 */
	public function subquery( Builder $builder ) {
		$this->parts[] = $builder;

		return $this;
	}

	/**
	 * toString method.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->build();
	}

	/**
	 * Build a sql statement.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function build() {

		if ( empty( $this->parts ) ) {
			return '';
		}

		$query = '';

		foreach ( $this->parts as $part) {

			if ($part instanceof Builder) {
				$query .= "($part)";
			} else {
				$query .= "$part ";
			}
		}

		return $query;
	}
}