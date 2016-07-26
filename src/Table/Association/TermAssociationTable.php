<?php
/**
 * TermAssociationTable class. Connects a term object with a model.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Association;

use IronBound\DB\Saver\TermSaver;
use IronBound\DB\Table\Column\SimpleForeign;
use IronBound\DB\Table\Column\ForeignTerm;
use IronBound\DB\Table\Table;

/**
 * Class TermAssociationTable
 * @package IronBound\DB\Table\Association
 */
class TermAssociationTable extends BaseAssociationTable {

	/**
	 * @var Table
	 */
	protected $model_table;

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
	protected $model_column;

	/**
	 * TermAssociationTable constructor.
	 *
	 * @param Table $model_table
	 * @param array $overrides
	 */
	public function __construct( Table $model_table, array $overrides = array() ) {

		$this->model_table = $model_table;
		$this->overrides   = $overrides;

		if ( ! empty( $overrides['slug'] ) ) {
			$this->slug = $overrides['slug'];
		} else {
			$this->slug = $model_table->get_slug() . '-terms';
		}

		if ( ! empty( $overrides['table_name'] ) ) {
			$this->table_name = $overrides['table_name'];
		} else {
			$this->table_name = str_replace( '-', '_', "{$model_table->get_slug()}_to_terms" );
		}

		if ( ! empty( $overrides['model_column'] ) ) {
			$this->model_column = $overrides['model_column'];
		} else {
			$this->model_column = $this->build_column_name_for_table( $model_table );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_column_for_table( Table $table ) {
		return $this->get_col_b();
	}

	/**
	 * @inheritDoc
	 */
	public function get_other_column_for_table( Table $table ) {
		return $this->get_col_a();
	}

	/**
	 * @inheritDoc
	 */
	public function get_col_a() {
		return $this->model_column;
	}

	/**
	 * @inheritDoc
	 */
	public function get_col_b() {
		return 'term_ID';
	}

	/**
	 * @inheritDoc
	 */
	public function get_saver() {
		return new TermSaver();
	}

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->prefix . $this->table_name;
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
			$this->get_col_a() => new SimpleForeign( $this->get_col_a(), $this->model_table ),
			$this->get_col_b() => new ForeignTerm( $this->get_col_b() )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return $this->model_table->get_version();
	}
}