<?php
/**
 * Test disabling caching.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\Cache\Cache;
use IronBound\DB\Manager;
use IronBound\DB\Tests\Stub\Models\Uncached;
use IronBound\DB\Tests\Stub\Tables\Uncached as UncachedTable;

/**
 * Class Test_Uncached
 * @package IronBound\DB\Tests
 */
class Test_Uncached extends \IronBound\DB\Tests\TestCase {

	public function setUp() {
		parent::setUp();

		Manager::register( new UncachedTable() );
		Manager::maybe_install_table( Manager::get( 'uncached' ) );
	}

	public function test_no_cache_on_create() {

		$model = Uncached::create( array( 'name' => 'John' ) );

		$this->assertEquals( 'John', Uncached::get( $model->get_pk() )->name );

		$cached = Cache::get( $model->get_pk(), $model::get_cache_group() );

		$this->assertEmpty( $cached );
	}

	/**
	 * @depends test_no_cache_on_create
	 */
	public function test_no_cache_on_update() {

		$model       = Uncached::create( array( 'name' => 'John' ) );
		$model->name = 'Jamie';
		$model->save();

		$this->assertEquals( 'Jamie', Uncached::get( $model->get_pk() )->name );

		$cached = Cache::get( $model->get_pk(), $model::get_cache_group() );
		$this->assertEmpty( $cached );
	}

	public function test_no_cache_from_query() {
		$model = Uncached::create( array( 'name' => 'John' ) );
		Uncached::all();

		$cached = Cache::get( $model->get_pk(), $model::get_cache_group() );
		$this->assertEmpty( $cached );
	}
}