<?php

namespace Ari\PdfHelper\String;

class Byte implements IStringFn
{

	public $charset;

	public function __construct( $charset = null )
	{
		if ( isset( $charset ) ) {
			$this->charset = $charset;
		}
	}

	public function length( $str )
	{
		return strlen( $str );
	}

	public function substr( $str, $start, $length = null )
	{
		return isset($length)
			? substr( $str, $start, $length )
			: substr( $str, $start );
	}

	public function rpos( $str, $search, $offset = 0 )
	{
		return strrpos( $str, $search, $offset );
	}

}