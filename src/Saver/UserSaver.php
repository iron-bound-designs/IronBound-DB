<?php
/**
 * Contains the UserSaver class definition.
 *
 * @author    Iron Bound Designs
 * @since     2.0
 * @license   MIT
 * @copyright Iron Bound Designs, 2016.
 */

namespace IronBound\DB\Saver;

/**
 * Class UserSaver
 *
 * @package IronBound\DB\Value
 */
class UserSaver extends Saver {

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * UserSaver constructor.
	 *
	 * @param string $key
	 */
	public function __construct( $key = 'id' ) {

		if ( ! in_array( $key, array( 'id', 'login', 'slug' ) ) ) {
			throw new \InvalidArgumentException( "Invalid key '$key'." );
		}

		$this->key = $key;
	}

	/**
	 * @inheritDoc
	 */
	public function get_pk( $value ) {
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

	/**
	 * @inheritDoc
	 */
	public function get_model( $pk ) {
		switch ( $this->key ) {
			case 'id':
			case 'login':
			case 'slug':
				return get_user_by( $this->key, $pk );
			default:
				throw new \UnexpectedValueException( 'Unexpected key type.' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function make_model( $attributes ) {
		return new \WP_User( (object) $attributes );
	}

	/**
	 * @inheritDoc
	 */
	public function save( $value, array $options = array() ) {

		if ( ! $value instanceof \WP_User ) {
			throw new \InvalidArgumentException( 'ForeignUser can only save WP_User objects.' );
		}

		if ( ! $value->exists() ) {
			return $this->do_save( $value );
		}

		$current = get_user_by( 'id', $value->ID );

		if ( ! $current ) {
			return $this->do_save( $value );
		}

		$old = $current->to_array();
		$new = $value->to_array();

		if ( $this->has_changes( $old, $new ) ) {
			return $this->do_save( $value );
		}

		return $value;
	}

	/**
	 * Do the saving for a user.
	 *
	 * @since 2.0
	 *
	 * @param \WP_User $user
	 *
	 * @return \WP_User
	 */
	protected function do_save( \WP_User $user ) {

		if ( ! $user->exists() ) {

			if ( empty( $user->user_pass ) ) {
				$user->user_pass = wp_generate_password( 24 );
			}

			$id = wp_insert_user( wp_slash( $user->to_array() ) );
		} else {
			$id = wp_update_user( wp_slash( $user->to_array() ) );
		}

		if ( is_wp_error( $id ) ) {
			throw new \InvalidArgumentException( 'Error encountered while saving WP_User: ' . $id->get_error_message() );
		}

		return get_user_by( 'id', $id );
	}
}