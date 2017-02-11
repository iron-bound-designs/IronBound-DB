<?php
/**
 * Test the MetaSupport trait.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Meta;

use IronBound\DB\Manager;
use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Tests\Stub\Models\PHP54;
use IronBound\DB\Tests\Stub\Tables\PHP54s;

/**
 * Class Test_Meta_Trait
 * @package IronBound\DB\Tests\Meta
 */
class Test_Meta_Trait extends \IronBound\DB\Tests\TestCase {

	function setUp() {
		parent::setUp();

		Manager::register( new PHP54s() );
		Manager::register( new BaseMetaTable( new PHP54s(), array(
			'primary_id_column' => 'php54_id'
		) ) );

		Manager::maybe_install_table( Manager::get( 'php54' ) );
		Manager::maybe_install_table( Manager::get( 'php54-meta' ) );
	}

	// just basic tests to ensure things are working since we delegate to WP's functions
	public function test() {

		if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
			$this->markTestSkipped( 'PHP5.4 Only Test' );
		}

		$model = PHP54::create( array(
			'name' => 'Awesome'
		) );

		$model->add_meta( 'basic', 'stuff' );
		$this->assertEquals( 'stuff', $model->get_meta( 'basic', true ) );

		$model->update_meta( 'basic', 'other-stuff' );
		$this->assertEquals( 'other-stuff', $model->get_meta( 'basic', true ) );

		$model->delete_meta( 'basic' );
		$this->assertEmpty( $model->get_meta( 'basic', true ) );
	}
}