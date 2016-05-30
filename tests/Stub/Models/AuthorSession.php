<?php
/**
 * AuthorSession stub.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Model;

/**
 * Class AuthorSession
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int    $id
 * @property array  $data
 * @property Author $author
 */
class AuthorSession extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	protected function _get_data( $data ) {
		if ( empty( $data ) ) {
			return array();
		}

		return json_decode( $data, true );
	}

	protected function _set_data( $data ) {
		$data = json_encode( $data );

		$this->_attributes['data'] = $data;

		return $this;
	}

	public function set_value( $key, $val ) {

		$data = $this->data;

		$data[ $key ] = $val;

		$this->data = $data;

		$this->save();

		return $this;
	}

	public function get_value( $key ) {
		return $this->data[ $key ];
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'author-sessions' );
	}
}