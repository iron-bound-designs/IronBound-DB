<?php
/**
 * Contains the AssociationTable class.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Association;

use Doctrine\Common\Inflector\Inflector;
use IronBound\DB\Saver\ModelSaver;
use IronBound\DB\Table\Column\SimpleForeign;
use IronBound\DB\Table\Table;

/**
 * Class AssociationTable
 * @package IronBound\DB\Table
 */
class ModelAssociationTable extends BaseAssociationTable {

	/**
	 * @var Table
	 */
	protected $table_a;

	/**
	 * @var Table
	 */
	protected $table_b;

	/**
	 * @var array
	 */
	protected $overrides = array();

	/**
	 * @var string
	 */
	protected $table_name;

	/**
	 * @var string
	 */
	protected $slug;

	/**
	 * @var string
	 */
	protected $col_a;

	/**
	 * @var string
	 */
	protected $col_b;

	/**
	 * AssociationTable constructor.
	 *
	 * @param Table $table_a
	 * @param Table $table_b
	 * @param array $overrides
	 */
	public function __construct( Table $table_a, Table $table_b, array $overrides = array() ) {

		$this->table_a   = $table_a;
		$this->table_b   = $table_b;
		$this->overrides = $overrides;

		if ( ! empty( $overrides['slug'] ) ) {
			$this->slug = $overrides['slug'];
		} else {
			$this->slug = $table_a->get_slug() . '-' . $table_b->get_slug();
		}

		if ( ! empty( $overrides['table_name'] ) ) {
			$this->table_name = $overrides['table_name'];
		} else {
			$this->table_name = str_replace( '-', '_', "{$table_a->get_slug()}_to_{$table_b->get_slug()}" );
		}

		if ( ! empty( $overrides['col_a'] ) ) {
			$this->col_a = $overrides['col_a'];
		} else {
			$this->col_a = $this->build_column_name_for_table( $table_a );
		}

		if ( ! empty( $overrides['col_b'] ) ) {
			$this->col_b = $overrides['col_b'];
		} else {
			$this->col_b = $this->build_column_name_for_table( $table_b );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_saver() {
		return new ModelSaver();
	}

	/**
	 * @inheritdoc
	 */
	public function get_primary_column_for_table( Table $table ) {
		return $this->get_table_a()->get_slug() === $table->get_slug() ? $this->get_col_b() : $this->get_col_a();
	}
	
	/**
	 * @inheritdoc
	 */
	public function get_other_column_for_table( Table $table ) {
		return $this->get_table_a()->get_slug() === $table->get_slug() ? $this->get_col_a() : $this->get_col_b();
	}

	/**
	 * @inheritdoc
	 */
	public function get_col_a() {
		return $this->col_a;
	}

	/**
	 * @inheritdoc
	 */
	public function get_col_b() {
		return $this->col_b;
	}

	/**
	 * Get the a connecting table.
	 *
	 * @since 2.0
	 *
	 * @return Table
	 */
	public function get_table_a() {
		return $this->table_a;
	}

	/**
	 * Get the b connecting table.
	 *
	 * @since 2.0
	 *
	 * @return Table
	 */
	public function get_table_b() {
		return $this->table_b;
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}{$this->table_name}";
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return array(
			$this->col_a => new SimpleForeign( $this->col_a, $this->table_a ),
			$this->col_b => new SimpleForeign( $this->col_b, $this->table_b )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return $this->table_a->get_version() + $this->table_b->get_version();
	}
}