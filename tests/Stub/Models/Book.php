<?php
/**
 * Book Model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Model;

/**
 * Class Book
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int       $id
 * @property string    $title
 * @property float     $price
 * @property \DateTime $published
 * @property Author    $author
 */
class Book extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'books' );
	}
}