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
use IronBound\DB\Relations\ManyToManyPosts;

/**
 * Class Gallery
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int        $id
 * @property string     $title
 * @property string     $theme
 * @property Collection $art
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

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'galleries' );
	}
}