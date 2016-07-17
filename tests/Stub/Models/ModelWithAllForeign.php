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
use IronBound\DB\Relations\HasForeign;
use IronBound\DB\Relations\HasForeignComment;
use IronBound\DB\Relations\HasForeignPost;
use IronBound\DB\Relations\HasForeignTerm;
use IronBound\DB\Relations\HasForeignUser;

/**
 * Class ModelWithAllForeign
 *
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

	protected function _user_relation() {
		return new HasForeignUser( 'user', $this );
	}

	protected function _post_relation() {
		return new HasForeignPost( 'post', $this );
	}

	protected function _comment_relation() {
		return new HasForeignComment( 'comment', $this );
	}

	protected function _term_relation() {
		return new HasForeignTerm( 'term', $this );
	}

	protected function _model_relation() {
		return new HasForeign( 'model', $this, 'IronBound\DB\Tests\Stub\Models\Book' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return self::$_db_manager->get( 'with-all-foreign' );
	}
}
