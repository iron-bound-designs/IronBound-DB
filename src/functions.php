<?php
/**
 * Helper functions available across the entire project.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB;

use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Table\Column\Boolean;
use IronBound\DB\Table\Column\Column;
use IronBound\DB\Table\Column\Date;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\Enum;
use IronBound\DB\Table\Column\ForeignComment;
use IronBound\DB\Table\Column\ForeignModel;
use IronBound\DB\Table\Column\ForeignPost;
use IronBound\DB\Table\Column\ForeignTerm;
use IronBound\DB\Table\Column\ForeignUser;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\SimpleForeign;
use IronBound\DB\Table\Column\Time;
use IronBound\DB\Table\InMemoryTable;
use IronBound\DB\Table\Table;

/**
 * Get a list of all traits a class uses.
 *
 * @since 2.0
 *
 * @param string $class
 * @param bool   $autoload
 *
 * @return array
 */
function class_uses_recursive( $class, $autoload = true ) {

	if ( ! function_exists( 'class_uses' ) ) {
		return array();
	}

	$traits = array();

	// Get traits of all parent classes
	do {
		$traits = array_merge( class_uses( $class, $autoload ), $traits );
	} while ( $class = get_parent_class( $class ) );

	// Get traits of all parent traits
	$traitsToSearch = $traits;
	while ( ! empty( $traitsToSearch ) ) {
		$newTraits      = class_uses( array_pop( $traitsToSearch ), $autoload );
		$traits         = array_merge( $newTraits, $traits );
		$traitsToSearch = array_merge( $newTraits, $traitsToSearch );
	}

	foreach ( $traits as $trait => $same ) {
		$traits = array_merge( class_uses( $trait, $autoload ), $traits );
	}

	return array_unique( $traits );
}

/**
 * Get the class "basename" of the given object / class.
 *
 * @since 2.0
 *
 * @param  string|object $class
 *
 * @return string
 */
function class_basename( $class ) {
	$class = is_object( $class ) ? get_class( $class ) : $class;

	return basename( str_replace( '\\', '/', $class ) );
}

/**
 * Register a table.
 *
 * @since 2.0.0
 *
 * @param array $args
 *
 * @return Table
 */
function register_table( array $args ) {

	if ( ! isset( $args['columns'] ) || ! is_array( $args['columns'] ) ) {
		throw new \InvalidArgumentException( '`columns` key is required.' );
	}

	if ( ! isset( $args['name'] ) ) {
		throw new \InvalidArgumentException( '`name` key is required.' );
	}

	if ( isset( $args['model'] ) ) {
		$class = $args['model'];
	} elseif ( isset( $args['generate-model'] ) ) {
		$class = $args['generate-model'];
	} else {
		throw new \InvalidArgumentException( '`model` key is required.' );
	}

	$pk      = isset( $args['primary-key'] ) ? $args['primary-key'] : '';
	$columns = array();

	foreach ( $args['columns'] as $name => $config ) {

		if ( $config instanceof Column ) {
			$columns[ $name ] = $config;

			continue;
		}

		if ( ! is_string( $config ) ) {
			throw new \InvalidArgumentException( "Invalid column config for `$name`." );
		}

		$exploded     = explode( '(', $config );
		$type_options = array();

		// Working on a format of decimal(10,2)
		if ( count( $exploded ) === 2 ) {
			$config       = $exploded[0];
			$type_options = substr( $exploded[1], 0, - 1 );
			$type_options = explode( ',', $type_options );
		}

		switch ( $config ) {
			case 'primary-key':
				$pk     = $name;
				$column = new IntegerBased( 'BIGINT', $name, array(
					'unsigned',
					'NOT NULL',
					'auto_increment'
				), array( 20 ) );
				break;
			case 'boolean':
				$column = new Boolean( $name );
				break;
			case 'date-time':
				$column = new DateTime( $name );
				break;
			case 'date':
				$column = new Date( $name );
				break;
			case 'time':
				$column = new Time( $name );
				break;
			case 'wp:post':
				$column = new ForeignPost( $name );
				break;
			case 'wp:term':
				$column = new ForeignTerm( $name );
				break;
			case 'wp:user':
				$column = new ForeignUser( $name );
				break;
			case 'wp:comment':
				$column = new ForeignComment( $name );
				break;
			case 'int':
			case 'integer':
				$column = new IntegerBased( 'INT', $name, array(), $type_options );
				break;
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'bigint':
				$column = new IntegerBased( strtoupper( $config ), $name, array(), $type_options );
				break;
			case 'decimal':
				$column = new DecimalBased( 'DECIMAL', $name, array(), $type_options );
				break;
			case 'enum':

				$default = '';

				foreach ( $type_options as $i => $type_option ) {
					if ( substr( $type_option, - 1 ) === '*' ) {
						if ( substr( $type_option, - 2 ) === '\\*' ) {
							$type_options[ $i ] = substr( $type_option, 0, - 2 );
							$type_options[ $i ] .= '*';
						} else {
							$default            = substr( $type_option, 0, - 1 );
							$type_options[ $i ] = $default;
						}
					}
				}

				$column = new Enum( $type_options, $name, $default );
				break;
			default:

				$sub_config = explode( ':', $config );

				switch ( $sub_config[0] ) {
					case 'foreign':
						list( $foreign_table_name, $column_name ) = array_pad( explode( '.', $sub_config[1] ), 2, null );
						$foreign_table = Manager::get( $foreign_table_name );

						if ( ! $foreign_table ) {
							throw new \InvalidArgumentException( "No table named `$foreign_table_name` found for column config `$name`." );
						}

						if ( $column_name ) {
							$foreign_columns = $foreign_table->get_columns();

							if ( ! isset( $foreign_columns[ $column_name ] ) ) {
								throw new \InvalidArgumentException( "No column named `$column_name` found for column config `$name`." );
							}
						}

						$column = new SimpleForeign( $name, $foreign_table, $column_name );
						break;
					case 'model':
						$foreign_table_name = $sub_config[1];
						$foreign_table      = Manager::get( $foreign_table_name );

						if ( ! $foreign_table ) {
							throw new \InvalidArgumentException( "No table named `$foreign_table_name` found for column config `$name`." );
						}

						$foreign_model = Manager::get_model( $foreign_table );

						if ( ! $foreign_model ) {
							throw new \InvalidArgumentException( "No model found for column config `$name`." );
						}

						$column = new ForeignModel( $name, $foreign_model, $foreign_table );
						break;
					default:
						throw new \InvalidArgumentException( "Invalid column config for `$name`." );
				}
		}

		$columns[ $name ] = $column;
	}

	if ( empty( $pk ) ) {
		throw new \InvalidArgumentException( '`primary-key` is required.' );
	}

	$table_args = array(
		'primary-key' => $pk,
	);

	$pass_through = array( 'slug', 'defaults' );

	foreach ( $pass_through as $through ) {
		if ( isset( $args[ $through ] ) ) {
			$table_args[ $through ] = $args[ $through ];
		}
	}

	$table = new InMemoryTable( $args['name'], $columns, $table_args );
	$meta  = null;

	if ( ! empty( $args['meta'] ) ) {
		if ( $args['meta'] === true ) {
			$meta = new BaseMetaTable( $table );
		} elseif ( is_array( $args['meta'] ) ) {
			$meta = new BaseMetaTable( $table, $args['meta'] );
		} else {
			throw new \InvalidArgumentException( 'Invalid `meta` key.' );
		}
	}

	if ( isset( $args['generate-model'] ) ) {
		$class   = $args['generate-model'];
		$extends = empty( $args['meta'] ) ? '\IronBound\DB\Model' : '\IronBound\DB\Extensions\Meta\ModelWithMeta';

		ob_start();

		echo "class {$class} extends $extends {";

		echo "public function get_pk() { return \$this->$pk; }";

		echo "protected static function get_table() { return static::\$_db_manager->get( '{$table->get_slug()}' ); }";

		if ( $meta ) {
			echo "public static function get_meta_table() { return static::\$_db_manager->get( '{$meta->get_slug()}' ); }";
		}

		echo '}';

		$code = ob_get_clean();

		eval( $code );
	}

	Manager::register( $table, '', $class );
	Manager::register( $meta );

	return $table;
}