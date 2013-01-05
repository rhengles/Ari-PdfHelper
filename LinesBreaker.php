<?php

namespace Ari\PdfHelper;

use Ari\PdfHelper\LinesBreakPoint;

class LinesBreaker
{

	protected $width;

	protected $measure;
	protected $strFn;
	protected $breakChars;
	protected $debug = false;

	protected $average; // Characters per line
	protected $score; // Characters per line
	protected $lines = array();

	public function setMeasure( MeasureText $ms )
	{
		$this->measure = $ms;
		return $this;
	}

	public function setStringFn( String\IStringFn $strFn )
	{
		$this->strFn = $strFn;
		return $this;
	}

	public function setBreakChars( LinesBreakChar $bc )
	{
		$this->breakChars = $bc;
		return $this;
	}

	public function setWidth( $width )
	{
		$this->width = $width;
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

	public function breakText( $text )
	{
		$score = 0;
		$lines = array();
		$breakPoint = new LinesBreakPoint();
		$breakPoint
			->setMeasure( $this->measure )
			->setStringFn( $this->strFn )
			->setWidth( $this->width )
			->setBreakChars( $this->breakChars )
			->setAverage( $this->average )
			->setDebug( $this->debug );
		do {
			$bp = $breakPoint->findBreak( $text );
			$score += $bp->counts->count();
			$lines[] = $bp;
			$bpLen = $this->strFn->length( $bp->text );
			$text = ltrim( $this->strFn->substr( $text, $bpLen ) );
			$avg = empty( $avg )
				? $bp->position->length
				: intval( ($bp->position->length + $avg) / 2 );
			$breakPoint->setAverage( $avg );
		} while ( $this->strFn->length($text) );
		$this->score = $score;
		$this->lines = $lines;
		$this->average = $breakPoint->getAverage();
	}

}