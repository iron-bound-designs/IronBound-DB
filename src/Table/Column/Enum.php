<?php
/**
 * Enum Column Type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Exception\InvalidDataForColumnException;

/**
 * Class Enum
 *
 * @package IronBound\DB\Table\Column
 */
class Enum extends BaseColumn {

	/**
	 * @var array
	 */
	protected $enum = array();

	/**
	 * @var string
	 */
	protected $default;

	/**
	 * @var bool
	 */
	protected $allow_empty;

	/**
	 * @var BaseColumn
	 */
	protected $storage;

	/**
	 * @var bool
	 */
	protected $fallback = false;

	/**
	 * Enum constructor.
	 *
	 * Enums are not implemented as a 'enum' column in mysql, rather, they provide a simple way to validate
	 * that the provided value is within a set.
	 *
	 * @param array             $enum        Possible values.
	 * @param BaseColumn|string $storage     How to store the enum. If string given, will be stored as a 'VARCHAR'
	 *                                       with $storage as the column name.
	 * @param string            $default     Default value.
	 * @param bool              $allow_empty Allow for empty values.
	 */
	public function __construct( array $enum, $storage, $default = '', $allow_empty = true ) {

		if ( count( $enum ) === 0 ) {
			throw new \InvalidArgumentException( '$enum must contain at least 1 element.', 1 );
		}

		if ( empty( $default ) && ! $allow_empty ) {
			throw new \InvalidArgumentException( '$default must be a non-empty value if $allow_empty is false.', 2 );
		}

		$first = reset( $enum );

		if ( ! is_scalar( $first ) ) {
			throw new \InvalidArgumentException( sprintf(
				'$enum must contain only scalar values. %s given.', is_object( $first ) ? get_class( $first ) : gettype( $first )
			), 3 );
		}

		if ( ( $a = gettype( $default ) ) && ( $b = gettype( $first ) ) && $a !== $b ) {
			throw new \InvalidArgumentException( sprintf(
				'$enum types and $default types must match. %s and %s given.', $a, $b
			), 4 );
		}

		if ( ( $c = count( array_unique( array_map( 'gettype', $enum ) ) ) ) !== 1 ) {
			throw new \InvalidArgumentException( sprintf( '$enum must consist of only one data type. %d given.', $c ), 5 );
		}


		if ( is_string( $storage ) ) {
			$largest = max( array_map( 'strlen', $enum ) );

			// Provide a buffer of 10 in case the $enums change so we can avoid resizing the column later.
			$storage = new StringBased( 'VARCHAR', $storage, array(), array( $largest + 10 ) );
		} elseif ( ! $storage instanceof BaseColumn ) {
			throw new \InvalidArgumentException( '$storage must be a string or a BaseColumn object.', 6 );
		}

		parent::__construct( $storage->name );

		$this->enum        = $enum;
		$this->default     = $default;
		$this->allow_empty = $allow_empty;
		$this->storage     = $storage;
	}

	/**
	 * Instead of throwing an \InvalidDataForColumnException when the given value is invalid,
	 * fallback to the default value.
	 *
	 * By default, an exception will be thrown.
	 *
	 * @since 2.0
	 *
	 * @param bool $fallback
	 *
	 * @return $this
	 */
	public function fallback_to_default_on_error( $fallback = true ) {
		$this->fallback = $fallback;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		return $this->storage->get_mysql_type();
	}

	/**
	 * @inheritDoc
	 */
	protected function get_definition_without_column_name( array $exclude_options = array() ) {
		return $this->storage->get_definition_without_column_name( $exclude_options );
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw ) {

		if ( $this->allow_empty && empty( $raw ) ) {
			return $raw;
		}

		if ( in_array( $raw, $this->enum ) ) {
			return $raw;
		} elseif ( ! $this->fallback ) {
			throw new InvalidDataForColumnException( 'Value is not contained in enum set.', $this, $raw );
		}

		return $this->default;
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( $this->allow_empty && empty( $value ) ) {
			return $value;
		}

		if ( in_array( $value, $this->enum ) ) {
			return $value;
		} elseif ( ! $this->fallback ) {
			throw new InvalidDataForColumnException( 'Value is not contained in enum set.', $this, $value );
		}

		return $this->default;
	}
}