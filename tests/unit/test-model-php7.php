<?php
/**
 * Test the collection class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Tests;

use IronBound\DB\Model;

/**
 * Class Test_Model
 *
 * @package IronBound\DB\Tests
 */
class Test_Model extends \IronBound\DB\Tests\TestCase {

	protected function get_table() {
		$column = $this->getMockBuilder( '\IronBound\DB\Table\Column\Column' )->setMethods( [
			'prepare_for_storage',
			'convert_raw_to_value'
		] )->getMockForAbstractClass();
		$column->method( 'prepare_for_storage' )->willReturnArgument( 0 );
		$column->method( 'convert_raw_to_value' )->willReturnArgument( 0 );

		$table = $this->getMockBuilder( '\IronBound\DB\Table' )->setMethods( [
			'get_columns',
			'get_slug'
		] )->getMock();
		$table->method( 'get_slug' )->willReturn( 'table' );
		$table->method( 'get_columns' )->willReturn( array(
			'colA' => $column,
			'colB' => $column,
			'colC' => $column,
		) );

		return $table;
	}

	public function test_fill() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_guarded_rejects_parameters_in_fill() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public $_guarded = array( 'colB' );

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertNull( $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_fillable_whitelist_in_fill() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public $_fillable = array( 'colA', 'colC' );

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertNull( $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_guarded() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_guarded( 'colB', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( null, $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_guarded_multiple() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_guarded( 'colB', 'colC', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( null, $model->get_attribute( 'colB' ) );
		$this->assertEquals( null, $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_guarded_and_existing_guarded_properties() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public $_guarded = array( 'colA' );

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_guarded( 'colB', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( null, $model->get_attribute( 'colA' ) );
		$this->assertEquals( null, $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( null, $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_guarded_and_existing_fillable_properties() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public $_fillable = array( 'colB', 'colC' );

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_guarded( 'colB', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( null, $model->get_attribute( 'colA' ) );
		$this->assertEquals( null, $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( null, $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_guarded_and_unguarded_set() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public static $_unguarded = true;

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_guarded( 'colB', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( null, $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_unguarded() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_unguarded( 'colB', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'Me', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'Me', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_unguarded_multiple() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			protected $_guarded = array( 'colA', 'colB' );

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_unguarded( 'colA', 'colB', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'Me', 'colB' => 'bo', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_unguarded_and_existing_guarded_properties() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public $_guarded = array( 'colA' );

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_unguarded( 'colA', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'Me', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_unguarded_and_existing_fillable_properties() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public $_fillable = array( 'colB', 'colC' );

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_unguarded( 'colA', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'Me', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}

	public function test_with_unguarded_and_unguarded_set() {

		$table = $this->get_table();

		/** @var Model $model */
		$model = new class( $table, [] ) extends Model {

			public static $table;

			public static $_unguarded = true;

			public function __construct( $table = null, $data = array() ) {

				if ( $table ) {
					static::$table = $table;
				}

				parent::__construct( [] );
			}

			public function get_pk() {
				return $this->get_attribute( 'colA' );
			}

			public static function get_table() {
				return static::$table;
			}

		};

		$model::$table = $table;

		$model->with_unguarded( 'colB', function ( Model $model ) {
			$model->fill( array( 'colA' => 'He', 'colB' => 'll', 'colC' => 'o' ) );
		} );

		$this->assertEquals( 'He', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );

		$model->fill( array( 'colA' => 'Me', 'colB' => 'll', 'colC' => 'o' ) );

		$this->assertEquals( 'Me', $model->get_attribute( 'colA' ) );
		$this->assertEquals( 'll', $model->get_attribute( 'colB' ) );
		$this->assertEquals( 'o', $model->get_attribute( 'colC' ) );
	}
}