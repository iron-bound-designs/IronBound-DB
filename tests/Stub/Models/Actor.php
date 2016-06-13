<?php
/**
 * Actor model.
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   AGPL
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Collection;
use IronBound\DB\Model;
use IronBound\DB\Relations\ManyToMany;

/**
 * Class Actor
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int        $id
 * @property string     $name
 * @property \DateTime  $birth_date
 * @property string     $bio
 * @property \WP_Post   $picture
 * @property Collection $movies
 */
class Actor extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	protected function _movies_relation() {
		/** @noinspection PhpParamsInspection */
		$relation = new ManyToMany( get_class( new Movie() ), $this, static::$_db_manager->get( 'actors-movies' ), 'movies', 'actors' );
		$relation->keep_synced();

		return $relation;
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'actors' );
	}
}