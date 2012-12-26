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
			->setFont( $this->font )
			->setSize( $this->size )
			->setWidth( $this->width )
			->setAverage( $this->average )
			->setBreakChars( $this->breakChars )
			->setDebug( $this->debug );
        do {
            $bp = $breakPoint->findBreak( $text );
			$score++;
			/*if ( $this->debug ) {
				$dc = array();
				foreach ( $bp->counts as $lc ) {
					//$dc[] = $lc;
					$dc[] = number_format($lc->remain, 1);
					$score++;
				}
				$bp['debug'] = implode(', ', $dc).' | '.$bp['length'].' | '.($bp['length'] - $bp['space']);
			}*/
            $lines[] = $bp;
            $text = ltrim( substr( $text, strlen( $bp->text ) ) );
            $avg = empty( $avg ) ? $bp->position->length : intval( ($bp->position->length + $avg) / 2 );
			$breakPoint->setAverage( $avg );
        } while ( strlen($text) );
		$this->score = $score;
		$this->lines = $lines;
		$this->average = $breakPoint->getAverage();
	}

}