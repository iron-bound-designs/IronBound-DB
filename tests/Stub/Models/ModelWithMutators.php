<?php
/**
 * ModelWithForeignPost
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Model;

/**
 * Class ModelWithForeignPost
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int         $id
 * @property \WP_Post    $post
 * @property float       $price
 * @property \DateTime   $published
 */
class ModelWithMutators extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	protected function _mutate_price( $price ) {

		if ( $price < 0 ) {
			throw new \InvalidArgumentException( '$price < 0' );
		}

		return round( $price, 2 );
	}

	protected function _access_post( $post ) {

		if ( ! $post ) {
			return $post;
		}

		$post->model = $this;

		return $post;
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'with-foreign-post' );
	}
}