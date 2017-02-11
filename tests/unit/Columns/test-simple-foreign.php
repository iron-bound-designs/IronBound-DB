<?php
/**
 * Test the Simple Foreign column.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests\Unit\Columns;

use IronBound\DB\Table\Column\SimpleForeign;

/**
 * Class Test_SimpleForeign
 *
 * @package IronBound\DB\Tests\Unit\Columns
 */
class Test_SimpleForeign extends \IronBound\DB\Tests\TestCase {

	public function test_get_foreign_table_column_name_defaults_to_primary_key() {

		$table = $this->getMockBuilder( '\IronBound\DB\Table\BaseTable' )
		              ->disableOriginalConstructor()->setMethods( array( 'get_primary_key' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_primary_key' )->willReturn( 'PK' );

		$foreign = new SimpleForeign( 'foreign', $table );

		$this->assertEquals( 'PK', $foreign->get_foreign_table_column_name() );
	}

	public function test_get_definition() {

		$column = $this->getMockBuilder( '\IronBound\DB\Table\Column\BaseColumn' )
		               ->disableOriginalConstructor()
		               ->setMethods( array( 'get_definition_without_column_name' ) )
		               ->getMockForAbstractClass();
		$column->expects( $this->once() )->method( 'get_definition_without_column_name' )
		       ->with( array( 'auto_increment' ) )
		       ->willReturn( 'BIGINT(20) NOT NULL' );

		$table = $this->getMockBuilder( '\IronBound\DB\Table\BaseTable' )
		              ->disableOriginalConstructor()->setMethods( array( 'get_primary_key', 'get_columns' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_primary_key' )->willReturn( 'PK' );
		$table->method( 'get_columns' )->willReturn( array(
			'PK' => $column
		) );

		$foreign = new SimpleForeign( 'foreign', $table );
		$this->assertEquals( 'foreign BIGINT(20) NOT NULL', $foreign->get_definition() );
	}

	public function test_get_mysql_type() {

		$column = $this->getMockBuilder( '\IronBound\DB\Table\Column\BaseColumn' )
		               ->disableOriginalConstructor()
		               ->setMethods( array( 'get_mysql_type' ) )
		               ->getMockForAbstractClass();
		$column->expects( $this->once() )->method( 'get_mysql_type' )
		       ->willReturn( 'BIGINT' );

		$table = $this->getMockBuilder( '\IronBound\DB\Table\BaseTable' )
		              ->disableOriginalConstructor()->setMethods( array( 'get_primary_key', 'get_columns' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_primary_key' )->willReturn( 'PK' );
		$table->method( 'get_columns' )->willReturn( array(
			'PK' => $column
		) );

		$foreign = new SimpleForeign( 'foreign', $table );
		$this->assertEquals( 'BIGINT', $foreign->get_mysql_type() );
	}

	public function test_convert_raw_to_value() {

		$raw   = '5';
		$value = 5;

		$column = $this->getMockBuilder( '\IronBound\DB\Table\Column\BaseColumn' )
		               ->disableOriginalConstructor()
		               ->setMethods( array( 'convert_raw_to_value' ) )
		               ->getMockForAbstractClass();
		$column->expects( $this->once() )->method( 'convert_raw_to_value' )
		       ->with( $raw )
		       ->willReturn( $value );

		$table = $this->getMockBuilder( '\IronBound\DB\Table\BaseTable' )
		              ->disableOriginalConstructor()->setMethods( array( 'get_primary_key', 'get_columns' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_primary_key' )->willReturn( 'PK' );
		$table->method( 'get_columns' )->willReturn( array(
			'PK' => $column
		) );

		$foreign = new SimpleForeign( 'foreign', $table );
		$this->assertEquals( $value, $foreign->convert_raw_to_value( $raw ) );
	}

	public function test_prepare_for_storage() {

		$value = '5';
		$raw   = 5;

		$column = $this->getMockBuilder( '\IronBound\DB\Table\Column\BaseColumn' )
		               ->disableOriginalConstructor()
		               ->setMethods( array( 'prepare_for_storage' ) )
		               ->getMockForAbstractClass();
		$column->expects( $this->once() )->method( 'prepare_for_storage' )
		       ->with( $value )
		       ->willReturn( $raw );

		$table = $this->getMockBuilder( '\IronBound\DB\Table\BaseTable' )
		              ->disableOriginalConstructor()->setMethods( array( 'get_primary_key', 'get_columns' ) )
		              ->getMockForAbstractClass();
		$table->method( 'get_primary_key' )->willReturn( 'PK' );
		$table->method( 'get_columns' )->willReturn( array(
			'PK' => $column
		) );

		$foreign = new SimpleForeign( 'foreign', $table );
		$this->assertEquals( $raw, $foreign->prepare_for_storage( $value ) );
	}
}