<?php
/**
 * Author Model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Relations\HasForeignPost;
use IronBound\DB\Relations\HasMany;
use IronBound\DB\Relations\HasOne;

/**
 * Class Author
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int           $id
 * @property string        $name
 * @property \DateTime     $birth_date
 * @property string        $bio
 * @property \WP_Post      $picture
 * @property \DateTime     $created_at
 * @property \DateTime     $updated_at
 * @property Collection    $books
 * @property AuthorSession $session
 */
class Author extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	protected function _books_relation() {

		$book = new Book();

		$relation = new HasMany( 'author', get_class( $book ), $this, 'books' );
		$relation->keep_synced();

		return $relation;
	}

	protected function _picture_relation() {
		return new HasForeignPost( 'picture', $this );
	}

	protected function _session_relation() {
		return new HasOne( 'author', get_class( new AuthorSession() ), $this, 'session' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'authors' );
	}
}