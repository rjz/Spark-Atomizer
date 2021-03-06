<?php if (! defined('BASEPATH')) exit('No direct script access');

/**
 *	Provide Atomizer functions to the world!
 */
class Atomizer {

	protected $ci;

	public function __construct() {

		$this->ci = &get_instance();
		$this->ci->load->model('Atomizer_feed');
	}

	/**
	 *	Create a new feed
	 * @deprecated
	 *	@param	array	(optional) Information (title,link,description,etc.) to use 
	 *					that describe the first channel
	 *	@param	array	(optional) Items (if any) to append to the first channel
	 *	@return	AtomizerFeed
	 */
	public function create( $info = NULL, $entries = NULL ) {

		return $this->ci->Atomizer_feed->create( $info, $entries );
	}

	/**
	 *	Grab a feed over HTTP
	 * 
	 * @deprecated
	 *	@param	string	The feed address (e.g., http://mysite.com/feed.rss)
	 * @return	AtomizerFeed
	 */
	public function loadURL( $url ) {

		return $this->ci->Atomizer_feed->loadURL( $url );
	}

	/**
	 *	Read a feed from XML Data
	 * 
	 * @deprecated
	 *	@param	string	The XML content (i.e., RSS feed) to parse
	 *	@return	AtomizerFeed
	 */
	public function load( $content ) {

		return $this->ci->Atomizer_feed->load( $content );
	}

	/**
	 *	Convert an {@see AtomizerFeed} to XML
	 * 
	 * @deprecated
	 *	@param	AtomizerFeed	The feed object to convert to XML
	 *	@return string
	 */
	public function save( $feed ) {

		return $this->ci->Atomizer_feed->save( $feed );
	}
}

?>