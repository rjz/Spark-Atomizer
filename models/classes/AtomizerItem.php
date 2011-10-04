<?php if (! defined('BASEPATH')) exit('No direct script access');

require_once( 'AtomizerStruct.php' );

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

	/**
	 *	Compare two items by date for sorting
	 * 
	 *	@param	AtomizerItem	the first item to compare
	 * @param	AtomizerItem	the second item to compare
	 */
	static public function compare( $a, $b ) {

		$adate = strtotime($a->pubDate);
		$bdate = strtotime($b->pubDate);
		
		if( $adate === $bdate ) {
			
			return 0;
		}
		
		return ($adate < $bdate) ? 1 : -1;
	}
}