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

use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Relations\HasForeign;
use IronBound\DB\Relations\HasMany;
use IronBound\DB\Relations\HasOne;

/**
 * Class Book
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int        $id
 * @property string     $title
 * @property float      $price
 * @property \DateTime  $published
 * @property Author     $author
 * @property Collection $reviews
 */
class Book extends \IronBound\DB\Extensions\Meta\ModelWithMeta {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	protected function _reviews_relation() {

		$relation = new HasMany( 'book', get_class( new Review() ), $this, 'reviews' );
		$relation->keep_synced();

		return $relation;
	}

	protected function _author_relation() {
		return new HasForeign( 'author', $this, get_class( new Author() ) );
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'books' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_meta_table() {
		return static::$_db_manager->get( 'books-meta' );
	}
}