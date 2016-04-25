<?php
/**
 * Contains the stub builder class.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Builder;
use IronBound\DB\Exception;

/**
 * Class Stub_Builder
 * @package IronBound\DB\Tests
 */
class Stub_Builder extends Builder {

	public function set_price( $price ) {

		if ( $price < 0 ) {
			throw new \Exception;
		}

		$this->set_col_value( 'price', $price );

		return $this;
	}

	public function set_title( $title ) {

		$this->set_col_value( 'title', $title );

		return $this;
	}

	public function validate_title( $title ) {
		return sanitize_text_field( $title );
	}

	/**
	 * @return Stub_Model
	 */
	public function build() {
		return Stub_Model::get( $this->create() );
	}

	protected function get_table() {
		return new Stub_Table();
	}
}