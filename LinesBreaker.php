<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\LinesBreakPoint;

class LinesBreaker
{

	protected $font;
	protected $size;
	protected $width;
	protected $breakChars = array(' ');
	protected $debug = false;

	protected $average; // Characters per line
	protected $score; // Characters per line
	protected $lines = array();

	public function __construct( $font = null, $size = null )
	{
		if ( isset( $font ) ) $this->setFont( $font );
		if ( isset( $size ) ) $this->setSize( $size );
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

	public function setWidth( $width )
	{
		$this->width = $width;
		return $this;
	}

	public function setBreakChars( $bc )
	{
		$this->breakChars = $bc;
		return $this;
	}

	public function setDebug( $debug )
	{
		$this->debug = $debug;
		return $this;
	}

	public function getLines()
	{
		return $this->lines;
	}

	public function getLineCount()
	{
		return count($this->lines);
	}

	public function getDebugText()
	{
		return 'Score '.$this->score.' Avg '.number_format( $this->score / count( $this->lines ), 4 );
	}

	public function breakText( $text, $charset )
	{
		$score = 0;
		$lines = array();
		$breakPoint = new LinesBreakPoint();
		$breakPoint
			->setFont( $this->font )
			->setSize( $this->size )
			->setWidth( $this->width )
			->setAverage( $this->average )
			->setBreakChars( $this->breakChars )
			->setDebug( $this->debug );
		do {
			$bp = $breakPoint->findBreak( $text, $charset );
			$score += $bp->counts->count();
			$lines[] = $bp;
			$bpLen = mb_strlen( $bp->text, $charset );
			$remainLen = mb_strlen( $text, $charset ) - $bpLen;
			$text = ltrim( mb_substr( $text, $bpLen, $remainLen, $charset ) );
			$avg = empty( $avg )
				? $bp->position->length
				: intval( ($bp->position->length + $avg) / 2 );
			$breakPoint->setAverage( $avg );
		} while ( mb_strlen($text, $charset) );
		$this->score = $score;
		$this->lines = $lines;
		$this->average = $breakPoint->getAverage();
	}

}