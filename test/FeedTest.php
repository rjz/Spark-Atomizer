<?php

require('test_helper.php');
require('models/atomizer_feed.php');

/**
 *	Utility to clean invalid data from CDATA section of feed XML.
 *	From: http://blog.mark-mclaren.info/2007/02/invalid-xml-characters-when-valid-utf8_5873.html
 *
 * 	Atomizer is not designed to handle malformed XML feeds internally.
 * 	If you're using a feed source that may contain malformatted data, you
 * 	may choose to clean it first.
 */
function clean_cdata ($content)
{
	$result = '';

	for ($i = 0; $i < strlen($content); $i++) {
		$current = ord($content[$i]);
		if (($current == 0x9) ||
			($current == 0xA) ||
			($current == 0xD) ||
			(($current >= 0x20) && ($current <= 0xD7FF)) ||
			(($current >= 0xE000) && ($current <= 0xFFFD)) ||
			(($current >= 0x10000) && ($current <= 0x10FFFF)))
			$result .= $content[$i];
	}

	return $result;
}

class FeedTest extends TestCase 
{

	protected
		$model = 'Atomizer_feed';

	/**
	 *	Malformatted strings will raise a warning. Here, with PHPUnit
	 *	@expectedException PHPUnit_Framework_Error_Warning
	 */
	public function test_malformatted_cdata_hu ()
	{
		$data = get_fixture('rss_hu.xml');
		$feed = new $this->model;
		$feed->load($data);
	}

	public function test_clean_cdata_hu () 
	{
		$data = get_fixture('rss_hu.xml');
		$data = clean_cdata( $data );
		$feed = new $this->model;
		$feed->load($data);
	}
}

