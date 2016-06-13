<?php
/**
 * PHP 5.4 model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Stub\Models;

use IronBound\DB\Model;

/**
 * Class PHP54
 * @package IronBound\DB\Tests\Stub\Models
 *
 * @property int    $id
 * @property string $name
 */
class PHP54 extends Model implements Model\WithMeta, Model\Trashable {

	use Model\MetaSupport;
	use Model\TrashSupport;

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
		return static::$_db_manager->get( 'php54' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_meta_table() {
		return static::$_db_manager->get( 'php54-meta' );
	}
}