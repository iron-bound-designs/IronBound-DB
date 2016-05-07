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

Manager::register_plugin( new DeleteConstrainer() );