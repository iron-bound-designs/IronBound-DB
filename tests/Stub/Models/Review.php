<?php
/**
 * Review model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Model;

/**
 * Class Review
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int       $id
 * @property string    $content
 * @property int       $stars
 * @property \DateTime $published
 * @property Book      $book
 */
class Review extends Model {

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
		return static::$_db_manager->get( 'reviews' );
	}
}