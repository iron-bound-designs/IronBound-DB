<?php
/**
 * Test the model events.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;
use IronBound\DB\Tests\Stub\Models\ModelWithForeignPost;
use IronBound\DB\Tests\Stub\Tables\TableWithForeignPost;
use IronBound\WPEvents\EventDispatcher;
use IronBound\WPEvents\GenericEvent;

/**
 * Class Test_Events
 * @package IronBound\DB\Tests
 */
class Test_Events extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new TableWithForeignPost(), '', get_class( new ModelWithForeignPost() ) );
		Manager::maybe_install_table( Manager::get( 'with-foreign-post' ) );
	}

	public function test_updated_event() {

		ModelWithForeignPost::set_event_dispatcher( new EventDispatcher() );

		$model = new ModelWithForeignPost( array(
			'price' => 22.95
		) );
		$model->save();

		$called  = false;
		$phpunit = $this;

		ModelWithForeignPost::updated( function ( GenericEvent $event ) use ( &$called, $model, $phpunit ) {
			$called = true;

			$phpunit->assertEquals( $model, $event->get_subject() );
			$phpunit->assertTrue( $event->has_argument( 'changed' ) );
			$phpunit->assertTrue( $event->has_argument( 'from' ) );
			$phpunit->assertEquals( array(
				'price' => 99.99
			), $event->get_argument( 'changed' ) );
			$phpunit->assertEquals( array(
				'price' => 22.95
			), $event->get_argument( 'from' ) );
		} );

		$model->price = 99.99;
		$model->save();

		$this->assertTrue( $called );
	}
}