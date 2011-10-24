<?php

class Atomizer_feed extends CI_Model {

	public function __construct() {

		require_once('classes/AtomizerFeed.php');
	}

	/**
	 *	Create a new feed
	 *	@param	array	(optional) Information (title,link,description,etc.) to use 
	 *					that describe the first channel
	 *	@param	array	(optional) Items (if any) to append to the first channel
	 *	@return	AtomizerFeed
	 */
	public function create( $info = NULL, $entries = NULL ) {

		$feed = new AtomizerFeed( $info, $entries );
		return $feed;
	}

	/**
	 *	Grab a feed over HTTP
	 *	@param	string	The feed address (e.g., http://mysite.com/feed.rss)
	 * @return	AtomizerFeed
	 */
	public function loadURL( $url ) {

		$feed = new AtomizerFeed();		
		$feed->loadUrl( $url );
		
		return $feed;
	}

	/**
	 *	Read a feed from XML Data
	 *	@param	string	The XML content (i.e., RSS feed) to parse
	 *	@return	AtomizerFeed
	 */
	public function load( $content ) {

		$feed = new AtomizerFeed();
		$feed->load( $content );

		return $feed;
	}

	/**
	 *	Convert an {@see AtomizerFeed} to XML
	 *	@param	AtomizerFeed	The feed object to convert to XML
	 *	@return string
	 */
	public function save( $feed ) {

		return $feed->save();
	}
}
