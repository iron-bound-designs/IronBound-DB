<?php
/**
 * HasForeignComment class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Saver\CommentSaver;
use IronBound\DB\Saver\PostSaver;
use IronBound\DB\WP\Comments;
use IronBound\DB\WP\Posts;

/**
 * Class HasForeignComment
 *
 * @package IronBound\DB\Relations
 */
class HasForeignComment extends HasForeign {

	/**
	 * @var bool
	 */
	protected $update_meta_cache = true;

	/**
	 * @inheritDoc
	 */
	public function __construct( $attribute, Model $parent ) {
		parent::__construct( $attribute, $parent, new CommentSaver() );

		$this->related_primary_key_column = 'comment_ID';
	}

	/**
	 * @inheritDoc
	 */
	protected function make_query_object( $model_class = false ) {
		return new FluentQuery( new Comments() );
	}

	/**
	 * Update the comment meta cache when loading this relation.
	 *
	 * By default, the meta cache IS updated.
	 *
	 * @since 2.0
	 *
	 * @param bool $update
	 *
	 * @return $this
	 */
	public function update_meta_cache( $update = true ) {
		$this->update_meta_cache = $update;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_results() {
		$comment = parent::get_results();

		if ( ! $comment ) {
			return $comment;
		}

		$comments = array( $comment );

		update_comment_cache( $comments, $this->update_meta_cache );

		return $comment;
	}

	/**
	 * @inheritDoc
	 */
	public function eager_load( array $models, $callback = null ) {
		$loaded   = parent::eager_load( $models, $callback );
		$comments = $loaded->toArray();

		update_comment_cache( $comments, $this->update_meta_cache );

		return $loaded;
	}
}