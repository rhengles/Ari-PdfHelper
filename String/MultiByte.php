<?php

namespace Ari\PdfHelper\String;

class MultiByte implements IStringFn
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
		return mb_strlen( $str, $this->charset );
	}

	public function substr( $str, $start, $length = null )
	{
		if ( !isset( $length ) ) {
			$length = $this->length( $str ) - $start;
		}
		return mb_substr( $str, $start, $length, $this->charset );
	}

	public function rpos( $str, $search, $offset = 0 )
	{
		return mb_strrpos( $str, $search, $offset, $this->charset );
	}

}