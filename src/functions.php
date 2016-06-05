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
	};

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