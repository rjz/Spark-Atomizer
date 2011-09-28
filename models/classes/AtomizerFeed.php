<?php if (! defined('BASEPATH')) exit('No direct script access');

require_once('AtomizerChannel.php');
require_once('AtomizerItem.php');


/**
 *	An Atomizer feed
 */
class AtomizerFeed {

	public
		/**
		 * @type	array.AtomizerChannel
		 */
		$channels = array();

	/**
	 *	Initialize a feed
	 *	@param	array	(optional) Information (title,link,description,etc.) to use 
	 *					that describe the first channel
	 *	@param	array	(optional) Items (if any) to append to the first channel
	 *	@return	AtomizerFeed
	 */
	public function __construct( $info = NULL, $entries = NULL ) {

		if( !$info ) {
			$info = array();
		}

		$info['items'] = $entries;

		$channel = new AtomizerChannel( $info );
		$channel->sort();

		$this->addChannel( $channel );

		return $this;
	}

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
	 *	Append this feed to a DOM document (e.g. for {@link AtomizerFeed::save})
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

	/**
	 *  Convolve this and another feed to create a whacky combination of the two
	 */
	public function convolve( $feed, $info = NULL ) {
		
		$items = array();
		
		foreach( $feed->channels as $channel ) {
			$items = array_merge( $items, $channel->items );
		}
		
		foreach( $this->channels as $channel ) {
			$items = array_merge( $items, $channel->items );
		}

		return new AtomizerFeed( $info, $items );
	}

	/**
	 *	Grab a feed over HTTP
	 *	@param	string	The feed address (e.g., http://mysite.com/feed.rss)
	 * @return	AtomizerFeed
	 */
	public function loadURL( $url ) {

		$content = file_get_contents( $url);
		
		if( !$content ) {
			return NULL;
		}
		
		return $this->load( $content );
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

		foreach( $channels as $node ) {
			$channel = new AtomizerChannel();
				$channel->fromXML( $node );
			$this->addChannel( $channel );
		}

		return $this;
	}

	/**
	 *	Convert an {@see AtomizerFeed} to XML
	 *	@param	AtomizerFeed	The feed object to convert to XML
	 *	@return string
	 */
	public function save() {

		$doc = new DOMDocument('1.0', 'iso-8859-1');
		
		$this->appendTo( $doc );

		return $doc->saveXML();
	}
}