<?php
/**
 * Contains the stub model class.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Model;

/**
 * Class Stub_Model
 * @package IronBound\DB\Tests
 */
class Stub_Model extends Model {

	protected $ID;
	protected $price;
	protected $title;
	protected $published;

	public function __construct( $data ) {
		$this->init( $data );
	}

	public function get_pk() {
		return $this->ID;
	}

	protected function init( \stdClass $data ) {
		$this->ID        = $data->ID;
		$this->price     = $data->price;
		$this->title     = $data->title;
		$this->published = new \DateTime( $data->published );
	}

	protected static function get_table() {
		return new Stub_Table();
	}

	public function get_price() {
		return $this->price;
	}

	public function get_title() {
		return $this->title;
	}

	/**
	 * @return \DateTime
	 */
	public function get_published() {
		return $this->published;
	}

	public function set_price( $price ) {
		$this->price = $price;

		$this->update( 'price', $price );
	}

	public function set_title( $title ) {
		$this->title = sanitize_text_field( $title );

		$this->update( 'title', $this->title );
	}

	public function set_published( \DateTime $published ) {
		$this->published = $published;

		$this->update( 'published', $published->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' ) );
	}
}