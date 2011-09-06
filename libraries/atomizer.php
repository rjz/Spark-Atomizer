<?php if (! defined('BASEPATH')) exit('No direct script access');

/**
 *	A structure for storing and converting between array, object, and XML data.
 */
class AtomizerStruct {

	protected 
		$data,
		$strict,
		$template;

	/**
	 *	Create a new object based on the specified template
	 *	@param	array	the template to validate this object's data against
	 *	@param	boolean	whether or not the template's data types must be matched
	 */
	public function __construct( $template, $strict = false ) {
		$this->template = $template;
		foreach( $this->template as $key => $value ) {
			$this->data[ $key ] = NULL;
		}

		$this->strict = $strict;
	}

	/**
	 *	Getter: retrieve the value of the specified key
	 *	@param	string	Key
	 *	@return	mixed	Value
	 */
	public function __get( $key ) {
		if( array_key_exists( $key, $this->template ) ) {
			return $this->data[ $key ];
		}
	}

	/**
	 *	Setter: set the value specified by a key
	 *	@param	string	Key
	 *	@param	mixed	Value
	 */
	public function __set( $key, $value ) {
		if( array_key_exists( $key, $this->template ) ) {

			$validator = 'is_' . $this->template[ $key ];

			if( !$this->strict || $validator( $value ) ) {
				$this->data[ $key ] = $value;
			} else {
				throw new Exception( 'Data types do not match in ' . get_class( $this ) . '::' . $key );
			}
		}
	}

	/**
	 *	Find the first child of a DOMElement that matches the specfied tag
	 *	@param	DOMElement  Parent element
	 *	@param	string      The tag name (e.g., "item") to match
	 *	@return	DOMElement|NULL
	 */
	protected function findChild( $node, $tagName ) {
		$tags = $node->getElementsByTagName( $tagName );
		$i = 0;
		while( $tag = $tags->item($i++) ) {
			if( $tag->parentNode === $node && $tag->hasChildNodes() ) {
			
				return $tag;
			}
		}
		return NULL;
	}

	/**
	 *	Clone object
	 *	@param	object	The object to clone
	 */
	public function fromCopy( $copy ) {
		foreach( $this->template as $key => $type ) {
			$this->data[$key] = $copy->$key;
		}
	}

	/**
	 *	Construct object from array data
	 *	@param	array	The array to use to set parameters
	 */
	public function fromArray( $data ) {

		foreach( $this->template as $key => $type ) {
			if( array_key_exists( $key, $data ) ) {
				$this->$key = $data[ $key ];
			}
		}	
	}

	/**
	 *	Construct object from XML template
	 *	@param	DOMElement	The parent element containing parameters
	 */
	public function fromXML( $xml ) {

		foreach( $this->template as $key => $type ) {

			$el = $this->findChild( $xml, $key );
			if( $el ) {
				$this->$key = $el->firstChild->textContent; 
			}
		}
	}
}

/**
 *	A single item in an atomizer feed
 */
class AtomizerItem extends AtomizerStruct {

	public function __construct( $copy = NULL ) {

		parent::__construct( array(
				'title' => 'string',
				'link' => 'string',
				'description' => 'string',
				'pubDate' => 'string'
			)
		);
		
		if( is_a( $copy, get_class( $this ) ) ) {
			$this->fromCopy( $copy );
		} else if( is_array( $copy ) ) {
			$this->fromArray( $copy );
		} else if( $copy ) {
			$this->fromXML( $copy );
		}
	}

	/**
	 *	Append this item to a channel
	 *	@param	DOMElement	The channel to append to
	 *	@param	DOMDocument	The document that contains the channel
	 */
	public function appendTo( $channel, $doc ) {

		$item = $doc->createElement('item');
		
		foreach( $this->data as $key => $value ) {

			$tag = $doc->createElement( $key, $value );
			$item->appendChild( $tag );
		}

		$channel->appendChild( $item );
	}
}

/**
 *	A single channel in an atomizer feed
 */
class AtomizerChannel extends AtomizerStruct {

	public
		$items = array();

	public function __construct( $copy = NULL ) {
		parent::__construct( array(
				'title' => 'string',
				'link' => 'string',
				'description' => 'string',
				'language' => 'string',
				'lastBuildDate' => 'string'
			)
		);

		if( is_array( $copy ) ) {
			$this->fromArray( $copy );
		} else if( $copy ) {
			$this->fromXML( $copy );
		}
	}

	/**
	 *	Add an item to this channel
	 *	@param	AtomizerItem	The item to add
	 */
	public function addItem( $item ) {
		$this->items[] = $item;
	}

	/**
	 *	Append this channel to a feed
	 *	@param	DOMElement	The feed to append to
	 *	@param	DOMDocument	The document that contains the feed
	 */
	public function appendTo( $feed, $doc ) {

		$channel = $doc->createElement('channel');
		
		foreach( $this->data as $key => $value ) {

			$tag = $doc->createElement( $key, $value );
			$channel->appendChild( $tag );
		}
		
		foreach( $this->items as $item ) {
			$item->appendTo( $channel, $doc );
		}

		$feed->appendChild( $channel );
	}

	/**
	 *	Overload default {@see AtomizerStruct::fromArray} function to support
	 *	inclusion of items
	 *
	 *	@param	Array	The array to create this feed from
	 */
	public function fromArray( $arr ) {

		parent::fromArray( $arr );
		
		if( $arr && $arr['items']) {
			foreach( $arr['items'] as $item ) {
				$this->addItem( new AtomizerItem( $item ) );
			}
		}
	}

	/**
	 *	Overload default {@see AtomizerStruct::fromXML} function to support
	 *	inclusion of items
	 *
	 *	@param	DOMElement	The XML data to create this feed from
	 */
	public function fromXML( $xml ) {

		parent::fromXML( $xml );

		$items = $xml->getElementsByTagName('item');
		
		foreach( $items as $node ) {
			$this->addItem( new AtomizerItem( $node ) );
		}
	}
}

/**
 *	An Atomizer feed
 */
class AtomizerFeed {

	public 
		$channels = array();

	/**
	 *	Add a channel to this feed
	 *	@param	AtomizerChannel	The channel to add
	 */
	public function addChannel( $channel ) {
		$this->channels[] = $channel;
	}

	/**
	 *	Add an item to this feed.
	 *	@param	AtomizerItem	The item to add
	 *	@param	number	(optional) The index of the channel to attach the item to; 
	 *					if no channel is specified, item will be added to all channels.
	 */
	public function addItem( $item, $index = -1 ) {

		if( array_key_exists( $index, $this->channels ) ) {
			$this->channels[ $index ]->addItem( $item );
		} else {
			foreach( $this->channels as &$channel ) {
				$channel->addItem( $item );
			}
		}
	}

	/**
	 *	Append this feed to a document
	 *	@param	DOMDocument	The document to attach the feed to
	 */
	public function appendTo( $doc ) {

		$rss = $doc->createElement('rss');
			$rss->setAttribute('version','2.0');

		foreach( $this->channels as $channel ) {

			$channel->appendTo( $rss, $doc );
		}

		$doc->appendChild( $rss );
	}
}

/**
 *	Provide Atomizer functions to the world!
 */
class Atomizer {

	/**
	 *	Create a new feed
	 *	@param	array	(optional) Information (title,link,description,etc.) to use 
	 *					that describe the first channel
	 *	@param	array	(optional) Items (if any) to append to the first channel
	 *	@return	AtomizerFeed
	 */
	public function create( $info = NULL, $entries = NULL ) {

		$feed = new AtomizerFeed();

		if( $info ) {

			$info['items'] = $entries;
		}

		$channel = new AtomizerChannel( $info );

		$feed->addChannel( $channel );

		return $feed;
	}

	/**
	 *	Read a feed from XML Data
	 *	@param	string	The XML content (i.e., RSS feed) to parse
	 *	@return	AtomizerFeed
	 */
	public function load( $content ) {

		$doc  = new DOMDocument();
		$doc->loadXML( $content );

		$channels = $doc->getElementsByTagName( 'channel' );
		$result = new AtomizerFeed();

		foreach( $channels as $node ) {
			$channel = new AtomizerChannel();
				$channel->fromXML( $node );
			$result->addChannel( $channel );
		}

		return $result;
	}

	/**
	 *	Convert an {@see AtomizerFeed} to XML
	 *	@param	AtomizerFeed	The feed object to convert to XML
	 *	@return string
	 */
	public function save( $feed ) {

		$doc = new DOMDocument('1.0', 'iso-8859-1');
		
		$feed->appendTo( $doc );

		return $doc->saveXML();
	}
}

?>