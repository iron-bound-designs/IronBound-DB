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
use IronBound\DB\Relations\HasForeignPost;

/**
 * Class ModelWithForeignPost
 *
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int       $id
 * @property \WP_Post  $post
 * @property float     $price
 * @property \DateTime $published
 */
class ModelWithForeignPost extends Model {

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->id;
	}

	public function _post_relation() {
		return new HasForeignPost( 'post', $this );
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'with-foreign-post' );
	}
}