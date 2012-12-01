<?php

require('test_case.php');

define('BASEPATH', '/path');

/**
 *	Stub model class
 */
class CI_Model
{

}

function get_fixture ($filename)
{
	$path = dirname(__FILE__) . '/fixtures/';
	return file_get_contents( $path . $filename );
}
