Atomizer
========

Atomizer is a Codeigniter library that provides a friendly interface for managing RSS data that doesn't actually offer official support for the Atom format. Yet.

Introduction
------------

Available for Codeigniter via [Sparks](http://getsparks.org/install).

You can then load the spark using:

	$this->load->spark('atomizer/[version #]');

Once Atomizer is loaded, we can get on to more exciting things.

### Creating feeds

	$info = array(
		'title' => 'my feed'
	);
	
	$items = array(
		array(
			'title' => 'my first entry',
			'description' => 'This is the first entry in my new feed'
		),
		array(
			'title' => 'my second entry',
			'description' => 'This is the second entry in my new feed'
		)
	);

	$feed = $this->atomizer->create( $info, $items );

### Adding items to a feed

	$item = new AtomizerItem(array(
		'title' => 'my third feed entry',
		'description' => 'This is a new entry for my feed'
	));

	$feed->addItem( $item );

### Saving a feed as XML

	header('Content-type: application/rss+xml');
	echo $this->atomizer->save( $feed );

### Parsing feeds

	$url = 'http://rss.news.yahoo.com/rss/topstories';
	$feed = $this->atomizer->load( file_get_contents( $url ) );

	$items = $feed->channels[0]->items;
	
	foreach( $items as $item ) {
		print_r( $items );
	}

Author
------

RJ Zaworski <rj@rjzaworski.com>

License
-------

Atomizer is released under the JSON License. You can read the license [here](http://www.json.org/license.html).
