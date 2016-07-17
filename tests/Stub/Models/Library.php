<?php
/**
 * Library model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Relations\ManyToMany;

/**
 * Class Library
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int        $id
 * @property string     $name
 * @property float      $lat
 * @property float      $lon
 * @property Collection $books
 */
class Library extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	protected function _books_relation() {

		$relation = new ManyToMany( get_class( new Book() ), $this, static::$_db_manager->get( 'books-libraries' ), 'books' );

		return $relation;
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'libraries' );
	}
}