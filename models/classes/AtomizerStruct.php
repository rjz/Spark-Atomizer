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