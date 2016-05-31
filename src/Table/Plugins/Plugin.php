<?php
/**
 * Contains the Plugin interface definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Plugins;

use IronBound\DB\Table\Table;

/**
 * Interface Plugin
 *
 * @package IronBound\DB\Table\Plugins
 */
interface Plugin {

	/**
	 * Determine whether this plugin operates on this table.
	 *
	 * @since 2.0
	 *
	 * @param Table $table
	 *
	 * @return bool
	 */
	public function accepts( Table $table );

	/**
	 * Is called whenever a table is registered.
	 *
	 * @since 2.0
	 *
	 * @param Table $table
	 */
	//public function registered( Table $table );

	/**
	 * Is called whenever a table is installed for the first time.
	 *
	 * @since 2.0
	 *
	 * @param Table $table
	 */
	//public function installed( Table $table );

	/**
	 * Is called whenever a table is updated.
	 *
	 * @since 2.0
	 *
	 * @param Table $table
	 */
	//public function updated( Table $table );

	/**
	 * Is called whenever a table's schema is updated.
	 *
	 * @since 2.0
	 *
	 * @param Table $table
	 * @param int   $to   Table version updated to.
	 * @param int   $from Table version update from.
	 */
	//public function updated_schema( Table $table, $to );
}