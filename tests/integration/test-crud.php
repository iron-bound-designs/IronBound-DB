<?php
/**
 * Integration tests for crud.
 *
 * @author      Iron Bound Designs
 * @since       1.2
 * @copyright   2016 (c) Iron Bound Designs.
 * @license     MIT
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;

/**
 * Class Test_Crud
 * @package IronBound\DB\Tests
 */
class Test_Crud extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new Stub_Table() );
		Manager::maybe_install_table( new Stub_Table() );
	}

	public function test_create() {

		$builder = new Stub_Builder();
		$builder->set_price( '5.45' );
		$builder->set_title( "My new title" );

		$model = $builder->build();

		$this->assertInstanceOf( 'IronBound\DB\Tests\Stub_Model', $model );
		$this->assertEquals( 5.45, $model->get_price() );
		$this->assertEquals( "My new title", $model->get_title() );
		$this->assertEquals( current_time( 'timestamp', true ), $model->get_published()->getTimestamp(), '', 5 );
	}

	public function test_get() {

		$builder = new Stub_Builder();
		$model   = $builder->build();

		$this->assertEquals( $model, Stub_Model::get( $model->get_pk() ) );
	}

	public function test_update() {

		$builder = new Stub_Builder();
		$model   = $builder->build();

		$published = new \DateTime( 'tomorrow' );
		$model->set_published( $published );

		$model = Stub_Model::get( $model->get_pk() );
		$this->assertEquals( $published, $model->get_published() );
	}

	public function test_delete() {

		$builder = new Stub_Builder();
		$model   = $builder->build();

		$model->delete();
		$this->assertNull( Stub_Model::get( $model->get_pk() ) );
	}

	public function test_caching() {

		$builder = new Stub_Builder();
		$model   = $builder->build();

		$current = $GLOBALS['wpdb']->num_queries;
		Stub_Model::get( $model->get_pk() );
		$this->assertEquals( $current, $GLOBALS['wpdb']->num_queries );
	}

	public function test_serialize() {

		$builder = new Stub_Builder();
		$model   = $builder->build();

		$this->assertEquals( $model, unserialize( serialize( $model ) ) );
	}
}