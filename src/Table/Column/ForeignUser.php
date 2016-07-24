<?php
/**
 * Contains the class for the ForeignUser column type.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Table\Column;

use IronBound\DB\Saver\UserSaver;
use IronBound\DB\Table\Column\Contracts\Savable;
use IronBound\DB\Table\ForeignKey\DeleteConstrainable;
use IronBound\DB\Table\Table;
use IronBound\DB\WP\Users;

/**
 * Class ForeignUser
 *
 * @package IronBound\DB\Table\Column
 */
class ForeignUser extends BaseColumn implements Foreign, DeleteConstrainable {

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * ForeignUser constructor.
	 *
	 * @param string $name Column name.
	 * @param string $key  User table key. id | login | slug
	 */
	public function __construct( $name, $key = 'id' ) {
		parent::__construct( $name );

		if ( ! in_array( $key, array( 'id', 'login', 'slug' ) ) ) {
			throw new \InvalidArgumentException( "Invalid key '$key'." );
		}

		$this->key = $key;
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table() {
		return new Users();
	}

	/**
	 * @inheritDoc
	 */
	public function get_foreign_table_column_name() {
		switch ( $this->key ) {
			case 'id':
				return 'ID';
			case 'login':
				return 'user_login';
			case 'slug':
				return 'user_nicename';
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_definition() {
		switch ( $this->key ) {
			case 'id':
				return "{$this->name} bigint(20) unsigned NOT NULL";
			case 'login':
				return "{$this->name} varchar(60) NOT NULL";
			case 'slug':
				return "{$this->name} varchar(50) NOT NULL";
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_mysql_type() {
		switch ( $this->key ) {
			case 'id':
				return 'BIGINT';
			case 'login':
			case 'slug':
				return 'VARCHAR';
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function convert_raw_to_value( $raw ) {
		return get_user_by( $this->key, $raw );
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage( $value ) {

		if ( $value instanceof \WP_User ) {

			switch ( $this->key ) {
				case 'id':
					return $value->ID;
				case 'login':
					return $value->user_login;
				case 'slug':
					return $value->user_nicename;
				default:
					throw new \UnexpectedValueException( 'Unexpected key type.' );
			}
		}

		switch ( $this->key ) {
			case 'id':
				return absint( $value );
			case 'login':
			case 'slug':
				return trim( $value );
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function register_delete_callback( $callback ) {

		$self = $this;

		add_action( 'delete_user', function ( $id, $reassign ) use ( $callback, $self ) {

			$user = get_user_by( 'id', $id );
			$pk   = $self->prepare_for_storage( $user );

			$callback( $pk, $user, $reassign );

		}, 10, 2 );
	}
}