<?php

namespace Ari\PdfHelper;

class MeasureText
{

	protected $font;
	protected $size;
	protected $charset;

	public function __construct( $charset = null, $font = null, $size = null )
	{
		if ( isset( $charset ) ) $this->setCharset( $charset );
		if ( isset( $font ) ) $this->setFont( $font );
		if ( isset( $size ) ) $this->setSize( $size );
	}

	public function setCharset( $charset )
	{
		$this->charset = $charset;
		return $this;
	}

	public function setFont( $font )
	{
		$this->font = $font;
		return $this;
	}

	public function getFont()
	{
		return $this->font;
	}

	public function setSize( $size )
	{
		$this->size = $size;
		return $this;
	}

	public function getSize()
	{
		return $this->size;
	}

	public function setFontFromPage( $page )
	{
		return $this
			->setFont( $page->getFont() )
			->setSize( $page->getFontSize() );
	}

	public function width( $str8 )
	{
		if ( empty( $this->font ) ) throw new \Exception('Font not defined!');
		if ( empty( $this->size ) ) throw new \Exception('Font size not defined!');

		$font = $this->font;
		$str = iconv( $this->charset, 'UTF-16BE', $str8 );
		if ( !strlen( $str ) && strlen( $str8 ) ) {
			print_r( 'Length 8: '.strlen( $str8 ).', Length 16: '.strlen( $str ).' -- './*htmlspecialchars(*/$str8.'<br/>');
		}
		$chars    = array();
		for ($i = 0; $i < strlen( $str ); $i++) {
			$chars[] = ( ord( $str[$i++] ) << 8 ) | ord( $str[$i] );
		}
		$glyphs = $font->glyphNumbersForCharacters( $chars );
		$widths = $font->widthsForGlyphs( $glyphs );
		$width  = ( array_sum( $widths ) / $font->getUnitsPerEm()) * $this->size;
		return $width;
	}

	public function guessCharWidth()
	{
		return (
			6 // = 540 points of width / 90 characters of width
			* $this->size
			/ 10
		);
	}

}