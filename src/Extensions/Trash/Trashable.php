<?php
/**
 * Trashable model interface.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Extensions\Trash;

use IronBound\DB\Query\FluentQuery;

/**
 * Interface Trashable
 * @package IronBound\DB\Model
 */
interface Trashable {

	/**
	 * Forcibly delete this model, bypassing the trash.
	 *
	 * The model does not have to be trashed to be force deleted.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function force_delete();

	/**
	 * Untrash the model.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function untrash();

	/**
	 * Determine if this model is trashed.
	 *
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function is_trashed();

	/**
	 * Perform a query including trashed models.
	 *
	 * @since 2.0
	 *
	 * @return FluentQuery
	 */
	public static function with_trashed();

	/**
	 * Perform a query only including trashed models.
	 *
	 * @since 2.0
	 *
	 * @return FluentQuery
	 */
	public static function only_trashed();

	/**
	 * Register a trashing event.
	 *
	 * @since 2.0
	 *
	 * @param callable $callback
	 * @param int      $priority
	 * @param int      $accepted_args
	 *
	 * @return void
	 */
	public static function trashing( $callback, $priority = 10, $accepted_args = 3 );

	/**
	 * Register a trashed event.
	 *
	 * @since 2.0
	 *
	 * @param callable $callback
	 * @param int      $priority
	 * @param          $accepted_args
	 *
	 * @return void
	 */
	public static function trashed( $callback, $priority = 10, $accepted_args = 3 );

	/**
	 * Register an untrashing event.
	 *
	 * @since 2.0
	 *
	 * @param callable $callback
	 * @param int      $priority
	 * @param          $accepted_args
	 *
	 * @return void
	 */
	public static function untrashing( $callback, $priority = 10, $accepted_args = 3 );

	/**
	 * Register an untrashed event.
	 *
	 * @since 2.0
	 *
	 * @param callable $callback
	 * @param int      $priority
	 * @param          $accepted_args
	 *
	 * @return void
	 */
	public static function untrashed( $callback, $priority = 10, $accepted_args = 3 );
}