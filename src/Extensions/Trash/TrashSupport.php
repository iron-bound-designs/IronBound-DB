<?php
/**
 * TrashSupport trait.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Extensions\Trash;

use IronBound\DB\Query\FluentQuery;
use IronBound\DB\Extensions\Trash\TrashTable;

/**
 * Trait TrashSupport
 * @package IronBound\DB\Model
 */
trait TrashSupport {

	/**
	 * @var bool
	 */
	private $force_deleting = false;

	/**
	 * Initialize the TrashSupport trait.
	 *
	 * @since 2.0
	 */
	public static function boot_TrashSupport() {

		$table = static::table();

		if ( ! $table instanceof TrashTable ) {
			throw new \UnexpectedValueException( sprintf( "%s model's table must implement TrashTable." ), get_called_class() );
		}

		static::register_global_scope( 'trash', function ( FluentQuery $query ) use ( $table ) {
			$query->and_where( $table->get_deleted_at_column(), true, null );
		} );
	}

	/**
	 * @inheritdoc
	 */
	public function delete() {

		if ( $this->force_deleting ) {
			return parent::delete();
		} else {

			$this->fire_model_event( 'trashing' );

			$table = static::table();

			$this->{$table->get_deleted_at_column()} = $this->fresh_timestamp();

			$this->fire_model_event( 'trashed' );

			return $this->save();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function force_delete() {
		$this->force_deleting = true;
		$this->delete();
		$this->force_deleting = false;
	}

	/**
	 * @inheritdoc
	 */
	public function untrash() {

		$this->fire_model_event( 'untrashing' );

		$table                                   = static::table();
		$this->{$table->get_deleted_at_column()} = null;

		$this->fire_model_event( 'untrashed' );

		return $this->save();
	}

	/**
	 * @inheritdoc
	 */
	public function is_trashed() {

		$table  = static::table();
		$column = $table->get_deleted_at_column();

		return $this->{$column} !== null;
	}

	/**
	 * @inheritdoc
	 */
	public static function with_trashed() {
		return static::without_global_scope( 'trash' );
	}

	/**
	 * @inheritdoc
	 */
	public static function only_trashed() {

		/** @var FluentQuery $query */
		$query = static::with_trashed();

		$query->where( static::table()->get_deleted_at_column(), false, null );

		return $query;
	}

	/**
	 * @inheritdoc
	 */
	public static function trashing( $callback, $priority = 10, $accepted_args = 3 ) {
		return static::register_model_event( 'trashing', $callback, $priority, $accepted_args );
	}

	/**
	 * @inheritdoc
	 */
	public static function trashed( $callback, $priority = 10, $accepted_args = 3 ) {
		return static::register_model_event( 'trashed', $callback, $priority, $accepted_args );
	}

	/**
	 * @inheritdoc
	 */
	public static function untrashing( $callback, $priority = 10, $accepted_args = 3 ) {
		return static::register_model_event( 'untrashing', $callback, $priority, $accepted_args );
	}

	/**
	 * @inheritdoc
	 */
	public static function untrashed( $callback, $priority = 10, $accepted_args = 3 ) {
		return static::register_model_event( 'untrashed', $callback, $priority, $accepted_args );
	}
}