<?php
/**
 * Gallery model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Relations\ManyToManyComments;
use IronBound\DB\Relations\ManyToManyPosts;
use IronBound\DB\Relations\ManyToManyTerms;
use IronBound\DB\Relations\ManyToManyUsers;

/**
 * Class Gallery
 *
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int        $id
 * @property string     $title
 * @property string     $theme
 * @property Collection $art
 * @property Collection $attendees
 * @property Collection $comments
 * @property Collection $terms
 */
class Gallery extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	protected function _art_relation() {
		/** @noinspection PhpParamsInspection */
		return new ManyToManyPosts( $this, static::$_db_manager->get( 'galleries-posts' ), 'art' );
	}

	protected function _attendees_relation() {
		/** @noinspection PhpParamsInspection */
		return new ManyToManyUsers( $this, static::$_db_manager->get( 'galleries-users' ), 'attendees' );
	}

	protected function _comments_relation() {
		/** @noinspection PhpParamsInspection */
		return new ManyToManyComments( $this, static::$_db_manager->get( 'galleries-comments' ), 'comments' );
	}

	protected function _terms_relation() {
		/** @noinspection PhpParamsInspection */
		return new ManyToManyTerms( $this, static::$_db_manager->get( 'galleries-terms' ), 'terms' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'galleries' );
	}
}