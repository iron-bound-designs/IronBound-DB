<?php
/**
 * Generic sql tag.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Generic
 * @package IronBound\DB\Query\Tag
 */
class Generic {

	/**
	 * @var string
	 */
	private $tag_name;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * Constructor.
	 *
	 * @param string $tag_name
	 * @param string $value
	 */
	public function __construct( $tag_name, $value = '' ) {
		$this->tag_name = $tag_name;
		$this->value    = $value;
	}

	/**
	 * Get the tag name.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_name() {
		return $this->tag_name;
	}

	/**
	 * Get the stored value.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_value() {
		return $this->value;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return strtoupper( $this->get_name() ) . " " . $this->get_value();
	}

	/**
	 * Implode an array if necessary.
	 *
	 * @param string|array $values
	 *
	 * @return string
	 */
	protected function implode( $values ) {
		if ( is_array( $values ) ) {
			return implode( ', ', $values );
		} else {
			return $values;
		}
	}
}