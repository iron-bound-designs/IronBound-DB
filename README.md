# IronBound DB

[![Build Status](https://travis-ci.org/iron-bound-designs/IronBound-DB.svg?branch=master)](https://travis-ci.org/iron-bound-designs/IronBound-DB) [![codecov.io](https://codecov.io/github/iron-bound-designs/IronBound-DB/coverage.svg?branch=master)](https://codecov.io/github/iron-bound-designs/IronBound-DB?branch=master)

IronBound DB is a straightforward, but powerful, ORM for WordPress. It facilitates the creation of custom database tables,
 and bridges the gap between WordPress functionality and IronBound Models.

Installs with composer.

````
composer require ironbound/db
````

## Basic Usage

### Relationships
```php
$author = Author::create( array( 'name' => 'John Doe' ) );
$author->books->add( new Book( array( 'title' => "John's Book" ) ) );
$author->save();

// Eager load One-to-Many Books relationship to prevent N+1 problem
$authors = Author::with( 'books' )->results();

foreach ( $authors as $author ) {
	foreach ( $author->books as $book ) {
		$book->price += 5.00;
	}
}

$authors->save();

```

### Interacting with WordPress Models

```php
$author = Author::get( 1 );
$author->user->display_name = 'John Doe'; // This is a WP_User object
$author->picture->post_title = 'John Doe'; // This is a WP_Post object
$author->save(); // The User and Post were automatically saved

// You can have posts inserted too

$author = new Author(
	'picture' => new WP_Post( (object) array( 
		'post_type'  => 'attachment',
		'post_title' => 'Jane Doe'
	) );
);
$author->save();
```

### Meta Support

```php
Author::create( array( 
 	'name' => 'John Doe'
 ) )->update_meta( 'favorite_color', 'blue' );

Author::create( array( 
 	'name' => 'Jane Doe'
 ) )->update_meta( 'favorite_color', 'green' );
 
$authors = Author::query()->where_meta( array( 
	array(
		'key' 	=> 'favorite_color',
		'value' => 'blue'
	)
) )->results();
```

### Events

When used with [WPEvents](https://github.com/iron-bound-designs/IronBound-WPEvents), IronBound DB will
fire events (actions) when various state changes occur.

```php
Book::created( function( GenericEvent $event ) {
	
	$book = $event->get_subject();
	// do custom processing...
} );

Book::saving( function( GenericEvent $event ) {

	$book = $event->get_subject();
	
	if ( $book->price === 0 ) {
		throw new UnexpectedValueException( 'Books are not allowed to be free!' );
	}
} );
```

### Foreign Key Constrains

If the constraint behavior is set to `cascade`, this will delete both the Author and the Book. 
`deleted` events will be fired for each model. Constraints require `WPEvents` be configured.

```php
$author = Author::create( array( 'name' => 'John Doe' ) );
$book 	= Book::create( array( 'title' => "John's Book", 'author' => $author ) );

$author->delete();
```

## Defining Tables

Tables are defined in PHP. Each model is represented by a table. Tables are generally defined by a PHP class.

### Basic Model Tables

```php

class Authors extends \IronBound\DB\Table\BaseTable {

	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}authors";
	}

	public function get_slug() {
		return 'authors';
	}
	public function get_columns() {
		return array(
			'id'         => new \IronBound\DB\Table\Column\IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment' ), array( 20 ) ),
			'name'       => new \IronBound\DB\Table\Column\StringBased( 'VARCHAR', 'name', array(), array( 60 ) ),
			'birth_date' => new \IronBound\DB\Table\Column\DateTime( 'birth_date' ),
			'bio'        => new \IronBound\DB\Table\Column\StringBased( 'LONGTEXT', 'bio' ),
			'picture'    => new \IronBound\DB\Table\Column\ForeignPost( 'picture', new \IronBound\DB\Saver\PostSaver() ),
			'user'    	 => new \IronBound\DB\Table\Column\ForeignUser( 'user', new \IronBound\DB\Saver\UserSaver() )
		);
	}

	public function get_column_defaults() {
		return array(
			'id'         => 0,
			'name'       => '',
			'birth_date' => '',
			'bio'        => '',
			'picture'    => 0,
			'user		 => 0
		);
	}

	public function get_primary_key() {
		return 'id';
	}

	public function get_version() {
		return 1;
	}
}

\IronBound\DB\Manager::register( new Authors() );
\IronBound\DB\Manager::maybe_install_table( new Authors() );
```

### Foreign Key Constrains

```php

class Authors extends \IronBound\DB\Table\BaseTable implements \IronBound\DB\Table\ForeignKey\DeleteConstrained {

	// ... other methods

	public function get_delete_constraints() {
		return array(
			'user' 	  => self::RESTRICT    // Exception thrown when deleting a User if an author referencing it exists,
			'picture' => self::SET_DEFAULT // The column will be updated to its default value when its referenced post is deleted 
		);
	}
}
```

[Read about how to use it](https://ironbounddesigns.com/custom-tables-wordpress/).