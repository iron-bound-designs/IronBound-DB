<?php
/**
 * ModelWithAllForeign class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Model;

/**
 * Class ModelWithAllForeign
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int         $id
 * @property \WP_Post    $post
 * @property \WP_User    $user
 * @property \WP_Term    $term
 * @property \WP_Comment $comment
 * @property Book        $model
 */
class ModelWithAllForeign extends Model {

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
		return self::$_db_manager->get( 'with-all-foreign' );
	}
}
