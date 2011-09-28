<?php if (! defined('BASEPATH')) exit('No direct script access');

require_once('AtomizerStruct.php');
require_once('AtomizerItem.php');

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

	/**
	 *  Sort this channel's items
	 */
	public function sort() {
		usort( $this->items, array( $this, 'compare_item' ) );
	}
	
	/**
	 *	Compare two items by date for sorting
	 * 
	 *	@param	AtomizerItem	the first item to compare
	 * @param	AtomizerItem	the second item to compare
	 */
	protected function compare_item( $a, $b ) {

		$adate = strtotime($a->pubDate);
		$bdate = strtotime($b->pubDate);
		
		if( $adate === $bdate ) {
			
			return 0;
		}
		
		return ($adate < $bdate) ? 1 : -1;
	}
}