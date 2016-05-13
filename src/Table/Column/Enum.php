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

/**
 * Class Enum
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
	 * Enum constructor.
	 *
	 * @param array             $enum        Possible values.
	 * @param string            $default     Default value.
	 * @param bool              $allow_empty Allow for empty values.
	 * @param BaseColumn|string $storage     How to store the enum. If string given, will be stored as a 'VARCHAR'.
	 */
	public function __construct( array $enum, $default = '', $allow_empty = true, $storage = null ) {

		if ( is_string( $storage ) ) {
			$storage = new StringBased( 'VARCHAR', $storage, array(), array( 255 ) );
		} elseif ( ! $storage instanceof BaseColumn ) {
			throw new \InvalidArgumentException( '$storage must be a string or a BaseColumn object.' );
		}

		parent::__construct( $storage->name );

		$this->enum        = $enum;
		$this->default     = $default;
		$this->allow_empty = $allow_empty;
		$this->storage     = $storage;
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
	public function convert_raw_to_value( $raw, \stdClass $row = null ) {

		if ( $this->allow_empty && empty( $raw ) ) {
			return $raw;
		}

		if ( in_array( $raw, $this->enum ) ) {
			return $raw;
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
		}

		return $this->default;
	}
}