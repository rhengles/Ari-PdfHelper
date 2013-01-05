<?php

namespace Ari\PdfHelper\String;

interface IStringFn
{

	public function length( $str );

	public function substr( $str, $start, $length = null );

	public function rpos( $str, $search, $offset = 0 );

}