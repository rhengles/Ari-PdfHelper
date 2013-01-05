<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\LinesBreaker;
use Ari\PdfHelper\LinesBreakChar;

class Paragraph
{

	protected $page;
	protected $width;

	protected $lines;
	protected $lineCount = 0;
	protected $size;
	protected $height;

	protected $measure;
	protected $strFn;
	protected $breakChars;
	protected $debug = false;

	public function __construct( $page = null, $line = null )
	{
		$this->setDefaults();
		if ( isset( $page ) ) $this->setPage( $page );
		if ( isset( $line ) ) $this->setLineSpace( $line );
	}

	public function setDefaults()
	{
		return $this
			->setDefaultStringFn()
			->setDefaultMeasure()
			->setDefaultBreakChars();
	}

	public function setDebug( $debug )
	{
		$this->debug = $debug;
		return $this;
	}

	public function setStringFn( String\IStringFn $strFn )
	{
		$this->strFn = $strFn;
		return $this;
	}

	public function setStringFnByte( $charset )
	{
		return $this
			->setStringFn( new String\Byte( $charset ) );
	}

	public function setStringFnMultiByte( $charset )
	{
		return $this
			->setStringFn( new String\MultiByte( $charset ) );
	}

	public function setDefaultStringFn()
	{
		return $this
			->setStringFnMultiByte( 'UTF-8' );
	}

	public function setMeasure( MeasureText $ms )
	{
		$this->measure = $ms;
		return $this;
	}

	public function setDefaultMeasure()
	{
		return $this
			->setMeasure( new MeasureText() );
	}

	public function setBreakChars( LinesBreakChar $bc )
	{
		$this->breakChars = $bc;
		return $this;
	}

	public function setDefaultBreakChars()
	{
		return $this
			->setBreakChars( new LinesBreakChar() );
	}

	public function setPage( $page )
	{
		$this->page = $page;
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

	public function prepare( $text, $width )
	{
		$this->size = $this->page->getFontSize();

		$this->measure
			->setCharset( $this->strFn->charset )
			->setFontFromPage( $this->page );
		$this->breakChars->setStringFn( $this->strFn );

		$lb = new LinesBreaker();

		$lb->setMeasure( $this->measure )
			->setStringFn( $this->strFn )
			->setWidth( $width )
			->setBreakChars( $this->breakChars )
			->setDebug( $this->debug )
			->breakText( $text );

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

	public function draw( $text, $x, $y, $width )
	{
		return $this
			->prepare( $text, $width )
			->render( $x, $y );
	}

	public function render( $x, $y )
	{
		$page = $this->page;
		$size = $this->size;

		$charset = $this->strFn->charset;
		$debugX = $x + $this->width + 4;

		$lines = $this->getLines();
		foreach ( $lines as $line ) {
			$page->drawText( $line->text, $x, $y, $charset );
			if ( $this->debug ) {
				$page->drawText( $line->getDebugText(), $debugX, $y, $charset );
			}
			$y -= $size + $this->lineSpace;
		}
		if ( $this->debug ) {
			$page->drawText( $this->getLinesDebug(), $debugX, $y, $charset );
			$page->setLineColor( $this->debugLineColor );
			$x += $this->width + 2;
			$y += $size;
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