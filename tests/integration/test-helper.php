<?php
/**
 * Test the register table helper.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Manager;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\Enum;
use IronBound\DB\Table\Column\ForeignModel;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\SimpleForeign;
use IronBound\DB\Tests\Stub\Tables\Authors;

/**
 * Class Test_Helper
 *
 * @package IronBound\DB\Tests
 */
class Test_Helper extends \IronBound\DB\Tests\TestCase {

	public function test() {

		Manager::register( new Authors(), '', 'IronBound\DB\Tests\Stub\Models\Author' );

		$table = \IronBound\DB\register_table( array(
			'name'           => 'test_table',
			'generate-model' => 'Test_Helper_Model',
			'meta'           => true,
			'columns'        => array(
				'id'             => 'primary-key',
				'earnings'       => 'decimal(10,2)',
				'referrals'      => 'integer',
				'status'         => 'enum(active\*,inactive*,disabled)',
				'big_num'        => 'bigint(20)',
				'post'           => 'wp:post',
				'foreign_pk'     => 'foreign:authors',
				'foreign_column' => 'foreign:authors.bio',
				'foreign_model'  => 'model:authors',
			),
			'defaults'       => array( 'post' => 1 ),
		) );

		$columns  = $table->get_columns();
		$defaults = $table->get_column_defaults();

		$this->assertEquals( 1, $defaults['post'] );
		$this->assertEquals( 0, $defaults['referrals'] );

		$this->assertCount( 9, $columns );
		$this->assertEquals( 'id', $table->get_primary_key() );
		$this->assertEquals( 'test-table', $table->get_slug() );

		$id             = $columns['id'];
		$earnings       = $columns['earnings'];
		$referrals      = $columns['referrals'];
		$status         = $columns['status'];
		$big_num        = $columns['big_num'];
		$post           = $columns['post'];
		$foreign_pk     = $columns['foreign_pk'];
		$foreign_column = $columns['foreign_column'];
		$foreign_model  = $columns['foreign_model'];

		$this->assertInstanceOf( '\IronBound\DB\Table\Column\IntegerBased', $id );
		$this->assertInstanceOf( '\IronBound\DB\Table\Column\DecimalBased', $earnings );
		$this->assertInstanceOf( '\IronBound\DB\Table\Column\IntegerBased', $referrals );
		$this->assertInstanceOf( '\IronBound\DB\Table\Column\Enum', $status );
		$this->assertInstanceOf( '\IronBound\DB\Table\Column\IntegerBased', $big_num );
		$this->assertInstanceOf( '\IronBound\DB\Table\Column\ForeignPost', $post );
		$this->assertInstanceOf( '\IronBound\DB\Table\Column\SimpleForeign', $foreign_pk );
		$this->assertInstanceOf( '\IronBound\DB\Table\Column\SimpleForeign', $foreign_column );
		$this->assertInstanceOf( '\IronBound\DB\Table\Column\ForeignModel', $foreign_model );

		$this->assertEquals( (string) new IntegerBased( 'BIGINT', 'id', array(
			'unsigned',
			'NOT NULL',
			'auto_increment'
		), array( 20 ) ), (string) $id );

		$this->assertEquals(
			(string) new DecimalBased( 'DECIMAL', 'earnings', array(), array( 10, 2 ) ),
			(string) $earnings
		);
		$this->assertEquals(
			(string) new IntegerBased( 'INT', 'referrals' ),
			(string) $referrals
		);
		$this->assertEquals(
			(string) new Enum( array( 'active*', 'inactive', 'disabled' ), 'status' ),
			(string) $status
		);
		$this->assertEquals(
			(string) new IntegerBased( 'BIGINT', 'big_num', array(), array( 20 ) ),
			(string) $big_num
		);
		$this->assertEquals(
			(string) new SimpleForeign( 'foreign_pk', Manager::get( 'authors' ) ),
			(string) $foreign_pk
		);
		$this->assertEquals(
			(string) new SimpleForeign( 'foreign_column', Manager::get( 'authors' ), 'bio' ),
			(string) $foreign_column
		);
		$this->assertEquals(
			(string) new ForeignModel( 'foreign_model', Manager::get_model( 'authors' ), Manager::get( 'authors' ) ),
			(string) $foreign_model
		);

		$this->assertEquals( 'active*', $status->prepare_for_storage( 'active*' ) );
		$this->assertEquals( 'inactive', $status->prepare_for_storage( 'inactive' ) );
		$this->assertEquals( 'disabled', $status->prepare_for_storage( 'disabled' ) );

		$status->fallback_to_default_on_error();
		$this->assertEquals( 'inactive', $status->prepare_for_storage( 'garbage' ) );

		$status->fallback_to_default_on_error( false );
		$this->setExpectedException( '\IronBound\DB\Exception\InvalidDataForColumnException' );
		$status->prepare_for_storage( 'garbage' );

		$this->assertNotNull( Manager::get( 'test-table-meta' ) );

		$model = new Test_Helper_Model();
		$this->assertInstanceOf( '\IronBound\DB\Extensions\Meta\ModelWithMeta', $model );
	}
}