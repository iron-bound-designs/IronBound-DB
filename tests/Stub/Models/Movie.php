<?php
/**
 * Movie model.
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
 * Class Movie
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int        $id
 * @property string     $title
 * @property \DateTime  $release_date
 * @property \WP_Post   $poster
 * @property string     $description
 * @property float      $earnings
 * @property Collection $actors
 */
class Movie extends Model {

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
		return static::$_db_manager->get( 'movies' );
	}

	protected function _actors_relation() {
		/** @noinspection PhpParamsInspection */
		$relation = new ManyToMany( get_class( new Actor() ), $this, static::$_db_manager->get( 'actors-movies' ), 'actors', 'movies' );
		$relation->keep_synced();

		return $relation;
	}
}