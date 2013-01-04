<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\LinesBreaker;
use Ari\PdfHelper\LinesBreakChar;

class Paragraph
{

	protected $page;
	protected $font;
	protected $size;
	protected $width;
	protected $breakChars;
	protected $debug = false;

	protected $lines;
	protected $lineCount = 0;
	protected $height;

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

	public function prepare( $text, $width, $charset = 'UTF-8' )
	{
		$lb = new LinesBreaker();

		$lb->setFont( $this->font );
		$lb->setSize( $this->size );
		$lb->setWidth( $width );
		$lb->setBreakChars( $this->breakChars );
		$lb->setDebug( $this->debug );

		$lb->breakText( $text, $charset );

		$this->linesBreaker = $lb;
		$this->lineCount = $lb->getLineCount();
		$this->width = $width;
		$this->height = null;
		
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

	public function getLineCount()
	{
		return $this->lineCount;
	}

	public function getLinesDebug()
	{
		return $this->linesBreaker->getDebugText();
	}

	public function getHeight()
	{
		if ( empty( $this->lineCount ) ) return 0;
		if ( empty( $this->height ) ) {
			$this->height = $this->lineCount *
				( $this->size + $this->lineSpace );
		}
		return $this->height;
	}

	public function draw( $text, $x, $y, $width, $charset = 'UTF-8' )
	{
		return $this
			->prepare( $text, $width, $charset )
			->render( $x, $y, $charset );
	}

	public function testEncoding( $text, $charset )
	{
		$conv = iconv($charset, 'CP1252//IGNORE', $text);
		echo $text, ' [', mb_strlen( $text, $charset ), ' / ', strlen( $conv ), "]<br/>\r\n";
	}

	public function render( $x, $y, $charset = 'UTF-8' )
	{
		$page = $this->page;
		$page->setFont( $this->font, $this->size );
		$debugX = $x + $this->width + 4;

		$lines = $this->getLines();
		foreach ( $lines as $line ) {
			//$this->testEncoding( $line->text, $charset );
			$page->drawText( $line->text, $x, $y, $charset );
			if ( $this->debug ) {
				$page->drawText( $line->getDebugText(), $debugX, $y, $charset );
			}
			$y -= $this->size + $this->lineSpace;
		}
		if ( $this->debug ) {
			$page->drawText( $this->getLinesDebug(), $debugX, $y, $charset );
			$page->setLineColor( $this->debugLineColor );
			$x += $this->width + 2;
			$y += $this->size;
			$page->drawLine( $x, $y + $this->getHeight(), $x, $y );
		}
		return $this;
	}

	public function debug( $text, $width )
	{
		$this->prepare( $text, $width );
		$text = '';
		$lines = $this->getLines();
		foreach ( $lines as $line ) {
			$text .= $line->text.' | '.$line->getDebugText()."\r\n";
		}
		$text .= $this->getLinesDebug();
		return $text;
	}

}