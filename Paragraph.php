<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\LinesBreaker;
use Ari\PdfHelper\LinesBreakChar;

class Paragraph
{

	protected $page;
	protected $font;
	protected $size;
	protected $breakChars;
	protected $debug = false;

	protected $lines;

	public function __construct( $page = null, $font = null, $size = null, $line = null )
	{
		if ( isset( $page ) ) $this->setPage( $page );
		if ( isset( $font ) ) $this->setFont( $font );
		if ( isset( $size ) ) $this->setSize( $size );
		if ( isset( $line ) ) $this->setLineSpace( $line );
		$this->setDefaultBreakChars();
	}

	public function setPage( $page )
	{
		$this->page = $page;
		return $this;
	}

	public function setFont( $font )
	{
		$this->font = $font;
		return $this;
	}

	public function setSize( $size )
	{
		$this->size = $size;
		return $this;
	}

	public function setBreakChars( $bc )
	{
		$this->breakChars = $bc;
		return $this;
	}

	public function setDefaultBreakChars()
	{
		$this->breakChars = new LinesBreakChar();
		return $this;
	}

	public function setDebug( $debug )
	{
		$this->debug = $debug;
		return $this;
	}

	public function setLineSpace( $ls )
	{
		$this->lineSpace = $ls;
		return $this;
	}

	public function setDebugLineColor( $color )
	{
		$this->debugLineColor = $color;
		return $this;
	}

	public function breakLines( $text, $width )
	{
		$lb = new LinesBreaker();

		$lb->setFont( $this->font );
		$lb->setSize( $this->size );
		$lb->setWidth( $width );
		$lb->setBreakChars( $this->breakChars );
		$lb->setDebug( $this->debug );

		$lb->breakText( $text );

		$this->linesBreaker = $lb;
		return $this;
	}

	public function clearLines()
	{
		$this->linesBreaker = null;
		return $this;
	}

	public function getLines()
	{
		return $this->linesBreaker->getLines();
	}

	public function getLinesDebug()
	{
		return $this->linesBreaker->getDebugText();
	}

	public function render( $text, $x, $y, $width, $charset = 'UTF-8' )
	{
		$this->breakLines( $text, $width );
		$page = $this->page;
		$page->setFont( $this->font, $this->size );
		$debugX = $x + $width + 4;

		$lines = $this->getLines();
		foreach ( $lines as $line ) {
			$page->drawText( $line->text, $x, $y, $charset );
			if ( $this->debug ) {
				$page->drawText( $line->getDebugText(), $debugX, $y, $charset );
			}
            $y -= $this->size + $this->lineSpace;
		}
		if ( $this->debug ) {
			$page->drawText( $this->getLinesDebug(), $debugX, $y, $charset );
			//$cinza = new GrayScale( 0.8 );
			$page->setLineColor( $this->debugLineColor );
			$x += $width + 2;
			$y += $this->size;
			$page->drawLine( $x, $y + count( $lines ) * ( $this->size + $this->lineSpace ), $x, $y );
		}
	}

	public function debug( $text, $width )
	{
		$this->breakLines( $text, $width );
		$text = '';
		$lines = $this->getLines();
		foreach ( $lines as $line ) {
			$text .= $line->text.' | '.$line->getDebugText()."\r\n";
		}
		$text .= $this->getLinesDebug();
		return $text;
	}

}