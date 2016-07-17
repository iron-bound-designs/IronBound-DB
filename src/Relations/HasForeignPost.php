<?php
/**
 * HasForeignPost class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Relations;

use IronBound\DB\Model;
use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Saver\PostSaver;
use IronBound\DB\WP\Posts;

/**
 * Class HasForeignPost
 *
 * @package IronBound\DB\Relations
 */
class HasForeignPost extends HasForeign {

	/**
	 * @var bool
	 */
	protected $update_meta_cache = true;

	/**
	 * @var bool
	 */
	protected $update_term_cache = true;

	/**
	 * @inheritDoc
	 */
	public function __construct( $attribute, Model $parent ) {
		parent::__construct( $attribute, $parent, new PostSaver() );

		$this->related_primary_key_column = 'ID';
	}

	/**
	 * @inheritDoc
	 */
	protected function make_query_object( $model_class = false ) {
		return new FluentQuery( new Posts() );
	}

	/**
	 * Update the post meta cache when loading this relation.
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
	 * Update the term cache when loading this relation.
	 *
	 * By defaul the term cache IS updated.
	 *
	 * @since 2.0
	 *
	 * @param bool $update
	 *
	 * @return $this
	 */
	public function update_term_cache( $update = true ) {
		$this->update_term_cache = $update;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_results() {
		$post = parent::get_results();

		if ( ! $post ) {
			return $post;
		}

		$posts = array( $post );

		update_post_caches( $posts, 'any', $this->update_term_cache, $this->update_meta_cache );

		return $post;
	}

	/**
	 * @inheritDoc
	 */
	public function eager_load( array $models, $callback = null ) {
		$loaded = parent::eager_load( $models, $callback );
		$posts  = $loaded->toArray();

		update_post_caches( $posts, 'any', $this->update_term_cache, $this->update_meta_cache );

		return $loaded;
	}
}