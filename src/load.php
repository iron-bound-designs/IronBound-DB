<?php
/**
 * Performs any loading at the beginning of each request.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB;

use IronBound\DB\Table\Plugins\DeleteConstrainer;

require_once __DIR__ . '/functions.php';

Manager::register_plugin( new DeleteConstrainer() );

Model::set_db_manager( new Manager() );

if ( class_exists( '\IronBound\WPEvents\EventDispatcher' ) ) {
	Model::set_event_dispatcher( new \IronBound\WPEvents\EventDispatcher() );
}