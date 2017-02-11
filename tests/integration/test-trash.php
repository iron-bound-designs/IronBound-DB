<?php
/**
 * Test model trashing.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;
use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\PHP54;
use IronBound\DB\Tests\Stub\Tables\PHP54s;
use IronBound\WPEvents\GenericEvent;

/**
 * Class Test_Trash
 * @package IronBound\DB\Tests
 */
class Test_Trash extends \IronBound\DB\Tests\TestCase {

	function setUp() {
		parent::setUp();

		if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
			$this->markTestSkipped( 'PHP5.4 Only Test' );
		}

		Manager::register( new PHP54s() );
		Manager::register( new BaseMetaTable( new PHP54s(), array(
			'primary_id_column' => 'php54_id'
		) ) );

		Manager::maybe_install_table( Manager::get( 'php54' ) );
		Manager::maybe_install_table( Manager::get( 'php54-meta' ) );
	}

	public function test_trash_is_default() {

		$model = PHP54::create( array(
			'name' => 'My Name'
		) );
		$model->delete();

		$this->assertTrue( $model->is_trashed() );

		$this->assertNotNull( PHP54::get( $model->get_pk() ) );
	}

	public function test_force_delete() {

		$model = PHP54::create( array(
			'name' => 'My Name'
		) );
		$model->force_delete();

		$this->assertNull( PHP54::get( $model->get_pk() ) );
	}

	public function test_trashed_models_are_excluded_from_default_queries() {

		$m1 = PHP54::create( array( 'name' => 'Name 1' ) );
		$m2 = PHP54::create( array( 'name' => 'Name 2' ) );

		$m1->delete();

		$models = PHP54::all();

		$this->assertEquals( 1, $models->count() );
		$this->assertNotNull( $models->get_model( $m2->get_pk() ) );
	}

	public function test_with_trashed() {

		$m1 = PHP54::create( array( 'name' => 'Name 1' ) );
		$m2 = PHP54::create( array( 'name' => 'Name 2' ) );

		$m1->delete();

		$models = PHP54::with_trashed()->results();

		$this->assertEquals( 2, $models->count() );
		$this->assertNotNull( $models->get_model( $m1->get_pk() ) );
		$this->assertNotNull( $models->get_model( $m2->get_pk() ) );
	}

	public function test_only_trashed() {

		$m1 = PHP54::create( array( 'name' => 'Name 1' ) );
		$m2 = PHP54::create( array( 'name' => 'Name 2' ) );

		$m1->delete();

		$models = PHP54::only_trashed()->results();

		$this->assertEquals( 1, $models->count() );
		$this->assertNotNull( $models->get_model( $m1->get_pk() ) );
	}

	public function test_trashing_event() {

		$called = false;

		PHP54::trashing( function ( GenericEvent $event ) use ( &$called ) {
			$called = true;
		} );

		PHP54::create( array( 'name' => 'My Name' ) )->delete();

		$this->assertTrue( $called );
	}

	public function test_trashed_event() {

		$called = false;

		PHP54::trashed( function ( GenericEvent $event ) use ( &$called ) {
			$called = true;
		} );

		PHP54::create( array( 'name' => 'My Name' ) )->delete();

		$this->assertTrue( $called );
	}

	public function test_untrashing_event() {

		$called = false;

		PHP54::untrashing( function ( GenericEvent $event ) use ( &$called ) {
			$called = true;
		} );

		$model = PHP54::create( array( 'name' => 'My Name' ) );
		$model->delete();
		$model->untrash();

		$this->assertTrue( $called );
	}

	public function test_untrashed_event() {

		$called = false;

		PHP54::untrashed( function ( GenericEvent $event ) use ( &$called ) {
			$called = true;
		} );

		$model = PHP54::create( array( 'name' => 'My Name' ) );
		$model->delete();
		$model->untrash();

		$this->assertTrue( $called );
	}
}